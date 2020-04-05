<?php

class Manticore_Indexing implements SplObserver {

	const TYPE_POST = 'posts';
	const TYPE_COMMENTS = 'comments';
	const TYPE_ATTACHMENTS = 'attachments';
	const TYPE_STATS = 'stats';
	const PARSE_SERVER = 'http://docsapi.manticoresearch.com/';


	const INDEXING_POSTS_MAX_LIMIT = 100;
	const INDEXING_COMMENTS_MAX_LIMIT = 500;
	const INDEXING_SENTENCES_MAX_LIMIT = 5000;
	const INDEXING_ATTACHMENTS_MAX_LIMIT = 200;
	const INDEXING_STATS_MAX_LIMIT = 500;

	const INDEXING_COUNTERS_TABLE = 'sph_indexing_counters';
	const INDEXING_LOCK_NAME = 'manticore_indexing_lock';
	const INDEXING_LOCK_TIME = 1;

	const STORED_KEY_RAND = '43m4z87';


	private $autocomplete_max_id = null;

	/**
	 * Exploder for raw files
	 *
	 * @var string
	 */
	private $exploder = "\n\t\n\t\n";

	/**
	 * @var Manticore_Config
	 */
	private $config;
	/**
	 * @var wpdb
	 */
	private $wpdb;
	/**
	 * @var string
	 */
	private $table_prefix;
	/**
	 * @var string
	 */
	private $table_counters;
	/**
	 * @var array
	 */
	private $sql_queries = [];

	private $blog_id = 1;

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * Directory for storing raw data.
	 * If indexing error occurred, indexing data puts into storage
	 *
	 * @var string
	 */
	private $raw_directory;

	private $error_log_path = '';

	/**
	 * Manticore_Indexing constructor.
	 *
	 * @param Manticore_Config $config
	 */
	public function __construct( Manticore_Config $config ) {
		global $wpdb, $table_prefix;
		$this->config         = $config;
		$this->wpdb           = $wpdb;
		$this->table_prefix   = $table_prefix;
		$this->blog_id        = get_current_blog_id();
		$this->table_counters = $this->table_prefix . self::INDEXING_COUNTERS_TABLE;
		$this->raw_directory  = $this->config->admin_options['sphinx_path'] . DIRECTORY_SEPARATOR . 'reindex';
		$this->sql_queries    = $this->get_index_queries();
		$this->error_log_path = SPHINXSEARCH_SPHINX_INSTALL_DIR . DIRECTORY_SEPARATOR . 'indexing.log';
	}


	/**
	 * @param $index_data
	 * @param $index_limit
	 *
	 * @return float
	 */
	private function get_indexing_cycles_count( $index_data, $index_limit ) {

		return ceil( ( $index_data['all_count'] - $index_data['indexed'] ) / $index_limit );
	}

	/**
	 * @param $indexed
	 *
	 * @return string
	 */
	private function get_indexing_offset( $indexed ) {
		if ( $indexed == 0 ) {
			return '';
		}

		return ' OFFSET ' . $indexed;
	}


	/**
	 * @return array
	 */
	public function reindex() {
		if ( ! ManticoreSearch::$plugin->sphinxQL->is_active() ) {

			if ( ! file_exists( $this->config->get_option( 'sphinx_searchd' ) ) ||
			     ! file_exists( $this->config->get_option( 'sphinx_conf' ) ) ) {
				return [ 'status' => 'error', 'message' => 'Indexer: configuration files not found.' ];
			} else {
				return [ 'status' => 'error', 'message' => 'Manticore daemon inactive' ];
			}
		}

		if ( $this->config->admin_options[ WORKER_CACHE_ILS ] !== WORKER_CACHE ) {
			if ( $this->check_lock() != 1 ) {
				return [ 'status' => 'error', /*'message' => 'Another indexer is still running'*/ ];
			}

			/*
			require_once( '/home/klim/xhprof/xhprof_lib/utils/xhprof_lib.php' );
			require_once( '/home/klim/xhprof/xhprof_lib/utils/xhprof_runs.php' );
			xhprof_enable( XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY );
			*/

			$results = [];

			if ( ManticoreSearch::is_main_blog() == 'true' ) {

				$exclude_blogs = $this->config->get_option( 'exclude_blogs_from_search' );
				$blogs_list    = [];

				foreach ( ManticoreSearch::get_network_sites( false ) as $blog_id => $blog_url ) {
					if ( in_array( $blog_id, $exclude_blogs ) ) {
						continue;
					}
					$blogs_list[] = $blog_id;
				}

				foreach ( $blogs_list as $blog ) {
					$this->config->update_admin_options( [ 'now_indexing_blog' => $blog ] );
					$this->blog_id = $blog;

					if ( $blog > 1 ) {
						$this->table_prefix = $this->wpdb->base_prefix . $blog . '_';
					} else {
						$this->table_prefix = $this->wpdb->base_prefix;
					}

					$this->table_counters = $this->table_prefix . self::INDEXING_COUNTERS_TABLE;

					$this->sql_queries = $this->get_index_queries();

					$results = $this->do_reindex();
				}
				$this->config->update_admin_options( [ 'now_indexing_blog' => '' ] );
			} else {

				$root_options  = get_blog_option( ManticoreSearch::get_main_blog_id(),
					Manticore_Config::ADMIN_OPTIONS_NAME );
				$exclude_blogs = $root_options['exclude_blogs_from_search'];
				if ( in_array( $this->blog_id, $exclude_blogs ) ) {
					return [ 'status' => 'error', 'message' => 'This blog excluded from search by administrator' ];
				}

				$results = $this->do_reindex();
			}

			$this->wpdb->get_var( 'SELECT RELEASE_LOCK("' . self::INDEXING_LOCK_NAME . '")' );

			/*
			$xhprof_data = xhprof_disable();
			$xhprof_runs = new \XHProfRuns_Default();
			$run_id      = $xhprof_runs->save_run( $xhprof_data, "xhprof_testing" );
			*/


			return [ 'status' => 'success', 'results' => $results ];
		} else {
			return [ 'status' => 'success', 'results' => false ];
		}
	}

	private function do_reindex() {

		if ( $this->config->admin_options['autocomplete_cache_clear'] == 'update' ) {
			$au_cache = new ManticoreAutocompleteCache();
			$au_cache->clean_cache();
		}

		$started = $this->get_results();

		if ( $started['steps'] == 4 ) {
			$this->reset_counters();
		}


		$counters        = $this->get_index_counters();
		$sorted_counters = [];
		foreach ( $counters as $k => $counter ) {
			$sorted_counters[ $counter['type'] ] = $counter;
		}
		unset( $counters );

		$results = [];
		foreach (
			[
				self::TYPE_POST,
				self::TYPE_COMMENTS,
				self::TYPE_ATTACHMENTS,
				self::TYPE_STATS
			] as $type
		) {
			if ( $sorted_counters[ $type ]['finished'] == 1 ) {
				continue;
			}
			$results[] = $this->index( $type );
		}

		return $results;
	}

	/**
	 * @return null|string
	 */
	private function check_lock() {
		return $this->wpdb->get_var(
			'SELECT GET_LOCK("' . self::INDEXING_LOCK_NAME . '", ' . self::INDEXING_LOCK_TIME . ')' );
	}


	private function get_index_counters() {
		return $this->wpdb->get_results( 'SELECT * FROM ' . $this->table_counters, ARRAY_A );
	}


	private function reset_counters() {

		$counters = $this->get_index_counters();
		foreach ( $counters as $key => $counter ) {
			$counter['indexed']   = 0;
			$counter['finished']  = 0;
			$counter['all_count'] = $this->get_content_count( $counter['type'] );
			$this->wpdb->update( $this->table_counters, $counter, [ 'type' => $counter['type'] ] );
		}


		ManticoreSearch::$plugin->sphinxQL->deleteWhere( Manticore_Config_Maker::MAIN_INDEX_PREFIX . $this->blog_id,
			[ 'id', '>', 0 ] )->execute();
		ManticoreSearch::$plugin->sphinxQL->deleteWhere( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $this->blog_id,
			[ 'id', '>', 0 ] )->execute();
		ManticoreSearch::$plugin->sphinxQL->deleteWhere( Manticore_Config_Maker::STATS_INDEX_PREFIX . $this->blog_id,
			[ 'id', '>', 0 ] )->execute();

		$this->delete_indexing_log();
		$this->clear_raw_data();
	}


	/**
	 * @param string $type
	 *
	 * @return string|array
	 */
	private function index( $type = self::TYPE_POST ) {

		$index_name = Manticore_Config_Maker::MAIN_INDEX_PREFIX . $this->blog_id;
		if ( $type == self::TYPE_POST ) {
			$index_limit = self::INDEXING_POSTS_MAX_LIMIT;

		} elseif ( $type == self::TYPE_COMMENTS ) {
			$index_limit = self::INDEXING_COMMENTS_MAX_LIMIT;

		} elseif ( $type == self::TYPE_ATTACHMENTS ) {
			$index_limit = self::INDEXING_ATTACHMENTS_MAX_LIMIT;

		} else {
			$index_limit = self::INDEXING_STATS_MAX_LIMIT;
			$index_name  = Manticore_Config_Maker::STATS_INDEX_PREFIX . $this->blog_id;
		}

		$indexing_result = $this->get_index_result_by_type( $type );
		if ( $indexing_result['all_count'] == 0 ) {
			$indexing_result['finished'] = 1;
			$this->wpdb->update( $this->table_counters, $indexing_result, [ 'type' => $type ] );

			return '';
		}
		$cycles            = $this->get_indexing_cycles_count( $indexing_result, $index_limit );
		$errors            = 0;
		$old_error_handler = set_error_handler( [ $this, "my_error_handler" ] );

		for ( $i = 0; $i < $cycles; $i ++ ) {

			$offset  = $this->get_indexing_offset( $indexing_result['indexed'] );
			$results = $this->get_content_results( $type, $offset );

			if ( ! empty( $this->wpdb->last_error ) ) {
				$errors ++;
				$this->add_to_indexing_log( $this->wpdb->last_error );
				if ( $errors > 5 ) {
					break;
				}
				continue;
			}


			if ( empty( $results ) ) {
				$indexing_result['indexed']  = $indexing_result['all_count'];
				$indexing_result['finished'] = 1;
				$this->wpdb->update( $this->table_counters, $indexing_result, [ 'type' => $type ] );
				break;
			}

			$update = ManticoreSearch::$plugin->sphinxQL
				->index( $index_name )
				->insert( $results, [ 'title', 'body', 'category', 'tags', 'taxonomy', 'custom_fields', 'keywords' ] )
				->execute()
				->get_results();

			if ( $type != self::TYPE_STATS ) {

				$ac_sentences = $this->explode_sentences( $results, false );

				$chunked = array_chunk( $ac_sentences, self::INDEXING_SENTENCES_MAX_LIMIT );

				foreach ( $chunked as $chunk ) {
					$ac_update = ManticoreSearch::$plugin->sphinxQL
						->index( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $this->blog_id )
						->insert( $chunk, [
							'content',
							'string_content'
						] )
						->execute()
						->get_results();

				}

			}


			if ( $update['status'] == 'success' && $update['results'] === false ) {

				return [ 'status' => 'error', 'message' => 'Indexer: Error indexing.' ];
			} elseif ( $update['status'] == 'error' ) {

				return [ 'status' => 'error', 'message' => $update['message'] ];
			}

			$indexing_result['indexed'] = min( $indexing_result['indexed'] + $update['affected'],
				$indexing_result['all_count'] );
			if ( $indexing_result['indexed'] == $indexing_result['all_count'] ) {
				$indexing_result['finished'] = 1;
			} else {
				$indexing_result['finished'] = 0;
			}

			$this->wpdb->update( $this->table_counters, $indexing_result, [ 'type' => $type ] );
		}

		if ( ! empty( $old_error_handler ) ) {
			restore_error_handler( $old_error_handler );
		} else {
			restore_error_handler();
		}

		if ( $type != self::TYPE_STATS ) {
			ManticoreSearch::$plugin->sphinxQL->flush( $index_name );

			ManticoreSearch::$plugin->sphinxQL->flush( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $this->blog_id );

			ManticoreSearch::$plugin->sphinxQL->optimize( $index_name );

			ManticoreSearch::$plugin->sphinxQL->optimize( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $this->blog_id );

		} else {
			ManticoreSearch::$plugin->sphinxQL->optimize( $index_name );
		}

		return 'Indexing ' . $type . ' complete. <b>' . $indexing_result['all_count']
		       . '</b> document' . ( $indexing_result['all_count'] > 1 ? 's' : '' ) . ' indexed ';
	}

	/**
	 * @return array
	 */
	public function get_results() {
		if ( empty( $this->config->admin_options['secure_key'] ) ||
		     $this->config->admin_options['secure_key'] == 'false' ) {

			return [ 'status' => 'error', 'message' => 'Plugin secure key is not valid' ];
		} else {

			$blog = $this->config->admin_options['now_indexing_blog'];

			if ( ! empty( $blog ) && $blog > 1 ) {
				$table_prefix = $this->wpdb->base_prefix . $blog . '_';
			} else {
				$table_prefix = $this->wpdb->base_prefix;
			}

			$table_counters = $table_prefix . self::INDEXING_COUNTERS_TABLE;

			$results = $this->wpdb->get_row(
				'SELECT ' .
				'sum(`indexed`) as indexed, ' .
				'sum(`all_count`) as all_count, ' .
				'sum(`finished`) as steps ' .
				'FROM ' . $table_counters, ARRAY_A );
			if ( file_exists( $this->error_log_path ) ) {
				$results['logs'] = file_get_contents( $this->error_log_path );
				$results['logs'] = str_replace( "\n", '<br>', $results['logs'] );
			}
			$results['blog_id'] = $this->config->admin_options['now_indexing_blog'];

			return $results;
		}

	}

	/**
	 * @param $type
	 *
	 * @return array|null|object|void
	 */
	private function get_index_result_by_type( $type ) {
		return $this->wpdb->get_row( 'SELECT * FROM ' . $this->table_counters . ' WHERE type = "' . $type . '"',
			ARRAY_A );
	}

	/**
	 * @return array
	 */
	private function get_index_queries() {
		$queries_config = include( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_index_queries.php' );

		$indexing_taxonomy        = $this->get_taxonomy_indexing_fields();
		$indexing_custom_fields   = $this->get_custom_indexing_fields();
		$skip_indexing_mime_types = $this->get_skip_indexing_mime_types();
		$index_post_types         = $this->get_indexing_post_types();
		if ( ! empty( $queries_config ) ) {
			foreach ( $queries_config as $k => $query ) {
				$queries_config[ $k ] = str_replace( '{table_prefix}', $this->table_prefix, $query );
				$queries_config[ $k ] = str_replace( '{index_taxonomy}', $indexing_taxonomy, $queries_config[ $k ] );
				$queries_config[ $k ] = str_replace( '{index_custom_fields}', $indexing_custom_fields,
					$queries_config[ $k ] );
				$queries_config[ $k ] = str_replace( '{skip_indexing_mime_types}', $skip_indexing_mime_types,
					$queries_config[ $k ] );
				$queries_config[ $k ] = str_replace( '{index_post_types}', $index_post_types, $queries_config[ $k ] );
				$queries_config[ $k ] = str_replace( '{blog_id}', $this->blog_id, $queries_config[ $k ] );
				$queries_config[ $k ] = str_replace( '{shards_count}', ManticoreSearch::SHARDS_COUNT,
					$queries_config[ $k ] );

				if ( $k == 'query_stats' ) {
					$queries_config[ $k ] = str_replace( '{limit}', self::INDEXING_STATS_MAX_LIMIT,
						$queries_config[ $k ] );
				} elseif ( $k == 'query_attachments' ) {
					$queries_config[ $k ] = str_replace( '{limit}', self::INDEXING_ATTACHMENTS_MAX_LIMIT,
						$queries_config[ $k ] );
				} else {
					$queries_config[ $k ] = str_replace( '{limit}', self::INDEXING_POSTS_MAX_LIMIT,
						$queries_config[ $k ] );
				}

			}
		}

		return $queries_config;
	}

	/**
	 * @param $type
	 *
	 * @return null|string
	 */
	public function get_content_count( $type ) {

		if ( $type == self::TYPE_POST ) {
			$sum = $this->wpdb->get_var( $this->sql_queries['query_posts_count'] );
		} elseif ( $type == self::TYPE_COMMENTS ) {
			$sum = $this->wpdb->get_var( $this->sql_queries['query_comments_count'] );
		} elseif ( $type == self::TYPE_ATTACHMENTS ) {
			$sum = $this->wpdb->get_var( $this->sql_queries['query_attachments_count'] );
		} else {
			$sum = $this->wpdb->get_var( $this->sql_queries['query_stats_count'] );
		}

		return $sum;
	}

	/**
	 * @param $type
	 * @param string $offset
	 *
	 * @return array|null|object
	 */
	private function get_content_results( $type, $offset = '' ) {
		if ( $type == self::TYPE_POST ) {

			$prepare = $this->sql_queries['query_posts_ids'];
			$ids     = $this->wpdb->get_results( $prepare . $offset, ARRAY_N );

			$query = $this->sql_queries['query_posts'];

			$id_arr = [];
			foreach ( $ids as $id ) {
				$id_arr[] = $id[0];
			}

			if ( empty( $id_arr ) ) {
				return [];
			}

			$query = str_replace( '{in_ids}', implode( ',', $id_arr ), $query );

			$results = $this->wpdb->get_results( $query, ARRAY_A );


			/* Prepare taxonomy or custom fields to valid for storing format */
			if ( $this->config->admin_options['taxonomy_indexing'] == 'true'
			     || $this->config->admin_options['custom_fields_indexing'] == 'true' ) {
				foreach ( $results as $k => $result ) {

					if ( $this->config->admin_options['taxonomy_indexing'] == 'true' ) {
						if ( ! empty( $result['taxonomy'] ) ) {
							$one_row = explode( "\n", $result['taxonomy'] );
							foreach ( $one_row as $kk => $row ) {
								$exploded_rows  = explode( '|*|', $row );
								$field_name     = array_pop( $exploded_rows );
								$one_row[ $kk ] = $this->get_key_wrapper( trim( $field_name ),
									implode( ' ', $exploded_rows ) );
							}

							$results[ $k ]['taxonomy'] = implode( ' ', $one_row );
						}
					}
					if ( $this->config->admin_options['custom_fields_indexing'] == 'true' ) {
						if ( ! empty( $result['custom_fields'] ) ) {
							$one_row = explode( "\n", $result['custom_fields'] );
							foreach ( $one_row as $kk => $row ) {
								$exploded_rows  = explode( '|*|', $row );
								$field_name     = array_pop( $exploded_rows );
								$one_row[ $kk ] = $this->get_key_wrapper( trim( $field_name ),
									implode( ' ', $exploded_rows ) );
							}

							$results[ $k ]['custom_fields'] = implode( ' ', $one_row );
						}
					}
				}
			}


		} elseif ( $type == self::TYPE_COMMENTS ) {

			$query   = $this->sql_queries['query_comments'];
			$results = $this->wpdb->get_results( $query . $offset, ARRAY_A );

		} elseif ( $type == self::TYPE_ATTACHMENTS ) {

			$query       = $this->sql_queries['query_attachments'];
			$attachments = $this->wpdb->get_results( $query . $offset, OBJECT_K );
			if ( $this->config->admin_options['attachments_indexing'] == 'true' ) {
				$results = [];
				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $k => $attachment ) {
						$post              = new WP_Post( $attachment );
						$parsed_attachment = $this->parse_attachment( $post );
						if ( ! empty( $parsed_attachment ) ) {
							$results[] = $parsed_attachment;
						}
					}
				}
			} else {
				return [];
			}
		} else {

			$query   = $this->sql_queries['query_stats'];
			$results = $this->wpdb->get_results( $query . $offset, ARRAY_A );

		}

		return $results;
	}

	private function explode_sentences( $articles, $clear = true ) {
		$content = [];

		if ( $this->autocomplete_max_id == null ) {

			$max_id = ManticoreSearch::$plugin->sphinxQL
				->select()
				->index( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $this->blog_id )
				->append_select( 'max(id) as max_id' )
				->execute()
				->get_column( 'max_id' );

			if ( empty( $max_id ) ) {
				$max_id = 0;
			}
		} else {
			$max_id = $this->autocomplete_max_id;
		}

		$posts_id = [];
		foreach ( $articles as $article ) {
			$post_id    = ! empty( $article['ID'] ) ? $article['ID'] : $article['id'];
			$posts_id[] = $post_id;


			$taxonomy      = $article['taxonomy'];
			$custom_fields = $article['custom_fields'];
			$tags          = $article['tags'];

			$article = strip_tags( $article['title'] . '. ' . $article['body'] );
			$article = str_replace( [ "\n", "\t", "\r" ], [ ' ', ' ', ' ' ], $article );

			$sentences = preg_split( '/[.?!;:]\s+/', $article, - 1, PREG_SPLIT_NO_EMPTY );

			$advanced_sentences = [];
			if ( $taxonomy != 'null' ) {
				$taxonomy           = explode( "\n", $taxonomy );
				$advanced_sentences = array_merge( $advanced_sentences, $taxonomy );
			}

			if ( $custom_fields != 'null' ) {
				$custom_fields      = explode( "\n", $custom_fields );
				$advanced_sentences = array_merge( $advanced_sentences, $custom_fields );
			}

			if ( $tags != 'null' ) {
				$tags               = explode( "\n", $tags );
				$advanced_sentences = array_merge( $advanced_sentences, $tags );
			}
			foreach ( $sentences as $sentence ) {
				if ( empty( $sentence ) || strlen( $sentence ) <= 5 ) {
					continue;
				}
				$max_id ++;
				$sentence  = trim( $sentence );
				$content[] = [
					'id'             => $max_id,
					'post_ID'        => $post_id,
					'advanced'       => 0,
					'content'        => $sentence,
					'string_content' => $sentence
				];
			}

			foreach ( $advanced_sentences as $sentence ) {
				if ( empty( $sentence ) ) {
					continue;
				}
				$max_id ++;
				$sentence  = trim( $sentence );
				$content[] = [
					'id'             => $max_id,
					'post_ID'        => $post_id,
					'advanced'       => 1,
					'content'        => $sentence,
					'string_content' => $sentence
				];
			}
		}

		$this->autocomplete_max_id = $max_id;

		if ( ! empty( $post_id ) && $clear ) {
			ManticoreSearch::$plugin->sphinxQL->deleteWhere(Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $this->blog_id, ['id', 'IN', '(' . implode( ',', $posts_id ) . ')']);
		}

		return $content;
	}

	/**
	 * @return string
	 */
	private function get_custom_indexing_fields() {

		if ( ! empty( $this->config->get_option( 'custom_fields_for_indexing' ) ) ) {

			foreach ( $this->config->get_option( 'custom_fields_for_indexing' ) as $k => $field ) {
				$fields[] = "'" . $field . "'";
			}

			if ( ! empty( $fields ) ) {
				return implode( ',', $fields );
			}
		}

		return "'manticore_non_indexing'";
	}

	/**
	 * @return string
	 */
	private function get_skip_indexing_mime_types() {

		if ( ! empty( $this->config->get_option( 'attachments_type_for_skip_indexing' ) ) ) {

			foreach ( $this->config->get_option( 'attachments_type_for_skip_indexing' ) as $k => $field ) {
				$fields[] = "'" . $field . "'";
			}

			if ( ! empty( $fields ) ) {
				return implode( ',', $fields );
			}
		}

		return "'manticore_non_indexing'";
	}

	private function get_indexing_post_types() {
		foreach ( $this->config->get_option( 'post_types_for_indexing' ) as $k => $field ) {
			$fields[] = "'" . $field . "'";
		}

		if ( ! empty( $fields ) ) {
			return implode( ',', $fields );
		}

		return "'manticore_non_indexing'";
	}

	/**
	 * @return string
	 */
	private function get_taxonomy_indexing_fields() {

		if ( $this->config->get_option( 'taxonomy_indexing' ) == 'true' ) {
			$fields = [];
			foreach ( $this->config->get_option( 'taxonomy_indexing_fields' ) as $k => $field ) {
				$fields[] = "'" . $field . "'";
			}
			if ( ! empty( $fields ) ) {
				return implode( ',', $fields );
			}
		}

		return "'manticore_non_indexing'";
	}


	/**
	 * Inserting posts id into rawdata if manticore are stopped
	 *
	 * @param string $type
	 * @param int $blog_id
	 * @param string|array $rawData
	 *
	 * @return bool
	 */
	public function set_raw_data( $type, $blog_id, $rawData ) {
		$flag = false;
		if ( ! empty( $type ) && ! empty( $rawData ) ) {
			$handle = fopen( $this->raw_directory . DIRECTORY_SEPARATOR . sha1( microtime() ) . '.dat', "wb" );
			if ( flock( $handle, LOCK_EX | LOCK_NB ) ) {
				fwrite( $handle, json_encode( $type, JSON_UNESCAPED_UNICODE ) );
				fwrite( $handle, $this->exploder );
				fwrite( $handle, json_encode( $blog_id, JSON_UNESCAPED_UNICODE ) );
				fwrite( $handle, $this->exploder );
				if ( is_string( $rawData ) ) {
					fwrite( $handle, $rawData );
				} else {
					fwrite( $handle, json_encode( $rawData, JSON_UNESCAPED_UNICODE ) );
				}
				flock( $handle, LOCK_UN );
				$flag = true;
			}
			fclose( $handle );
		}

		return $flag;
	}

	/**
	 * Return list of raw data by limit
	 *
	 * @param int $limit
	 * @param int $page
	 *
	 * @return array
	 */
	public function find_raw_data_files( $limit = 0, $page = 1 ) {

		$cmd   = 'find ' . $this->raw_directory .
		         ' -type f -iname "*.dat" -printf \'%T@ %p\n\' 2>/dev/null'
		         . ' | sort -k1 -n | awk \'{print $2}\'';
		$limit = intval( $limit );
		$page  = intval( $page );
		if ( $limit > 0 ) {
			$line_to   = $limit * $page;
			$line_from = ( $line_to - $limit ) + 1;
			$cmd       .= ' | sed -n ' . $line_from . ',' . $line_to . 'p';
		}
		$stream  = '';
		$console = popen( $cmd, "r" );
		while ( ! feof( $console ) ) {
			$stream .= fread( $console, 2048 );
		}
		fclose( $console );
		if ( ! empty( $stream ) ) {
			return explode( "\n", trim( $stream ) );
		}

		return [];
	}


	/**
	 * Returns file content
	 *
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public function get_raw_data_file( $file ) {
		if ( ! empty( $file ) ) {
			$md5_file = md5( $file );

			$current_file = [
				$md5_file => explode( $this->exploder, file_get_contents( $file ) )
			];
			if ( ! empty( $current_file[ $md5_file ][0] ) ) {
				$current_file[ $md5_file ][0] =
					json_decode( $current_file[ $md5_file ][0], true );
			} else {
				$current_file[ $md5_file ][0] = [];
			}
			if ( empty( $current_file[ $md5_file ][1] ) ) {
				$current_file[ $md5_file ][1] = '';
			}

			if ( empty( $current_file[ $md5_file ][2] ) ) {
				$current_file[ $md5_file ][2] = '';
			}

			return $current_file[ $md5_file ];
		}

		return '';
	}

	/**
	 * Delete all raw files when call "Index all posts"
	 *
	 */
	public function clear_raw_data() {
		$rawFiles = $this->find_raw_data_files();
		if ( ! empty( $rawFiles ) ) {
			foreach ( $rawFiles as $rawFile ) {
				unlink( $rawFile );
			}
		}
	}


	/**
	 * @param $comment_id
	 * @param $comment_object
	 */
	public function on_comment_inserted( $comment_id, $comment_object ) {
		$this->on_all_status_transitions( 'approved', '', $comment_object );
	}


	/**
	 * @return bool
	 */
	public function check_raw_data() {

		if ( ( ! empty( $this->config->admin_options[ WORKER_CACHE_ILS ] ) &&
		       $this->config->admin_options[ WORKER_CACHE_ILS ] == WORKER_CACHE ) ||
		     ! ManticoreSearch::$plugin->sphinxQL->is_active() ) {
			return false;
		}
		$rawFiles     = $this->find_raw_data_files( 100 );
		$skippedFiles = 0;
		if ( ! empty( $rawFiles ) ) {
			foreach ( $rawFiles as $rawFile ) {
				if ( $skippedFiles >= 10 ) {
					/**
					 * If more than 10 files are skipped,
					 * it makes no sense to continue.
					 * Something is wrong with the indexer
					 */
					return false;
				}
				$content = $this->get_raw_data_file( $rawFile );
				if ( ! empty( $content ) ) {
					if ( $content[0] == 'delete' ) {
						$deleteIndex = json_decode( $content[2], true );

						$indexResult = ManticoreSearch::$plugin->sphinxQL
							->index( Manticore_Config_Maker::MAIN_INDEX_PREFIX . $content[1] )
							->delete( $deleteIndex )
							->execute()
							->get_results();

						$acResult = ManticoreSearch::$plugin->sphinxQL
							->index( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $content[1] )
							->delete( $deleteIndex, 'post_id' )
							->execute()
							->get_results();
					} else {
						$insertIndex = json_decode( $content[2], true );
						$indexResult = ManticoreSearch::$plugin->sphinxQL
							->index( Manticore_Config_Maker::MAIN_INDEX_PREFIX . $content[1] )
							->insert( $insertIndex, [ 'title', 'body', 'category' ] )
							->execute()
							->get_results();

						$ac_sentences = $this->explode_sentences( $insertIndex );

						$chunked = array_chunk( $ac_sentences, self::INDEXING_SENTENCES_MAX_LIMIT );

						foreach ( $chunked as $chunk ) {
							$ac_update = ManticoreSearch::$plugin->sphinxQL
								->index( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $content[1] )
								->insert( $chunk, [ 'title', 'body' ] )
								->execute()
								->get_results();
						}

					}

					if ( ! empty( $indexResult ) &&
					     ( ( $indexResult['status'] == 'success' && $indexResult['results'] === false )
					       || $indexResult['status'] == 'error' ) ) {
						$skippedFiles ++;
						continue;
					}
				}
				unlink( $rawFile );
			}
		}


		if ( $this->config->admin_options['attachments_indexing'] == 'true' ) {
			$unindexed_attachments = $this->wpdb->get_results( 'SELECT * FROM ' . $this->table_prefix . 'sph_indexing_attachments WHERE indexed = 0',
				ARRAY_A );
			if ( ! empty( $unindexed_attachments ) ) {
				foreach ( $unindexed_attachments as $unindexed_attachment ) {
					$results    = [];
					$attachment = get_post( $unindexed_attachment['id'] );
					if ( in_array( $attachment->post_mime_type,
						$this->config->admin_options['attachments_type_for_skip_indexing'] ) ) {
						$this->wpdb->delete( $this->table_prefix . 'sph_indexing_attachments',
							[ 'id' => $unindexed_attachment['id'] ] );
						$this->delete_from_main_index( ManticoreSearch::set_sharding_id( $unindexed_attachment['id'],
							$this->blog_id ), $this->blog_id );
						continue;
					}
					$parsed_attachment = $this->parse_attachment( $attachment );
					if ( ! empty( $parsed_attachment ) ) {
						$results[] = $parsed_attachment;
					}
				}
				if ( ! empty( $results ) ) {
					$this->insert_to_main_index( $results, $this->blog_id );
				}
			}
		}

		return true;
	}


	/**
	 * Remove all special characters from a key
	 *
	 * @param $string
	 *
	 * @return null|string
	 */
	public static function normalize_key( $string ) {
		return preg_replace( '/[^A-Za-z0-9]/', '', $string );
	}

	/**
	 * Wrap key by special words wor taxonomy/custom fields searching
	 *
	 * @param $key
	 * @param $content
	 *
	 * @return string
	 */
	private function get_key_wrapper( $key, $content ) {
		$key             = $this->normalize_key( $key );
		$originalContent = $content;
		$content         = explode( ' ', $content );
		foreach ( $content as $k => $v ) {
			$content[ $k ] = $key . '' . $v;
		}
		$content = implode( ' ', $content );

		return self::STORED_KEY_RAND . $key . self::STORED_KEY_RAND . 'START ' . $content . ' | ' . $originalContent . ' | ' . self::STORED_KEY_RAND . $key . self::STORED_KEY_RAND . "END \n";
	}

	/**
	 * @return string
	 */
	private function get_content_taxonomy() {
		$post_string_taxonomy = 'null';
		if ( $this->config->admin_options['taxonomy_indexing'] == 'true' ) {
			$post_taxonomies = [];

			foreach ( $this->config->get_option( 'taxonomy_indexing_fields' ) as $key => $taxonomy ) {

				if ( ! empty( $_POST['tax_input'][ $taxonomy ] ) ) {

					foreach ( $_POST['tax_input'][ $taxonomy ] as $k => $item ) {
						if ( preg_match( '#^\d+$#usi', $item ) ) {
							$term = get_term( intval( $item ) );
							if ( ! empty( $term->name ) ) {
								$post_taxonomies[] = $this->get_key_wrapper( $term->taxonomy, $term->name );
							}
						} else {
							$post_taxonomies[] = $this->get_key_wrapper( $k, $item );;
						}
					}
				}

			}

			if ( ! empty( $post_taxonomies ) ) {
				$post_string_taxonomy = implode( ' ', $post_taxonomies );
			}
		}

		return $post_string_taxonomy;
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	private function get_custom_fields( $post_id ) {
		$post_string_custom_fields = 'null';
		if ( $this->config->admin_options['custom_fields_indexing'] == 'true' ) {
			$post_custom_fields = [];

			foreach ( $this->config->get_option( 'custom_fields_for_indexing' ) as $k => $custom_field ) {
				$post_custom_fields_tmp = get_post_meta( $post_id, $custom_field, false );
				if ( ! empty( $post_custom_fields_tmp ) ) {
					$post_custom_fields[] = $this->get_key_wrapper( $custom_field,
						implode( ' ', $post_custom_fields_tmp ) );
				}
			}

			if ( ! empty( $post_custom_fields ) ) {
				$post_string_custom_fields = implode( ' ', $post_custom_fields );
			}
		}

		return $post_string_custom_fields;
	}


	/**
	 * @param int $post_id
	 */
	public function on_delete_attachment( $post_id ) {
		$this->delete_from_main_index( ManticoreSearch::set_sharding_id( $post_id, $this->blog_id ), $this->blog_id );
		$this->wpdb->delete( $this->table_prefix . 'sph_indexing_attachments', [ 'id' => $post_id ] );
	}


	public function on_add_attachment( $post_id ) {
		$attachment = $this->parse_attachment( get_post( $post_id ), true );
		if ( ! empty( $attachment ) ) {
			$this->insert_to_main_index( [ $attachment ], $this->blog_id );
		}
	}


	/**
	 * @param $new_status
	 * @param $old_status
	 * @param WP_Comment|WP_Post $object
	 */
	public function on_all_status_transitions( $new_status, $old_status, $object ) {

		if ( ! empty( $this->config->admin_options[ WORKER_CACHE_ILS ] ) && $this->config->admin_options[ WORKER_CACHE_ILS ] == WORKER_CACHE ) {
			return;
		}

		if ( $this->config->admin_options['autocomplete_cache_clear'] == 'update' ) {
			$au_cache = new ManticoreAutocompleteCache();
			$au_cache->clean_cache();
		}

		$this->check_raw_data();

		if ( $object instanceof WP_Comment ) {
			$type = 'comment';
		} else {
			$type = 'post';
		}

		if ( ! empty( $this->config->admin_options[ WORKER_CACHE_ILS ] ) && $this->config->admin_options[ WORKER_CACHE_ILS ] != WORKER_CACHE &&
		     ! empty( $this->config->admin_options[ MS_TIME ] ) && ! empty( $this->config->admin_options['hash_cache_time'] ) &&
		     md5( $this->config->admin_options[ MS_TIME ] ) != $this->config->admin_options['hash_cache_time'] ) {
			$this->config->update_admin_options( [ MS_TIME => 0, 'hash_cache_time' => md5( 0 ) ] );

			return;
		}

		if ( $type == 'post' && ! in_array( $object->post_type,
				$this->config->admin_options['post_types_for_indexing'] ) ) {
			return;
		}
		if ( $new_status == 'publish' || $new_status == 'approved' ) {
			$post_string_taxonomy = $this->get_content_taxonomy();
			$object_type          = 2;

			if ( $type == 'post' ) {
				$post_custom_fields = $this->get_custom_fields( $object->ID );
				$tags               = wp_get_post_tags( $object->ID );
				$categories         = wp_get_post_categories( $object->ID, [ 'fields' => 'all' ] );

				$tag_list = 'null';
				if ( ! empty( $tags ) ) {
					$tag_list = [];
					foreach ( $tags as $tag ) {
						$tag_list[] = $tag->name;
					}
					$tag_list = implode( ',', $tag_list );
				}

				$categories_list = 'null';
				if ( ! empty( $categories ) ) {
					$categories_list = [];
					foreach ( $categories as $category ) {
						$categories_list[] = $category->name;
					}
					$categories_list = implode( ',', $categories_list );
				}

				if ( $object->post_type == 'post' ) {
					$object_type = 0;
				} elseif ( $object->post_type == 'page' ) {
					$object_type = 1;
				}

				$newPostIndex[] = [
					'id'            => ManticoreSearch::set_sharding_id( $object->ID, $this->blog_id ),
					'blog_id'       => $this->blog_id,
					'comment_ID'    => 0,
					'post_ID'       => $object->ID,
					'title'         => $object->post_title,
					'body'          => $object->post_content,
					'category'      => ! empty( $categories_list ) ? $categories_list : 'null',
					'taxonomy'      => $post_string_taxonomy,
					'custom_fields' => $post_custom_fields,
					'isPost'        => $object_type == 0 ? 1 : 0,
					'isComment'     => 0,
					'isPage'        => $object_type == 1 ? 1 : 0,
					'post_type'     => $object_type,
					'date_added'    => strtotime( $object->post_date ),
					'tags'          => ! empty( $tag_list ) ? $tag_list : 'null'
				];
			} else {
				$newPostIndex[] = [
					'id'            => ManticoreSearch::set_sharding_id( $object->comment_ID, $this->blog_id, true ),
					'blog_id'       => $this->blog_id,
					'comment_ID'    => $object->comment_ID,
					'post_ID'       => $object->comment_post_ID,
					'title'         => $object->comment_title,
					'body'          => $object->comment_content,
					'attachments'   => 'null',
					'category'      => 'null',
					'taxonomy'      => $post_string_taxonomy,
					'custom_fields' => 'null',
					'isPost'        => $object_type == 0 ? 1 : 0,
					'isComment'     => $type == 'post' ? 0 : 1,
					'isPage'        => $object_type == 1 ? 1 : 0,
					'post_type'     => $object_type,
					'date_added'    => strtotime( $object->comment_date ),
					'tags'          => 'null'
				];
			}

			$this->insert_to_main_index( $newPostIndex, $this->blog_id );
		}
		if ( in_array( $new_status, [ 'trash', 'spam', 'unapproved', 'delete' ] ) ) {
			if ( $type == 'post' ) {
				$ids[] = ManticoreSearch::set_sharding_id( $object->ID, $this->blog_id );

				$children_attachments = get_children( [
					'post_parent' => $object->ID,
					'post_type'   => 'attachment'
				] );

				if ( ! empty( $children_attachments ) ) {
					foreach ( $children_attachments as $attachment ) {
						$ids[] = ManticoreSearch::set_sharding_id( $attachment->ID, $this->blog_id );
					}
				}
			} else {
				$ids[] = ManticoreSearch::set_sharding_id( $object->comment_ID, $this->blog_id, true );
			}
			foreach ( $ids as $id ) {
				$this->delete_from_main_index( $id, $this->blog_id );
			}
		}
		// A function to perform actions any time any post changes status.
	}


	/**
	 * @param WP_Post $attachment
	 * @param bool $reparse
	 *
	 * @return array
	 */
	private function parse_attachment( $attachment, $reparse = false ) {

		if ( $this->config->admin_options['attachments_indexing'] == 'true' ) {

			$file_name = get_attached_file( $attachment->ID );
			if ( empty( $file_name ) || ! file_exists( $file_name ) ||
			     in_array( $attachment->post_mime_type,
				     $this->config->admin_options['attachments_type_for_skip_indexing'] ) ) {

				return [
					'id'            => ManticoreSearch::set_sharding_id( $attachment->ID, $this->blog_id ),
					'blog_id'       => $this->blog_id,
					'comment_ID'    => 0,
					'post_ID'       => $attachment->ID,
					'title'         => $attachment->post_title,
					'body'          => $attachment->post_content,
					'category'      => 'null',
					'taxonomy'      => 'null',
					'custom_fields' => 'null',
					'isPost'        => 1,
					'isComment'     => 0,
					'isPage'        => 0,
					'post_type'     => 0,
					'date_added'    => $attachment->post_date,
					'tags'          => ''
				];
			}

			$hash        = sha1_file( $file_name );
			$cached_data = $this->wpdb->get_row( 'SELECT * FROM `' . $this->table_prefix . 'sph_indexing_attachments` WHERE id = ' . $attachment->ID,
				ARRAY_A );
			if ( $reparse == false && ! empty( $cached_data ) && $cached_data['hash'] == $hash && $cached_data['indexed'] == 1 ) {
				$attachment_content = $cached_data['content'];
			} else {

				$secure_key = $this->config->admin_options['secure_key'];
				$indexed    = 0;
				if ( file_exists( $file_name ) ) {
					$file = fopen( $file_name, 'r' );
					if ( false === $file ) {
						$response = new WP_Error( 'fopen', 'Could not open the file for reading.' );
					} else {
						$file_size = filesize( $file_name );
						$file_data = fread( $file, $file_size );

						$args          = [
							'headers' => [
								'accept'        => 'application/json',   // The API returns JSON.
								'content-type'  => 'application/binary', // Set content type to binary.
								'Authorization' => 'Basic ' . base64_encode( "webuser:1tn@M" )
							],
							'body'    => $file_data,
							'timeout' => 5,
						];
						$response      = wp_remote_post( self::PARSE_SERVER . 'index.php?secure_key=' . $secure_key,
							$args );
						$response_code = wp_remote_retrieve_response_code( $response );
						if ( $response_code == 200 ) {
							$indexed = 1;
						}

					}
				} else {
					$response = new WP_Error( 'file_exists', 'Could not find attachment file.' );
				}


				$content = $this->handle_server_response( $response );
				if ( empty( $content ) && ! empty( $this->errors ) ) {
					$indexed      = 0;
					$this->errors = [];
				}

				$attachment_content    = $content;
				$attachment_cache_data = [
					'id'        => $attachment->ID,
					'indexed'   => $indexed,
					'parent_id' => $attachment->post_parent,
					'content'   => $content,
					'hash'      => $hash
				];
				$this->wpdb->replace( $this->table_prefix . 'sph_indexing_attachments', $attachment_cache_data );
			}


			return [
				'id'            => ManticoreSearch::set_sharding_id( $attachment->ID, $this->blog_id ),
				'blog_id'       => $this->blog_id,
				'comment_ID'    => 0,
				'post_ID'       => $attachment->ID,
				'title'         => $attachment->post_title,
				'body'          => $attachment->post_content . ' ' . $attachment_content,
				'category'      => 'null',
				'taxonomy'      => 'null',
				'custom_fields' => 'null',
				'isPost'        => 1,
				'isComment'     => 0,
				'isPage'        => 0,
				'post_type'     => 0,
				'date_added'    => $attachment->post_date,
				'tags'          => ''
			];
		}

		return [];
	}


	/**
	 * @param WP_Error|array $response
	 *
	 * @return string|null
	 */
	function handle_server_response( $response ) {
		$success = null;
		$result  = '';
		if ( is_wp_error( $response ) ) {
			$this->errors[] = $response->get_error_message();
		} else {
			if ( isset( $response['body'] ) ) {
				$content = $response['body'];
				$content = json_decode( $content, true );

				if ( isset( $content['status'] ) && $content['status'] == 'error' ) {
					$this->config->update_admin_options( [ 'w_time' => $content['w_time'] ] );
					$this->errors[] = $content['result'];
				} else {

					$result = $content['result'];
				}
			}
		}

		return $result;
	}


	private function delete_from_main_index( $post_id, $blog_id ) {
		$manticore_is_active = ManticoreSearch::$plugin->sphinxQL->is_active();

		if ( $manticore_is_active ) {

			$indexResult = ManticoreSearch::$plugin->sphinxQL
				->index( Manticore_Config_Maker::MAIN_INDEX_PREFIX . $blog_id )
				->delete( $post_id )
				->execute()
				->get_results();

			$acResult = ManticoreSearch::$plugin->sphinxQL
				->index( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $blog_id )
				->delete( $post_id, 'post_id' )
				->execute()
				->get_results();
		}

		if ( $manticore_is_active === false || ! empty( $indexResult ) &&
		                                       ( ( $indexResult['status'] == 'success' && $indexResult['results'] === false )
		                                         || $indexResult['status'] == 'error' ) ) {

			/** Error handling, saving to temp storage */
			$this->set_raw_data( 'delete', $blog_id, $post_id );
		}
	}

	private function insert_to_main_index( $data, $blog_id ) {
		$manticore_is_active = ManticoreSearch::$plugin->sphinxQL->is_active();
		if ( $manticore_is_active ) {
			$indexResult = ManticoreSearch::$plugin->sphinxQL
				->index( Manticore_Config_Maker::MAIN_INDEX_PREFIX . $blog_id )
				->insert( $data, [
					'title',
					'body',
					'category',
					'tags',
					'taxonomy',
					'custom_fields',
					'attachments'
				] )
				->execute()
				->get_results();

			$ac_sentences = $this->explode_sentences( $data );

			$chunked = array_chunk( $ac_sentences, self::INDEXING_SENTENCES_MAX_LIMIT );

			foreach ( $chunked as $chunk ) {
				$ac_update = ManticoreSearch::$plugin->sphinxQL
					->index( Manticore_Config_Maker::AUTOCOMPLETE_INDEX_PREFIX . $blog_id )
					->insert( $chunk, [
						'content',
						'string_content'
					] )
					->execute()
					->get_results();
			}

		}

		if ( $manticore_is_active === false || ! empty( $indexResult ) &&
		                                       ( ( $indexResult['status'] == 'success' && $indexResult['results'] === false )
		                                         || $indexResult['status'] == 'error' ) ) {

			/** Error handling, saving to temp storage */
			$this->set_raw_data( 'insert', $blog_id, $data );
		}
	}


	private function add_to_indexing_log( $data ) {
		file_put_contents( $this->error_log_path, date( 'Y-m-d H:i:s' ) . ' ' . $data, FILE_APPEND );
	}

	private function delete_indexing_log() {
		if ( file_exists( $this->error_log_path ) ) {
			unlink( $this->error_log_path );
		}
	}


	// функция обработки ошибок
	public function my_error_handler( $errno, $errstr, $errfile, $errline ) {
		if ( ! ( error_reporting() & $errno ) ) {
			return false;
		}

		switch ( $errno ) {
			case E_USER_ERROR:
				$this->add_to_indexing_log(
					"<b>Пользовательская ОШИБКА</b> [$errno] $errstr<br />\n" .
					"  Фатальная ошибка в строке $errline файла $errfile" .
					", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n" .
					"Завершение работы...<br />\n" );
				exit( 1 );
				break;

			case E_USER_WARNING:
				$this->add_to_indexing_log(
					"<b>Пользовательское ПРЕДУПРЕЖДЕНИЕ</b> [$errno] $errstr<br />\n" );
				break;

			case E_USER_NOTICE:
				$this->add_to_indexing_log(
					"<b>Пользовательское УВЕДОМЛЕНИЕ</b> [$errno] $errstr<br />\n" );
				break;

			default:
				$this->add_to_indexing_log(
					"Неизвестная ошибка: [$errno] $errfile:$errline $errstr<br />\n" );
				break;
		}

		return true;
	}

	public function update( SplSubject $subject ) {
		$this->config = $subject;
	}
}