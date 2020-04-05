<?php
/*
    Manticore Search Plugin (contact@manticoresearch.com), 2018
    If you need commercial support, or if you’d like this plugin customized for your needs, we can help.

    Visit our website for the latest news:
    https://manticoresearch.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Office 2, Derby House, 123 Watling Street, Gillingham, Kent, ME7 2YY England
*/

class Manticore_FrontEnd {
	/**
	 * Manticore Search Results
	 */
	var $search_results = [];

	/**
	 * Posts info returned by Sphinx
	 *
	 * @var array
	 */
	var $posts_info = array();

	/**
	 * Total posts found
	 *
	 * @var int
	 */
	var $post_count = 0;

	/**
	 *  Search keyword
	 *
	 * @var string
	 */
	var $search_string = '';

	/**
	 * Search keyword (as it was specified by user)
	 *
	 * @var string
	 */
	var $search_string_original = '';

	/**
	 * Search params
	 */
	var $params = array();

	/**
	 * Config object
	 */
	var $config = '';

	/**
	 * IS searchd running
	 */
	var $is_searchd_up = true;

	var $top_ten_is_related = false;

	/**
	 * IS search mode MATCH ANY
	 *
	 * @var boolean
	 */
	var $used_match_any = false;


	private $sphinxQL;
	/**
	 * Post/Pages/Comments count variables
	 */
	var $posts_count = 0;
	var $pages_count = 0;
	var $comments_count = 0;

	/**
	 *
	 *
	 */
	var $_top_ten_total = 0;

	private $blog_id = 1;
	private $blog_urls = [];

	/**
	 * Delegate config object from SphinxSearch_Config class
	 * get search keyword from GET parameters
	 *
	 * @param Manticore_Config $config
	 *
	 */
	function __construct( $config ) {

		//initialize config
		$this->config = $config;

		if ( ! isset( $_GET['search_comments'] )
		     && ! isset( $_GET['search_posts'] )
		     && ! isset( $_GET['search_pages'] )
		     && ! isset( $_GET['search_tags'] )
		) {
			$this->params['search_comments'] = $this->config->admin_options['search_comments'] == 'false' ? '' : 'true';
			$this->params['search_posts']    = $this->config->admin_options['search_posts'] == 'false' ? '' : 'true';
			$this->params['search_pages']    = $this->config->admin_options['search_pages'] == 'false' ? '' : 'true';
			$this->params['search_tags']     = $this->config->admin_options['search_tags'] == 'false' ? '' : 'true';
		} else {
			$this->params['search_comments'] = isset( $_GET['search_comments'] ) ? esc_sql( $_GET['search_comments'] ) : false;
			$this->params['search_posts']    = isset( $_GET['search_posts'] ) ? esc_sql( $_GET['search_posts'] ) : false;
			$this->params['search_pages']    = isset( $_GET['search_pages'] ) ? esc_sql( $_GET['search_pages'] ) : false;
			$this->params['search_tags']     = isset( $_GET['search_tags'] ) ? esc_sql( $_GET['search_tags'] ) : false;
		}

		if ( $this->config->admin_options['search_sorting'] == 'user_defined' ) {

			if ( isset( $_GET['search_sortby_relevance'] ) && ! isset( $_GET['search_sortby_date'] ) ) {
				$this->params['search_sortby'] = 'relevance';
			} elseif ( ! isset( $_GET['search_sortby_relevance'] ) && isset( $_GET['search_sortby_date'] ) ) {
				$this->params['search_sortby'] = 'date';
			} else {
				$this->params['search_sortby'] = 'date_relevance';
			}

		} else {
			$this->params['search_sortby'] = $this->config->admin_options['search_sorting'];
		}

		$this->blog_id = get_current_blog_id();
		if ( ManticoreSearch::is_network() == 'true' ) {
			$this->blog_urls = ManticoreSearch::get_network_sites( false );
		}
	}

	/**
	 * Make Query to Sphinx search daemon and return result ids
	 *
	 * @param string $search_string
	 *
	 * @return array|$this
	 */
	function query( $search_string ) {

		global $wp_query;

		// checks weither SEO URLs are being used
		if ( $this->config->get_option( 'seo_url_all' ) == 'true' ) {
			$search_string = str_replace( '_', "'", $search_string );
		}

		$this->search_string_original = $search_string;
		$this->search_string          = $search_string;

		$sphinx = ManticoreSearch::$plugin->sphinxQL;


		////////////
		// set filters
		////////////

		$typeFilters = [];

		if ( ! empty( $this->params['search_comments'] ) ) {
			$typeFilters['isComment'] = 1;
		}

		if ( ! empty( $this->params['search_pages'] ) ) {
			$typeFilters['isPage'] = 1;
		}

		if ( ! empty( $this->params['search_posts'] ) ) {
			$typeFilters['isPost'] = 1;
		}

		if ( ! empty( $typeFilters ) ) {
			$sphinx->add_or_filter( $typeFilters );
		}

		if ( in_array( $this->params['search_sortby'], [ 'date', 'date_relevance' ] ) ) {

			if ( $this->params['search_sortby'] == 'date' ) {
				$this->params['search_sortby'] = 'date_added';
			}

			$sphinx->sort( $this->params['search_sortby'] );
		}

		////////////
		// set limits
		////////////

		$searchpage     = ( ! empty( $wp_query->query_vars['paged'] ) )
			? $wp_query->query_vars['paged'] : 1;
		$posts_per_page = intval( get_option( 'posts_per_page' ) );
		$offset         = intval( ( $searchpage - 1 ) * $posts_per_page );
		$sphinx->limits( $posts_per_page, $offset, $this->config->admin_options['sphinx_max_matches'] );

		////////////
		// do query
		////////////

		//replace key-buffer to key buffer
		//replace key -buffer to key -buffer
		//replace key- buffer to key buffer
		//replace key - buffer to key buffer
		$this->search_string = $this->unify_keywords( $this->search_string );

		$this->search_string = html_entity_decode( $this->search_string, ENT_QUOTES );


		if ( strpos( $this->search_string, 'tax:' ) === 0 ) {

			$type_list        = $this->config->admin_options['taxonomy_indexing_fields'];
			$short_name       = 'tax:';
			$short_field_name = '@taxonomy ';
		}

		if ( strpos( $this->search_string, 'field:' ) === 0 ) {

			$type_list        = $this->config->admin_options['custom_fields_for_indexing'];
			$short_name       = 'field:';
			$short_field_name = '@custom_fields ';
		}

		if ( strpos( $this->search_string, 'tag:' ) === 0 ) {
			$tags_names = get_tags();
			$tags       = [];
			if ( ! empty( $tags_names ) ) {
				foreach ( $tags_names as $tag ) {
					$tags[] = $tag->slug;
				}
			}

			$type_list        = $tags;
			$short_name       = 'tag:';
			$short_field_name = '@tags ';
		}


		$originalQuery         = $this->search_string;
		$normalized_field_name = 'null';
		if ( ! empty( $type_list ) ) {
			foreach ( $type_list as $name ) {
				if ( strpos( $this->search_string, $short_name . $name ) !== false ) {

					$field_name            = $name;
					$normalized_field_name = Manticore_Indexing::normalize_key( $field_name );

					$this->search_string = str_replace( $short_name . $name, '', $this->search_string );

					$explodedRequest         = explode( ' ', trim( $this->search_string ) );
					$this->query_words_count = count( $explodedRequest );

					if ( ! empty( $explodedRequest[0] ) ) {

						if ( ! empty( $explodedRequest ) ) {
							foreach ( $explodedRequest as $k => $item ) {
								$explodedRequest[ $k ] = $normalized_field_name . $item;
							}
						}
						$this->search_string = implode( ' ', $explodedRequest );
					}

					break;
				}
			}

			$startUniqueString = Manticore_Indexing::STORED_KEY_RAND . $normalized_field_name . Manticore_Indexing::STORED_KEY_RAND . 'START';
			$endUniqueString   = Manticore_Indexing::STORED_KEY_RAND . $normalized_field_name . Manticore_Indexing::STORED_KEY_RAND . "END";

			$this->search_string = $short_field_name . $startUniqueString . ' << ' . $this->search_string . ' << ' . $endUniqueString;
		}


		$res = $sphinx
			->select()
			->index( Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX . $this->blog_id )
			->match( $this->search_string )
			->field_weights( $this->config->admin_options['weight_fields'] )
			->execute()
			->get_all();

		if ( empty( $res ) && $this->is_simple_query( $this->search_string ) ) {

			$explodedQuery = explode( ' ', $this->search_string );
			if ( ! empty( $explodedQuery[1] ) ) {
				$query = implode( ' | ', $explodedQuery );
			} else {
				$query = $this->search_string;
			}

			$res = $sphinx
				->select()
				->index( Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX . $this->blog_id )
				->match( $query )
				->field_weights( $this->config->admin_options['weight_fields'] )
				->execute()
				->get_all();

			$this->used_match_any = true;
		}

		$this->search_string = $originalQuery;

		//to do something usefull with error
		if ( $res === false ) {
			$error = $sphinx->get_query_error();
			if ( preg_match( '/connection/', $error ) and preg_match( '/failed/', $error ) ) {
				$this->is_searchd_up = false;
			}

			return array();
		}
		////////////
		// try match any and save search string
		////////////
		$partial_keyword_match_or_adult_keyword = false;
		if ( ( strtolower( $this->search_string ) !=
		       $this->clear_censor_keywords( $this->search_string ) ) ||
		     $this->used_match_any === true ) {
			$partial_keyword_match_or_adult_keyword = true;
		}

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'] ) !== false ) {
			// make new query without filters
			if ( empty( $res ) ) {
				$this->used_match_any = false;

				$res_tmp = $sphinx
					->select()
					->index( Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX . $this->blog_id )
					->match( $this->search_string )
					->field_weights( $this->config->admin_options['weight_fields'] )
					->limits( 1, 0 )
					->execute()
					->get_all();

				//to do something usefull with error
				if ( $res_tmp === false ) {
					$error = $sphinx->get_query_error();
					if ( preg_match( '/connection/', $error ) and preg_match( '/failed/', $error ) ) {
						$this->is_searchd_up = false;
					}

					return array();
				}
				if ( is_array( $res_tmp ) && $partial_keyword_match_or_adult_keyword === false ) {
					$this->insert_sphinx_stats( $this->search_string );
				}
			} elseif ( $partial_keyword_match_or_adult_keyword === false ) {
				$this->insert_sphinx_stats( $this->search_string );
			}
		}

		//if no posts found return empty array
		if ( empty( $res ) ) {
			return [];
		}

		//group results
		$sphinx->clear();

		$res_tmp = $sphinx
			->select()
			->index( Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX . $this->blog_id )
			->match( $this->search_string )
			->field_weights( $this->config->admin_options['weight_fields'] )
			->limits( 1000, 0 )
			->group( 'post_type', 'count', 'desc' )
			->execute()
			->get_all();

		if ( ! empty( $res_tmp ) ) {
			foreach ( $res_tmp as $m ) {
				switch ( $m['post_type'] ) {
					case '0':
						$this->posts_count = $m['count'];
						break;
					case '1':
						$this->pages_count = $m['count'];
						break;
					case '2':
						$this->comments_count = $m['count'];
						break;
				}
			}

			//save matches
		}

		$this->search_results = $res;

		return $this;
	}

	/**
	 * Is query simple, if yes we use match any mode if nothing found in extended mode
	 *
	 * @param string $query
	 *
	 * @return boolean
	 */
	function is_simple_query( $query ) {
		$stopWords = array( '@title', '@body', '@category', '!', '-', '~', '(', ')', '|', '"', '/' );
		foreach ( $stopWords as $st ) {
			if ( strpos( $query, $st ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Parse matches and collect posts ids and comments ids
	 *
	 */
	function parse_results() {


		$content = [ 'posts' => [] ];
		if ( ! empty( $this->search_results ) ) {
			foreach ( $this->search_results as $val ) {
				if ( empty( $val['comment_id'] ) ) {
					$content['posts'][] = [
						'post_id'    => $val['post_id'],
						'blog_id'    => $val['blog_id'],
						'weight'     => $val['weight'],
						'comment_id' => 0,
						'is_comment' => 0
					];
				} else {
					$content['posts'][] = [
						'post_id'    => $val['post_id'],
						'blog_id'    => $val['blog_id'],
						'weight'     => $val['weight'],
						'comment_id' => $val['comment_id'],
						'is_comment' => 1
					];
				}
			}
		}


		$this->posts_info = $content['posts'];
		$this->post_count = count( $this->search_results );

		return $this;
	}

	/**
	 * Make new posts based on our Manticore Search Results
	 *
	 * @return array $posts
	 */
	function posts_results() {
		global $wpdb, $table_prefix;
		////////////////////////////
		//fetching coments and posts data
		////////////////////////////

		$posts_ids    = array();
		$comments_ids = array();
		foreach ( $this->posts_info as $p ) {
			if ( $p['is_comment'] ) {
				$comments_ids[ $p['blog_id'] ][]                          = $p['comment_id'];
				$comments_sorted_keys[ $p['blog_id'] . $p['comment_id'] ] = 1;
			}
			$posts_ids[ $p['blog_id'] ][]                       = $p['post_id'];
			$posts_sorted_keys[ $p['blog_id'] . $p['post_id'] ] = 1;
		}
		$posts_data = array();


		if ( ! empty( $posts_ids ) ) {
			$queries = [];
			/**
			 * @var int $blog_id
			 * @var array $post_id
			 */
			foreach ( $posts_ids as $blog_id => $blog_posts_ids ) {


				if ( $blog_id > 1 ) {
					$blog_prefix = preg_replace( '#_(\d+)_$#', '_' . $blog_id . '_', $table_prefix, 1 );

					$queries[] = 'SELECT *, ' . $blog_id . ' as blog_id FROM ' . $blog_prefix . 'posts wpposts' . $blog_id
					             . ' LEFT JOIN ' . $blog_prefix . 'sph_indexing_attachments att' . $blog_id
					             . ' ON wpposts' . $blog_id . '.ID = att' . $blog_id . '.id' .
					             ' WHERE  wpposts' . $blog_id . '.ID in (' . implode( ',', $blog_posts_ids ) . ')';
				} else {
					$queries[] = 'SELECT *, ' . $blog_id . ' as blog_id FROM ' . $wpdb->base_prefix . 'posts wp 
							LEFT JOIN ' . $wpdb->base_prefix . 'sph_indexing_attachments att 
							ON wp.ID = att.id 
							WHERE wp.ID in (' . implode( ',', $blog_posts_ids ) . ')';
				}


			}
			$query      = implode( ' UNION ', $queries );
			$posts_data = $wpdb->get_results( $query );

			foreach ( $posts_data as $item ) {
				$posts_sorted_keys[ $item->blog_id . $item->ID ] = $item;
			}
			$posts_data = $posts_sorted_keys;
		}


		$comments_data = array();
		if ( ! empty( $comments_ids ) ) {
			$queries = [];
			foreach ( $comments_ids as $blog_id => $blog_comments_id ) {
				if ( $blog_id > 1 ) {
					$blog_prefix = preg_replace( '#_(\d+)_$#', '_' . $blog_id . '_', $table_prefix, 1 );

					$queries [] = 'SELECT *, ' . $blog_id . ' as blog_id FROM ' . $blog_prefix
					              . 'comments com' . $blog_id . ' WHERE com' . $blog_id . '.comment_ID in (' .
					              implode( ',', $blog_comments_id ) . ")";
				} else {
					$queries [] = 'SELECT *, ' . $blog_id . ' as blog_id FROM ' . $wpdb->base_prefix
					              . 'comments com WHERE com.comment_ID in (' . implode( ',', $blog_comments_id ) . ")";
				}
			}
			$query = implode( ' UNION ', $queries );

			$comments_data = $wpdb->get_results( $query );

			foreach ( $comments_data as $item ) {
				$comments_sorted_keys[ $item->blog_id . $item->comment_ID ] = $item;
			}
			$comments_data = $comments_sorted_keys;
		}

		unset( $posts_ids );
		unset( $comments_ids );

		////////////////////////////
		//Make assoc array of
		//posts and comments data
		////////////////////////////

		$posts_content    = array();
		$posts_titles     = array();
		$posts_data_assoc = array();
		$comments_content = array();
		foreach ( $posts_data as $k => $p ) {
			//make id as indexes
			$posts_data_assoc[ $p->blog_id . $p->ID ] = $p;
			if ( $p->post_type == 'attachment' ) {
				$p->post_content .= PHP_EOL . $p->content;
			}
			$posts_content[ $p->blog_id .'|'. $p->ID ] = $p->post_content;
			$posts_titles[ $p->blog_id .'|'. $p->ID ]  = $p->post_title;
		}
		foreach ( $comments_data as $c ) {
			$comments_content[ $c->blog_id .'|'. $c->comment_ID ]                          = $c->comment_content;
			$comments_content_data[ $c->blog_id . $c->comment_ID ]['comment_date']     = $c->comment_date;
			$comments_content_data[ $c->blog_id . $c->comment_ID ]['comment_date_gmt'] = $c->comment_date_gmt;
			$comments_content_data[ $c->blog_id . $c->comment_ID ]['comment_author']   = $c->comment_author;
		}

		unset( $posts_data );
		unset( $comments_data );

		////////////////////////////
		//excerpts of contents
		//and titles
		////////////////////////////

		$posts_content_excerpt    = $this->get_excerpt( $posts_content );
		$posts_titles_excerpt     = $this->get_excerpt( $posts_titles, true );
		$comments_content_excerpt = $this->get_excerpt( $comments_content );
		//check if server is down
		if ( $posts_content_excerpt === false || $posts_titles_excerpt === false || $comments_content_excerpt === false ) {
			return null;
		}

		unset( $posts_content );
		unset( $posts_titles );
		unset( $comments_content );
		////////////////////////////
		//merge posts and comments
		//excerpts into gloabl
		//posts array
		////////////////////////////

		$posts = array();
		foreach ( $this->posts_info as $post ) {
			$posts_data_assoc_arry = array();
			$pID                   = $post['post_id'];
			$blogID                = $post['blog_id'];
			if ( is_object( $posts_data_assoc[ $blogID . $pID ] ) ) {
				$posts_data_assoc_arry[ $blogID . $pID ] = get_object_vars( $posts_data_assoc[ $blogID . $pID ] );
			}

			//it is comment
			if ( $post['is_comment'] ) {
				$cID = $post['comment_id'];

				$posts_data_assoc_arry[ $blogID . $pID ]['post_content'] = $comments_content_excerpt[ $blogID .'|'. $cID ];
				$posts_data_assoc_arry[ $blogID . $pID ]['post_excerpt'] = $comments_content_excerpt[ $blogID .'|'. $cID ];

				$posts_data_assoc_arry[ $blogID . $pID ]['post_title']         = strip_tags( $posts_titles_excerpt[ $blogID .'|'. $pID ] );
				$posts_data_assoc_arry[ $blogID . $pID ]['sphinx_post_title']  = $this->config->admin_options['before_comment'] . $posts_titles_excerpt[ $blogID .'|'. $pID ];
				$posts_data_assoc_arry[ $blogID . $pID ]['comment_id']         = $cID;
				$posts_data_assoc_arry[ $blogID . $pID ]['post_date_orig']     = $posts_data_assoc_arry[ $blogID . $pID ]['post_date'];
				$posts_data_assoc_arry[ $blogID . $pID ]['post_date_gmt_orig'] = $posts_data_assoc_arry[ $blogID . $pID ]['post_date_gmt'];
				$posts_data_assoc_arry[ $blogID . $pID ]['post_date']          = $comments_content_data[ $blogID . $cID ]['comment_date'];
				$posts_data_assoc_arry[ $blogID . $pID ]['comment_author']     = $comments_content_data[ $blogID . $cID ]['comment_author'];
				$posts_data_assoc_arry[ $blogID . $pID ]['comment_date']       = $comments_content_data[ $blogID . $cID ]['comment_date'];
				$posts[]                                                       = $posts_data_assoc_arry[ $blogID . $pID ];
			} else {
				$posts_data_assoc_arry[ $blogID . $pID ]['post_content'] = $posts_content_excerpt[ $blogID .'|'. $pID ];
				$posts_data_assoc_arry[ $blogID . $pID ]['post_excerpt'] = $posts_content_excerpt[ $blogID .'|'. $pID ];
				if ( 'page' == $posts_data_assoc_arry[ $blogID . $pID ]['post_type'] ) {
					$posts_data_assoc_arry[ $blogID . $pID ]['post_title']        = strip_tags( $posts_titles_excerpt[ $blogID .'|'. $pID ] );
					$posts_data_assoc_arry[ $blogID . $pID ]['sphinx_post_title'] = $this->config->admin_options['before_page'] . $posts_titles_excerpt[ $blogID .'|'. $pID ];
				} else {
					$posts_data_assoc_arry[ $blogID . $pID ]['post_title']        = strip_tags( $posts_titles_excerpt[ $blogID .'|'. $pID ] );
					$posts_data_assoc_arry[ $blogID . $pID ]['sphinx_post_title'] = $this->config->admin_options['before_post'] . $posts_titles_excerpt[ $blogID .'|'. $pID ];
				}
				$posts[] = $posts_data_assoc_arry[ $blogID . $pID ];
			}
		}

		////////////////////////////
		//Convert posts array to
		//posts object required by WP
		////////////////////////////

		$obj_posts = array();
		foreach ( $posts as $index => $post ) {
			foreach ( $post as $var => $value ) {
				if ( ! isset( $obj_posts[ $index ] ) ) {
					$obj_posts[ $index ] = new stdClass();
				}
				$obj_posts[ $index ]->$var = $value;
			}

			if ( ! empty( $obj_posts[ $index ]->post_excerpt ) ) {
				$post_id                           = $obj_posts[ $index ]->ID;
				$blog_id                           = $obj_posts[ $index ]->blog_id;
				$content                           = $posts_data_assoc[ $blog_id . $post_id ]->post_content;
				$obj_posts[ $index ]->post_excerpt .= $this->get_bottom_links( $content, get_permalink( $obj_posts[ $index ]->ID ) );
			}
		}

		return $obj_posts;
	}


	function get_bottom_links( $content, $url = '/' ) {
		$links = '';

		$unique_links = [];
		preg_match_all( '#<h[1-6]+.{0,25}>(.{1,20})</h[1-6]+>#usi', $content, $matches );
		if ( ! empty( $matches[0][0] ) && ! empty( $matches[1][0] ) ) {
			$links .= '<div class="bottom-links">';
			foreach ( $matches[0] as $k => $match ) {
				if ( in_array( $matches[1][ $k ], $unique_links ) ) {
					continue;
				}
				$unique_links[] = $matches[1][ $k ];
				$links          .= '<a href="' . $url . '#s_' . $k . '">' . $matches[1][ $k ] . '</a> ';
			}
			$links .= '</div>';
		}


		return $links;
	}


	/**
	 * Return modified blog title
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	function wp_title( $title = '' ) {
		return urldecode( $title );
	}

	/**
	 * Return modified post title
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	function the_title( $title = '' ) {
		global $post;

		if ( ! is_search() || ! in_the_loop() ) {
			return $title;
		}

		return $post->sphinx_post_title;
	}


	/**
	 * Custom title Tag for post title
	 *
	 * @return string
	 */
	function sphinx_the_title() {
		return the_title();
	}

	/**
	 * Replace post time to commen time
	 *
	 * @param string $the_time - post time
	 * @param string $d - time format
	 *
	 * @return string
	 */
	function the_time( $the_time, $d ) {
		global $post;
		if ( ! $post->comment_id ) {
			return $the_time;
		}
		if ( $d == '' ) {
			$the_time = date( get_option( 'time_format' ), strtotime( $post->comment_date ) );
		} else {
			$the_time = date( $d, strtotime( $post->comment_date ) );
		}

		return $the_time;
	}

	/**
	 * Replace post author name to comment author name
	 *
	 * @param string $display_name - post author name
	 *
	 * @return string
	 */
	function the_author( $display_name ) {
		global $post;
		if ( empty( $post->comment_id ) ) {
			return $display_name;
		}

		return $post->comment_author;
	}

	/**
	 * Return modified permalink for comments
	 *
	 * @param string $permalink
	 *
	 * @return string
	 */
	function the_permalink( $permalink = '' ) {
		global $post;

		if ( ! empty( $post->comment_id ) ) {
			return $permalink . '#comment-' . $post->comment_id;
		} else {
			return $permalink;
		}
	}

	/**
	 * Correct date time for comment records in search results
	 *
	 * @param string $permalink
	 * @param object $post usually null so we use global post object
	 *
	 * @return string
	 */
	function post_link( $permalink, $post = null ) {
		global $post;


		if ( empty( $post->comment_id ) ) {

			if ( ! empty( $post->blog_id ) && $post->blog_id != $this->blog_id ) {
				$permalink = str_replace( '/' . $this->blog_urls[ $this->blog_id ] . '/',
					'/' . $this->blog_urls[ $post->blog_id ] . '/', $permalink );
			}

			return $permalink;
		}

		$rewritecode = array(
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			'%postname%',
			'%post_id%',
			'%category%',
			'%author%',
			'%pagename%'
		);

		$permalink = get_option( 'permalink_structure' );

		if ( '' != $permalink && ! in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
			//Fix comment date to post date
			$unixtime = strtotime( $post->post_date_orig );

			$category = '';
			if ( strpos( $permalink, '%category%' ) !== false ) {
				$cats = get_the_category( $post->ID );
				if ( $cats ) {
					usort( $cats, '_usort_terms_by_ID' ); // order by ID
				}
				$category = $cats[0]->slug;
				if ( $parent = $cats[0]->parent ) {
					$category = get_category_parents( $parent, false, '/', true ) . $category;
				}
			}

			$authordata = get_userdata( $post->post_author );
			$author     = '';
			if ( is_object( $authordata ) ) {
				$author = $authordata->user_nicename;
			}
			$date           = explode( " ", date( 'Y m d H i s', $unixtime ) );
			$rewritereplace =
				array(
					$date[0],
					$date[1],
					$date[2],
					$date[3],
					$date[4],
					$date[5],
					$post->post_name,
					$post->ID,
					$category,
					$author,
					$post->post_name,
				);
			$permalink      = get_option( 'home' ) . str_replace( $rewritecode, $rewritereplace, $permalink );
			$permalink      = user_trailingslashit( $permalink, 'single' );

			if ( ! empty( $post->blog_id ) && $post->blog_id != $this->blog_id ) {
				$permalink = str_replace( '/' . $this->blog_urls[ $this->blog_id ] . '/',
					'/' . $this->blog_urls[ $post->blog_id ] . '/', $permalink );
			}

			return $permalink;
		} else { // if they're not using the fancy permalink option
			$permalink = get_option( 'home' ) . '/?p=' . $post->ID;

			return $permalink;
		}
	}

	/**
	 * Return Sphinx based Excerpts with highlitted words
	 *
	 * @param array $post_content keys of array is id numbers of search results
	 *                             can be as _title or empty
	 * @param string $isTitle it is postfix for array key, can be as 'title' for titles or FALSE for contents
	 *                             used to add tags around titles or contents
	 *
	 * @return array
	 */
	function get_excerpt( $post_content, $isTitle = false ) {
		$sphinx = ManticoreSearch::$plugin->sphinxQL;

		$is_string = false;
		if ( empty( $post_content ) ) {
			return array();
		}

		if ( $isTitle ) {
			$isTitle = "_title";
		}

		$around = $this->config->admin_options['excerpt_around'];
		$limit  = $this->config->admin_options['excerpt_limit'];

		if ( $this->config->admin_options['excerpt_dynamic_around'] == 'true' ) {
			/**
			 * If lot of results, then this value will dynamically decrease, but not less min limit
			 * If few results, this value will increase dynamically
			 */


			$values = explode( '-', $this->config->admin_options['excerpt_range'] );

			$min_around = intval( $values[0] );
			$max_around = intval( $values[1] );

			$around = $max_around - intval( $this->post_count ) + 1;
			if ( $around < $min_around ) {
				$around = $min_around;
			}

			$limitValues = explode( '-', $this->config->admin_options['excerpt_range_limit'] );
			$min_limit   = intval( $limitValues[0] );
			$max_limit   = intval( $limitValues[1] );

			$limit = $max_limit - intval( $this->post_count ) * 200;
			if ( $limit < $min_limit ) {
				$limit = $min_limit;
			}
		}


		$opts = [
			$limit . ' as limit',
			$around . ' as around',
			'\'' . $this->config->admin_options['excerpt_chunk_separator'] . '\' as chunk_separator',
			intval( $this->config->admin_options['passages-limit'] ) . ' as limit_passages',
			'\'{sphinx_after_match}\' as after_match',
			'\'{sphinx_before_match}\' as before_match'
		];

		$sphinx_after_match  = stripslashes( $this->config->admin_options[ 'after_' . ( $isTitle ? 'title' : 'text' ) . '_match' ] );
		$sphinx_before_match = stripslashes( $this->config->admin_options[ 'before_' . ( $isTitle ? 'title' : 'text' ) . '_match' ] );

		$excerpts_query = $this->clear_from_tags( $this->search_string_original );
		$excerpts_query = html_entity_decode( $excerpts_query, ENT_QUOTES );
		$excerpts_query = str_replace("'", "\'", $excerpts_query);

		//strip html tags
		//strip user defined tag

		$blogs = [];
		$keyed_results = [];

		foreach ( $post_content as $post_key => $post_value ) {
			$post_content[ $post_key ] = addslashes($this->strip_udf_tags( $post_value, false ));
			$blog_id = explode('|', $post_key);
			$blogs[$blog_id[0]][] = $post_content[ $post_key ];
			$keyed_results[$blog_id[0]][$blog_id[1]] = true;
			//$post_content_snippet[] = addslashes( $this->strip_udf_tags( $post_value, false ) );
		}

		$results = [];
		foreach ($blogs as $blog_id => $content){

			$excerpts = $sphinx->call_snippets($content, Manticore_Config_Maker::MAIN_INDEX_PREFIX . $blog_id, $excerpts_query, $opts);

			//to do something usefull with error
			// todo check om mysql
			if ( $excerpts === false ) {
				$error = $sphinx->get_query_error();
				if ( preg_match( '/connection/', $error ) and preg_match( '/failed/', $error ) ) {
					$this->is_searchd_up = false;
				}

				return [];
			} else {
				$results[$blog_id] = $excerpts;
			}
		}


		foreach ( $keyed_results as $blog => $keys ) {
			$i = 0;
			foreach ( $keys as $key => $value ) {
				$keyed_results[ $blog ][ $key ] = $results[ $blog ][ $i ]['snippet'];
				$i ++;
			}
		}


		$i                   = 0;
		foreach ( $post_content as $k => $v ) {
			$blog_id = explode('|', $k);

			if ( empty( $keyed_results[ $blog_id[0] ][ $blog_id[1] ] ) ) {
				continue;
			}
			$result =  str_replace(
				array( '{sphinx_after_match}', '{sphinx_before_match}' ),
				array( $sphinx_after_match, $sphinx_before_match ),
				esc_html( $keyed_results[ $blog_id[0] ][ $blog_id[1] ] ));


			$post_content[ $k ] = $result;
			$i ++;
		}

		return $post_content;
	}

	/**
	 * Clear content from user defined tags
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	function the_content( $content = '' ) {
		$content = $this->strip_udf_tags( $content, false );

		return $content;
	}

	/**
	 * Strip html and user defined tags
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	function strip_udf_tags( $str, $strip_tags = false ) {
		if ( $strip_tags ) {
			$str = strip_tags( $str, $this->config->admin_options['excerpt_before_match'] .
			                         $this->config->admin_options['excerpt_after_match'] );
		}
		if ( ! empty( $this->config->admin_options['strip_tags'] ) ) {
			foreach ( explode( "\n", $this->config->admin_options['strip_tags'] ) as $tag ) {
				$tag = trim( $tag );
				if ( empty( $tag ) ) {
					continue;
				}
				$str = str_replace( $tag, '', $str );
			}
		}

		return $str;
	}

	function get_search_string() {
		return $this->search_string_original;
	}

	/**
	 * Save statistic by about each search query
	 *
	 * @param string $keywords
	 *
	 * @return boolean
	 */
	function insert_sphinx_stats( $keywords_full ) {
		global $wpdb, $table_prefix;

		if ( is_paged() || ManticoreSearch::sphinx_is_redirect_required( $this->config->get_option( 'seo_url_all' ) ) ) {
			return false;
		}

		$keywords      = $this->clear_from_tags( $keywords_full );
		$keywords      = trim( $keywords );
		$keywords_full = trim( $keywords_full );

		$sql    = "select status from {$table_prefix}sph_stats
                where keywords_full = '" . esc_sql( $keywords_full ) . "'
                    limit 1";
		$status = $wpdb->get_var( $sql );
		$status = intval( $status );

		$sql = $wpdb->prepare(
			"INSERT INTO {$table_prefix}sph_stats (keywords, keywords_full, date_added, status)
            VALUES ( %s, %s, NOW(), %d )
            ", $keywords, $keywords_full, $status );

		$wpdb->query( $sql );


		ManticoreSearch::$plugin->sphinxQL
			->index( Manticore_Config_Maker::STATS_INDEX_PREFIX . get_current_blog_id() )
			->insert( [
				[
					'id'           => $wpdb->insert_id,
					'keywords'     => $keywords,
					'status'       => $status,
					'keywords_crc' => crc32( $keywords ),
					'date_added'   => time()
				]
			], [ 'keywords' ] )->execute()->get_results();


		return true;
	}

	/**
	 * Return TOP-N popual search keywords
	 *
	 * @param integer $limit
	 * @param integer $width
	 * @param string $break
	 *
	 * @return array
	 */
	function sphinx_stats_top_ten( $limit = 10, $width = 0, $break = '...' ) {
		$keywords = $this->search_string_original;

		//try to get related results on search page
		if ( is_search() && ! empty( $keywords ) ) {
			$results = $this->sphinx_stats_related( $keywords, $limit, $width, $break );
			if ( ! empty( $results ) ) {
				return $results;
			}
		}
		$results = $this->sphinx_stats_top( $limit, $width, $break );

		return $results;
	}


	function sphinx_stats_top(
		$limit = 10,
		$width = 0,
		$break = '...',
		$approved = false,
		$period_limit = 30,
		$start = 0
	) {
		global $wpdb, $table_prefix;

		$sphinx = ManticoreSearch::$plugin->sphinxQL;


		if ( $approved ) {
			$sphinx->add_filter( 'status', [ 1 ] );
		}

		if ( $period_limit ) {
			$minTime = strtotime( "-{$period_limit} days" );
			$sphinx->add_filter_range( 'date_added', $minTime, time() );
		}

		$res = $sphinx
			->select()
			->index( Manticore_Config_Maker::STATS_INDEX_PREFIX . get_current_blog_id() )
			->group( "keywords_crc", 'count', 'DESC' )
			->limits( $limit + 30, $start, $this->config->admin_options['sphinx_max_matches'] )
			->execute()
			->get_all();

		if ( empty( $res ) ) {
			return false;
		}
		foreach ( $res as $key => $item ) {
			$ids[] = $item['id'];
		}

		$this->_top_ten_total = count( $res );

		$sql     = "SELECT
                    distinct keywords_full,
                    keywords,
                    date_added
		FROM
                    {$table_prefix}sph_stats
                WHERE
                    id in (" . implode( ",", $ids ) . ")
                ORDER BY FIELD(id, " . implode( ",", $ids ) . ")
		LIMIT
                    " . ( $limit + 30 ) . "";
		$results = $wpdb->get_results( $sql );

		$results = $this->make_results_clear( $results, $limit, $width, $break );

		return $results;
	}

	function get_top_ten_total() {
		return $this->_top_ten_total;
	}

	function sphinx_stats_related( $keywords, $limit = 10, $width = 0, $break = '...', $approved = false ) {
		global $wpdb, $table_prefix;

		$sphinx = ManticoreSearch::$plugin->sphinxQL;

		$explodedKeywords = explode( ' ', $keywords );
		if ( ! empty( $explodedKeywords[1] ) ) {
			$keywords = implode( ' | ', $explodedKeywords );
		}


		$keywords = $this->clear_keywords( $keywords );
		$keywords = $this->unify_keywords( $keywords );

		if ( $approved ) {
			$sphinx->add_filter( 'status', [ 1 ] );
		}

		$res = $sphinx
			->select()
			->index( Manticore_Config_Maker::STATS_INDEX_PREFIX . get_current_blog_id() )
			->group( "keywords_crc", 'weight', 'DESC' )
			->limits( $limit + 30, 0, $this->config->admin_options['sphinx_max_matches'] )
			->match( $keywords )
			->execute()
			->get_all();

		if ( empty( $res ) ) {
			return false;
		}
		foreach ( $res as $key => $item ) {
			$ids[] = $item['id'];
		}


		$sql = "SELECT
                    keywords,
                    keywords_full
                FROM
                    {$table_prefix}sph_stats
		        WHERE
                    id in (" . implode( ",", $ids ) . ")
                    and keywords_full != '" . trim( esc_sql( $keywords ) ) . "'
                ORDER BY FIELD(id, " . implode( ",", $ids ) . ")
		        LIMIT " . ( $limit + 30 ) . "";

		$results = $wpdb->get_results( $sql );

		$results = $this->make_results_clear( $results, $limit, $width, $break );

		return $results;
	}

	function sphinx_stats_latest( $limit = 10, $width = 0, $break = '...', $approved = false ) {
		global $wpdb, $table_prefix;

		$sphinx = ManticoreSearch::$plugin->sphinxQL;

		if ( $approved ) {
			$sphinx->add_filter( 'status', [ 1 ] );
		}

		$res = $sphinx
			->select()
			->index( Manticore_Config_Maker::STATS_INDEX_PREFIX . get_current_blog_id() )
			->group( "keywords_crc", 'date_added', 'DESC' )
			->sort( 'date_added', 'DESC' )
			->limits( $limit + 30, 0, $this->config->admin_options['sphinx_max_matches'] )
			->execute()
			->get_all();

		if ( empty( $res ) ) {
			return false;
		}
		foreach ( $res as $key => $item ) {
			$ids[] = $item['id'];
		}

		$sql = "SELECT
                    distinct keywords_full,
                    keywords,
                    date_added
		FROM
                    {$table_prefix}sph_stats
                WHERE
                    id in (" . implode( ",", $ids ) . ")
                ORDER BY FIELD(id, " . implode( ",", $ids ) . ")
		LIMIT
                    " . ( $limit + 30 ) . "";

		$results = $wpdb->get_results( $sql );

		$results = $this->make_results_clear( $results, $limit, $width, $break );

		return $results;
	}

	function make_results_clear( $results, $limit, $width = 0, $break = '...' ) {
		$counter       = 0;
		$clear_results = array();

		foreach ( $results as $res ) {
			if ( $counter == $limit ) {
				break;
			}
			$keywords = $this->clear_censor_keywords( $res->keywords );
			if ( $keywords == strtolower( $res->keywords ) ) {
				$counter ++;
			} else {
				continue;
			}
			if ( $width && strlen( $res->keywords ) > $width ) {
				$res->keywords_cut = substr( $res->keywords, 0, $width ) . $break;
			} else {
				$res->keywords_cut = $res->keywords;
			}
			$clear_results[] = $res;
		}

		return $clear_results;
	}

	/**
	 * Is sphinx top ten is related
	 *
	 * @return boolean
	 */
	function sphinx_stats_top_ten_is_related() {
		return $this->top_ten_is_related;
	}

	/**
	 * Is sphinx daemon running
	 *
	 * @return boolean
	 */
	function sphinx_is_up() {
		return $this->is_searchd_up;
	}

	/**
	 * Remove non-valuable keywords from search string
	 *
	 * @param string $keywords
	 *
	 * @return string
	 */
	function clear_keywords( $keywords ) {
		$temp = strtolower( trim( $keywords ) );

		$prepositions            = array(
			'aboard',
			'about',
			'above',
			'absent',
			'across',
			'after',
			'against',
			'along',
			'alongside',
			'amid',
			'amidst',
			'among',
			'amongst',
			'into ',
			'onto',
			'around',
			'as',
			'astride',
			'at',
			'atop',
			'before',
			'behind',
			'below',
			'beneath',
			'beside',
			'besides',
			'between',
			'beyond',
			'by',
			'despite',
			'down',
			'during',
			'except',
			'following',
			'for',
			'from',
			'in',
			'inside',
			'into',
			'like',
			'mid',
			'minus',
			'near',
			'nearest',
			'notwithstanding',
			'of',
			'off',
			'on',
			'onto',
			'opposite',
			'out',
			'outside',
			'over',
			'past',
			're',
			'round',
			'since',
			'through',
			'throughout',
			'till',
			'to',
			'toward',
			'towards',
			'under',
			'underneath',
			'unlike',
			'until',
			'up',
			'upon',
			'via',
			'with',
			'within',
			'without',
			'anti',
			'betwixt',
			'circa',
			'cum',
			'per',
			'qua',
			'sans',
			'unto',
			'versus',
			'vis-a-vis',
			'concerning',
			'considering',
			'regarding'
		);
		$twoWordPrepositions     = array(
			'according to',
			'ahead of',
			'as to',
			'aside from',
			'because of',
			'close to',
			'due to',
			'far from',
			'in to',
			'inside of',
			'instead of',
			'on to',
			'out of',
			'outside of',
			'owing to',
			'near to',
			'next to',
			'prior to',
			'subsequent to'
		);
		$threeWordPrepositions   = array(
			'as far as',
			'as well as',
			'by means of',
			'in accordance with',
			'in addition to',
			'in front of',
			'in place of',
			'in spite of',
			'on account of',
			'on behalf of',
			'on top of',
			'with regard to',
			'in lieu of'
		);
		$coordinatingConjuctions = array( 'for', 'and', 'nor', 'but', 'or', 'yet', 'so', 'not' );

		$articles = array( 'a', 'an', 'the', 'is', 'as' );

		$stopWords = array_merge( $prepositions, $twoWordPrepositions );
		$stopWords = array_merge( $stopWords, $threeWordPrepositions );
		$stopWords = array_merge( $stopWords, $coordinatingConjuctions );
		$stopWords = array_merge( $stopWords, $articles );
		foreach ( $stopWords as $k => $word ) {
			$stopWords[ $k ] = '/\b' . preg_quote( $word ) . '\b/';
		}

		$temp = preg_replace( $stopWords, ' ', $temp );
		$temp = str_replace( '"', ' ', $temp );
		$temp = preg_replace( '/\s+/', ' ', $temp );
		$temp = trim( $temp );
		//if (empty($temp)) return '';

		//$temp = trim(preg_replace('/\s+/', ' ', $temp));

		return $temp;
	}

	function clear_censor_keywords( $keywords ) {
		$temp = strtolower( trim( $keywords ) );

		$censorWords = array(
			"ls magazine",
			"89",
			"www.89.com",
			"El Gordo Y La Flaca Univicion.Com",
			"ls-magazine",
			"big tits",
			"lolita",
			"google",
			"porsche",
			"none",
			"shemale",
			"buy tramadol now",
			"generic cialis",
			"cunt",
			"pussy",
			"c0ck",
			"twat",
			"clit",
			"bitch",
			"fuk",
			'sex',
			'nude',
			'porn',
			'naked',
			'teen',
			'pissing',
			'virgin',
			'fuck',
			'adult',
			'lick',
			'suck',
			'porno',
			'asian',
			'dick',
			'penis',
			'slut',
			'masturb',
			'xxx',
			'lesbian',
			'ass',
			'bitch',
			'anal',
			'gay',
			'incest',
			'masochism',
			'sadism',
			'viagra',
			'sperm',
			'breast',
			'rape',
			'beastality',
			'hardcore',
			'eroti',
			'amateur',
			'vibrator',
			'vagin',
			'clitor',
			'menstruation',
			'anus',
			'blow job',
			'srxy',
			'sexsy',
			'sexs',
			'girls',
			'blowjob',
			'cock',
			'cum',
			'fetish',
			'sexy',
			'youporn',
			'4r5e',
			'5h1t',
			'5hit',
			'a55',
			'anal',
			'ar5e',
			'arrse',
			'arse',
			'ass',
			'ass-fucker',
			'assfucker',
			'assfukka',
			'asshole',
			'asswhole',
			'b00bs',
			'ballbag',
			'balls',
			'ballsack',
			'blowjob',
			'boiolas',
			'boobs',
			'booobs',
			'boooobs',
			'booooobs',
			'booooooobs',
			'buceta',
			'bunny fucker',
			'buttmuch',
			'c0ck',
			'c0cksucker',
			'cawk',
			'chink',
			'cipa',
			'cl1t',
			'clit',
			'clit',
			'clits',
			'cnut',
			'cock',
			'cock-sucker',
			'cockface',
			'cockhead',
			'cockmunch',
			'cockmuncher',
			'cocksucker',
			'cocksuka',
			'cocksukka',
			'cok',
			'cokmuncher',
			'coksucka',
			'cox',
			'cum',
			'cunt',
			'cyalis',
			'dickhead',
			'dildo',
			'dirsa',
			'dlck',
			'dog-fucker',
			'dogging',
			'doosh',
			'duche',
			'f u c k e r',
			'fag',
			'faggitt',
			'faggot',
			'fannyfucker',
			'fanyy',
			'fcuk',
			'fcuker',
			'fcuking',
			'feck',
			'fecker',
			'fook',
			'fooker',
			'fuck',
			'fuck',
			'fucka',
			'fucker',
			'fuckhead',
			'fuckin',
			'fucking',
			'fuckingshitmother\'fucker',
			'fuckwhit',
			'fuckwit',
			'fuk',
			'fuker',
			'fukker',
			'fukkin',
			'fukwhit',
			'fukwit',
			'fux',
			'fux0r',
			'gaylord',
			'heshe',
			'hoare',
			'hoer',
			'hore',
			'jackoff',
			'jism',
			'kawk',
			'knob',
			'knobead',
			'knobed',
			'knobhead',
			'knobjocky',
			'knobjokey',
			'm0f0',
			'm0fo',
			'm45terbate',
			'ma5terb8',
			'ma5terbate',
			'master-bate',
			'masterb8',
			'masterbat*',
			'masterbat3',
			'masterbation',
			'masterbations',
			'masturbate',
			'mo-fo',
			'mof0',
			'mofo',
			'motherfucker',
			'motherfuckka',
			'mutha',
			'muthafecker',
			'muthafuckker',
			'muther',
			'mutherfucker',
			'n1gga',
			'n1gger',
			'nazi',
			'nigg3r',
			'nigg4h',
			'nigga',
			'niggah',
			'niggas',
			'niggaz',
			'nigger',
			'nob',
			'nob jokey',
			'nobhead',
			'nobjocky',
			'nobjokey',
			'numbnuts',
			'nutsack',
			'penis',
			'penisfucker',
			'phuck',
			'pigfucker',
			'pimpis',
			'piss',
			'pissflaps',
			'porn',
			'prick',
			'pron',
			'pusse',
			'pussi',
			'pussy',
			'rimjaw',
			'rimming',
			'schlong',
			'scroat',
			'scrote',
			'scrotum',
			'sh!+',
			'sh!t',
			'sh1t',
			'shag',
			'shagger',
			'shaggin',
			'shagging',
			'shemale',
			'shi+',
			'shit',
			'shit',
			'shitdick',
			'shite',
			'shited',
			'shitey',
			'shitfuck',
			'shithead',
			'shitter',
			'slut',
			'smut',
			'snatch',
			'spac',
			't1tt1e5',
			't1tties',
			'teets',
			'teez',
			'testical',
			'testicle',
			'titfuck',
			'tits',
			'titt',
			'tittie5',
			'tittiefucker',
			'titties',
			'tittyfuck',
			'tittywank',
			'titwank',
			'tw4t',
			'twat',
			'twathead',
			'twatty',
			'twunt',
			'twunter',
			'wang',
			'wank',
			'wanker',
			'wanky',
			'whoar',
			'whore',
			'willies',
			'willy'
		);

		if ( ! empty( $this->config->admin_options['censor_words'] ) ) {
			$censorWordsAdminOptions = explode( "\n", $this->config->admin_options['censor_words'] );
			foreach ( $censorWordsAdminOptions as $k => $v ) {
				$censorWordsAdminOptions[ $k ] = trim( $v );
			}
			$censorWords = array_unique( array_merge( $censorWords, $censorWordsAdminOptions ) );
		}
		foreach ( $censorWords as $k => $word ) {
			$censorWords[ $k ] = '/' . preg_quote( $word ) . '/';
		}

		$temp = preg_replace( $censorWords, ' ', $temp );
		$temp = str_replace( '"', ' ', $temp );
		$temp = preg_replace( '/\s+/', ' ', $temp );
		$temp = trim( $temp );

		return $temp;
	}

	/**
	 * Remove search tags from search keyword
	 *
	 * @param string $keywords
	 *
	 * @return string
	 */
	function clear_from_tags( $keywords ) {
		$stopWords = array(
			'@title',
			'@body',
			'@category',
			'@tags',
			'@!title',
			'@!body',
			'@!category',
			'@!tags',
			'!',
			'-',
			'~',
			'(',
			')',
			'|',
			'@'
		);
		$keywords  = trim( str_replace( $stopWords, ' ', $keywords ) );

		if ( empty( $keywords ) ) {
			return '';
		}

		$keyword = trim( preg_replace( '/\s+/', ' ', $keywords ) );

		return $keyword;
	}

	function get_type_count( $type ) {
		switch ( $type ) {
			case 'posts':
				return $this->posts_count;
			case 'pages':
				return $this->pages_count;
			case 'comments':
				return $this->comments_count;
			default:
				return 0;
		}
	}

	function unify_keywords( $keywords ) {
		//replace key-buffer to key buffer
		//replace key -buffer to key -buffer
		//replace key- buffer to key buffer
		//replace key - buffer to key buffer
		$keywords = preg_replace( "#([\w\S])\-([\w\S])#", "\$1 \$2", $keywords );
		$keywords = preg_replace( "#([\w\S])\s\-\s([\w\S])#", "\$1 \$2", $keywords );
		$keywords = preg_replace( "#([\w\S])-\s([\w\S])#", "\$1 \$2", $keywords );

		$from = array( '\\', '(', ')', '|', '!', '@', '~', '"', '&', '/', '^', '$', '=', "'" );
		$to   = array( '\\\\', '\(', '\)', '\|', '\!', '\@', '\~', '\"', '\&', '\/', '\^', '\$', '\=', '' );

		$keywords = str_replace( $from, $to, $keywords );
		$keywords = str_ireplace( array( '\@title', '\@body', '\@category', '\@tags', '\@\!tags' ),
			array( '@title', '@body', '@category', '@tags', '@!tags' ), $keywords );

		return $keywords;
	}

}
