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

class Manticore_Backend {

	private $config = '';

	private $view = null;

	/**
	 * SphinxSearch_Backend Constructor
	 *
	 * @param Manticore_Config $config
	 *
	 */
	function __construct( $config ) {
		$this->config = $config;

		$this->view = $config->get_view();

		if ( ! empty( $_GET['menu'] ) && ! empty ( $_REQUEST['action'] ) ) {
			if ( 'terms_editor' == $_GET['menu'] && $_REQUEST['action'] == 'export' ) {
				$terms_editor = new TermsEditorController( $this->config );
				$terms_editor->_export_keywords();
			}
		}
	}

	/**
	 * Draw admin page
	 *
	 */
	function print_admin_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$options = $this->config->get_admin_options();


		if ( ! empty( $_POST['start_wizard'] ) ||
		     ( empty( $options['sphinx_conf'] ) &&
		       'false' == $options['wizard_done'] ) ) {
			$wizard = new WizardController( $this->config );
			$this->view->assign( 'menu', 'wizard' );
			$wizard->start_action();
		}


		if ( ! function_exists( 'exec' ) ) {
			$this->view->assign( 'execute_disabled', true );
		}

		if ( function_exists( 'get_blog_option' ) ) {
			$root_options = get_blog_option( ManticoreSearch::get_main_blog_id(),
				Manticore_Config::ADMIN_OPTIONS_NAME );
		} else {
			$root_options = $options;
		}


		if ( in_array( get_current_blog_id(), $root_options['exclude_blogs_from_search'] ) ) {
			$this->view->assign( 'blog_search_disabled', true );
		}

		$res           = false;
		$error_message = $success_message = '';
		if ( ! empty( $_POST['start_sphinx'] ) ) {

			$res             = ManticoreSearch::$plugin->service->start();
			$success_message = 'Manticore successfully started.';
		} elseif ( ! empty( $_POST['stop_sphinx'] ) ) {

			$res             = ManticoreSearch::$plugin->service->stop();
			$success_message = 'Manticore successfully stopped.';

		} elseif ( isset( $_POST['update_indexing_options'] ) ) {

			$this->update_indexing_options();
			$success_message = 'Indexing settings updated.';

		} elseif ( isset( $_POST['update_network_settings'] ) ) {

			$this->update_network_options();
			$success_message = 'Network settings updated.';

		} elseif ( isset( $_POST['update_search_settings'] ) ) {

			$this->update_search_options();
			$success_message = 'Search settings updated.';
		} elseif ( isset( $_POST['update_blacklist'] ) ) {

			$this->update_blacklist();
			$success_message = 'Blacklist updated.';
		}

		if ( is_array( $res ) && ! empty( $res['err'] ) ) {
			$error_message = $res['err'];
		}

		$devOptions = $this->config->get_admin_options(); //update options

		if ( ! empty( $_POST['remove_secure_key'] ) ||
		     ( ! empty( $_POST['secure_key'] ) && ! empty( $_POST['add_secure_key'] ) ) ) {

			if ( empty( $_POST['remove_secure_key'] ) ) {

				$request = ManticoreSearch::$plugin->api->get( ManticoreSearch::LICENSE_SECTION,
					[ 'secure_key' => htmlentities( $_POST['secure_key'], ENT_QUOTES ), 'first' => true ] );
			} else {

				$request = ManticoreSearch::$plugin->api->get( ManticoreSearch::LICENSE_SECTION_DELETE,
					[ 'secure_key' => $devOptions['secure_key'] ] );

			}

			if ( ! empty( $request ) ) {
				if ( empty( $request['w_time'] ) ) {
					$this->view->assign( 'error_message', NEED_UPDATE_WORKER );
				}
				if ( $request['status'] == 'success' ) {
					unset( $request['status'] );
					$devOptions = array_merge( $devOptions, $request );
					$this->config->update_admin_options( $devOptions );
					if ( $this->config->admin_options[ WORKER_CACHE_ILS ] != WORKER_CACHE ) {
						$this->view->assign( 'start_reindex', 'true' );
					}
				} else {
					$this->view->assign( 'notice_message', $request['message'] );
				}
			} else {
				$api_errors = ManticoreSearch::$plugin->api->get_errors();
				if ( ! empty( $api_errors ) ) {
					$this->view->assign( 'error_message', $api_errors );
				} else {
					$this->view->assign( 'error_message', 'Service unavailable! Try again later...' );
				}
			}
		}

		if ( ! empty( $_GET['reindex'] ) ) {
			$this->view->assign( 'start_reindex', 'true' );
		}

		if ( ! empty( $devOptions['activation_error_message'] ) ) {
			$this->view->assign( 'notice_message', $devOptions['activation_error_message'] );
		}

		if ( ! empty( $error_message ) ) {
			$this->view->assign( 'error_message', $error_message );
		}
		if ( ! empty( $success_message ) ) {
			$this->view->assign( 'success_message', $success_message );
		}


		$this->view->assign( 'devOptions', $devOptions );

		$this->view->assign( 'highlightingTypes', [
			'mark'             => '&ltmark&gt;',
			'em'               => '&ltem&gt;',
			'u'                => '&ltu&gt;',
			'strong'           => '&ltstrong&gt;',
			'text_color'       => 'Text color',
			'background_color' => 'Background color',
			'style'            => 'Css Style',
			'class'            => 'Css Class',
			'custom'           => 'Custom HTML'
		] );

		//load admin panel template
		$this->view->assign( 'header', 'Manticore Search for Wordpress' );

		$this->view->assign( 'is_sphinx_path_secure', $this->_isSphinxPathSecure() );
		$protocol   = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? "https://" : "http://";
		$domainName = $_SERVER['HTTP_HOST'] . '/';
		$this->view->assign( 'host', $protocol . $domainName );

		if ( ! empty( $_GET['menu'] ) ) {
			switch ( $_GET['menu'] ) {
				case 'network_settings':

					$this->view->assign( 'is_network', ManticoreSearch::is_network() );
					$this->view->assign( 'is_main_blog', ManticoreSearch::is_main_blog() );
					$this->view->assign( 'blog_list', ManticoreSearch::get_network_sites() );
					$this->view->assign( 'menu', 'network_settings' );
					break;
				case 'terms_editor':
					$terms_editor = new TermsEditorController( $this->config );
					$terms_editor->index_action();
					$this->view->assign( 'menu', 'terms_editor' );
					break;
				case 'stats':
					$stats = new StatsController( $this->config );
					$stats->index_action();
					$this->view->assign( 'menu', 'stats' );
					break;
				case 'search_settings':

					$this->view->assign( 'current_blog', get_current_blog_id() );
					$this->view->assign( 'blog_list', ManticoreSearch::get_network_sites( false ) );
					$this->view->assign( 'menu', 'search_settings' );
					break;
				case 'indexing_settings':
					$this->view->assign( 'menu', 'indexing_settings' );
					$this->view->assign( 'all_taxonomies', get_taxonomies() );
					$this->view->assign( 'all_custom_fields', $this->get_all_custom_fields() );
					$this->view->assign( 'mime_types', $this->get_mime_types_fields() );
					$this->view->assign( 'system_taxonomies', ManticoreSearch::$system_taxonomies );
					$this->view->assign( 'post_types', get_post_types() );
					break;
				case 'wizard';
					if ( empty( $_POST['action'] ) ) {
						if ( empty( $wizard ) ) {
							$wizard = new WizardController( $this->config );
						}
						$this->view->assign( 'menu', 'wizard' );
						$wizard->start_action();
					}
					break;
				case 'help':
					$this->view->assign( 'menu', 'help' );
					break;
			}
		} else {
			$this->view->assign( 'indexing_data', $this->get_indexing_count() );
		}

		$this->view->assign( 'is_manticore_active', ManticoreSearch::$plugin->service->is_sphinx_running() );
		$this->view->assign( 'is_widget_active', is_active_widget( false, null, 'searchsidebarwidget', true ) );
		$this->view->render( 'admin/layout.phtml' );
	}

	public function get_indexing_count() {
		$all_count     = 0;
		$indexed_count = 0;
		foreach (
			[
				Manticore_Indexing::TYPE_POST,
				Manticore_Indexing::TYPE_COMMENTS,
				Manticore_Indexing::TYPE_ATTACHMENTS,
				Manticore_Indexing::TYPE_STATS
			] as $indexing_type
		) {
			$indexed = ManticoreSearch::$plugin->indexer->get_content_count( $indexing_type );
			if ( ! empty( $indexed ) ) {
				$all_count += $indexed;
			}
		}

		if ( ! ManticoreSearch::$plugin->service->is_sphinx_running() ) {
			return [ 'indexed' => 'N/A', 'all_count' => $all_count ];
		}

		$current_blog = get_current_blog_id();
		$indexed      = ManticoreSearch::$plugin->sphinxQL
			->select()
			->index( Manticore_Config_Maker::MAIN_INDEX_PREFIX . $current_blog )
			->append_select( 'count(*)' )
			->execute()
			->get_column( 'count(*)' );

		if ( ! empty( $indexed ) ) {
			$indexed_count += $indexed;
		}

		$indexed = ManticoreSearch::$plugin->sphinxQL->select()
		                                             ->index( Manticore_Config_Maker::STATS_INDEX_PREFIX . $current_blog )
		                                             ->append_select( 'count(*)' )
		                                             ->execute()
		                                             ->get_column( 'count(*)' );

		if ( ! empty( $indexed ) ) {
			$indexed_count += $indexed;
		}

		return [ 'indexed' => intval( $indexed_count ), 'all_count' => $all_count ];
	}

	private function update_blacklist() {
		$devOptions = $this->config->admin_options;
		if ( isset( $_POST['censor_words'] ) ) {
			$devOptions['censor_words'] = trim( $_POST['censor_words'] );
		}
		$this->config->update_admin_options( $devOptions );
	}


	public function update_indexing_options() {
		$devOptions = $this->config->admin_options;

		if ( ! empty( $_POST['taxonomy_indexing'] ) ) {

			$devOptions['taxonomy_indexing'] = 'true';

			if ( ! empty( $_POST['taxonomy_for_indexing'] ) ) {

				foreach ( $_POST['taxonomy_for_indexing'] as $k => $v ) {

					$_POST['taxonomy_for_indexing'][ $k ] = htmlentities( $v, ENT_QUOTES );
				}
				$devOptions['taxonomy_indexing_fields'] = $_POST['taxonomy_for_indexing'];
			}
		} else {
			$devOptions['taxonomy_indexing']        = 'false';
			$devOptions['taxonomy_indexing_fields'] = [];
		}


		if ( ! empty( $_POST['custom_fields_indexing'] ) && $_POST['custom_fields_indexing'] == 'true' ) {
			$devOptions['custom_fields_indexing'] = 'true';

			if ( ! empty( $_POST['custom_fields_for_indexing'] ) ) {
				foreach ( $_POST['custom_fields_for_indexing'] as $k => $v ) {
					$_POST['custom_fields_for_indexing'][ $k ] = htmlentities( $v, ENT_QUOTES );
				}
				$devOptions['custom_fields_for_indexing'] = $_POST['custom_fields_for_indexing'];
			}
		} else {
			$devOptions['custom_fields_indexing']     = 'false';
			$devOptions['custom_fields_for_indexing'] = [];
		}


		if ( ! empty( $_POST['attachments_indexing'] ) && $_POST['attachments_indexing'] == 'true' ) {
			$devOptions['attachments_indexing'] = 'true';

			$skip_list = [];
			foreach ( $this->get_mime_types_fields() as $mime_type ) {
				if ( ! in_array( $mime_type['post_mime_type'], $_POST['attachments_type_for_skip_indexing'] ) ) {
					$skip_list[] = $mime_type['post_mime_type'];
				}
			}
			$devOptions['attachments_type_for_skip_indexing'] = $skip_list;

		} else {
			$devOptions['attachments_indexing']               = 'false';
			$devOptions['attachments_type_for_skip_indexing'] = [];
		}


		if ( ! empty( $_POST['post_types_for_indexing'] ) ) {
			foreach ( $_POST['post_types_for_indexing'] as $k => $v ) {
				$_POST['post_types_for_indexing'][ $k ] = htmlentities( $v, ENT_QUOTES );
			}
			$devOptions['post_types_for_indexing'] = $_POST['post_types_for_indexing'];
		} else {
			$devOptions['post_types_for_indexing'] = [];
		}

		$this->config->update_admin_options( $devOptions );
		ManticoreSearch::clear_autocomplete_config();

		if ( ! empty( $_POST['need_reindex'] ) && $_POST['need_reindex'] == 'true' ) {
			die( '<script>document.location.href = "?page=manticoresearch.php&reindex=1";</script>' );
		}
	}

	/**
	 * Update search Options
	 *
	 */
	function update_search_options() {
		//get options array
		$devOptions = $this->config->admin_options;
		/**
		 * autocomplete_enable  - enable autocomplete search for sidebar widget
		 * seo_url_all          - enable friendly url
		 *
		 * search_comments - search in comments
		 * search_posts    - search in posts
		 * search_pages    - search in pages
		 * search_tags     - search in tags
		 */
		foreach (
			[
				'autocomplete_enable',
				'seo_url_all',
				'search_comments',
				'search_posts',
				'search_pages',
				'search_tags',
				'excerpt_dynamic_around'
			] as $option
		) {
			if ( ! empty( $_POST[ $option ] ) ) {
				$devOptions[ $option ] = 'true';
			} else {
				$devOptions[ $option ] = 'false';
			}
		}

		if ( ! empty( $_POST['autocomplete_cache_clear'] ) &&
		     in_array( $_POST['autocomplete_cache_clear'], [ 'update', 'day', 'week' ] ) ) {
			$devOptions['autocomplete_cache_clear'] = $_POST['autocomplete_cache_clear'];

		}


		/**
		 * 'Let user choose'       => 'user_defined',
		 * 'Freshness & Relevance' => 'date_relevance',
		 * 'Relevance'             => 'relevance',
		 * 'Freshness'             => 'date',
		 */

		if ( ! empty( $_POST['search_sorting'] )
		     && in_array( $_POST['search_sorting'], [ 'user_defined', 'date_relevance', 'relevance', 'date' ] ) ) {
			$devOptions['search_sorting'] = $_POST['search_sorting'];

		}


		/**
		 * excerpt_chunk_separator - separator of content around the search keyword
		 */
		foreach (
			[
				'excerpt_chunk_separator',
				'before_comment',
				'before_page',
				'before_post',
				'strip_tags',
			] as $option
		) {
			if ( isset( $_POST[ $option ] ) ) {
				$devOptions[ $option ] = $_POST[ $option ];
			}
		}

		/**
		 * excerpt_limit - limit number of characters in excerpt
		 * excerpt_around - limit number of words in excerpt around the search keyword
		 * sphinx_port - sphinx search connection port
		 */
		foreach (
			[
				'passages-limit',
				'excerpt_limit',
				'excerpt_range',
				'excerpt_range_limit',
				'excerpt_around'
			] as $option
		) {

			if ( isset( $_POST[ $option ] ) ) {
				$devOptions[ $option ] = $_POST[ $option ];
			}
		}


		foreach ( $devOptions['weight_fields'] as $option => $value ) {

			if ( isset( $_POST[ 'weight_fields_' . $option ] ) ) {
				$devOptions['weight_fields'][ $option ] = intval( $_POST[ 'weight_fields_' . $option ] );
			}
		}


		foreach (
			[
				'before_text_match',
				'after_text_match',
				'before_title_match',
				'after_title_match'
			] as $option
		) {

			if ( isset( $_POST[ $option ] ) ) {
				$devOptions[ $option . '_clear' ] = $_POST[ $option ];
			}
		}


		foreach ( [ 'highlighting_title_type', 'highlighting_text_type' ] as $option ) {

			if ( isset( $_POST[ $option ] ) ) {
				$devOptions[ $option ] = $_POST[ $option ];
			}
		}

		$highlighting = $this->getHighlighting();


		if ( ! empty( $highlighting['text']['before'] ) ) {
			$devOptions['before_text_match'] = $highlighting['text']['before'];
		}

		if ( ! empty( $highlighting['text']['after'] ) ) {
			$devOptions['after_text_match'] = $highlighting['text']['after'];
		}

		if ( ! empty( $highlighting['title']['before'] ) ) {
			$devOptions['before_title_match'] = $highlighting['title']['before'];
		}

		if ( ! empty( $highlighting['title']['after'] ) ) {
			$devOptions['after_title_match'] = $highlighting['title']['after'];
		}

		unset( $highlighting );


		$need_rebuild_config = false;

		$before_saving = $devOptions['search_in_blogs'];

		$devOptions['search_in_blogs'] = [];
		if ( ! empty( $_POST['search_in_blogs'] ) ) {

			$all_blogs = ManticoreSearch::get_network_sites( false );

			foreach ( $_POST['search_in_blogs'] as $value ) {
				$devOptions['search_in_blogs'][] = array_search( $value, $all_blogs );
			}

		}

		$this->config->update_admin_options( $devOptions );

		if ( $before_saving !== $devOptions['search_in_blogs'] ) {
			$need_rebuild_config = true;
		}

		if ( $need_rebuild_config ) {
			$wizard_controller = new WizardController( $this->config );

			$config_content = $wizard_controller->generate_config_file_content();
			if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
				$wizard_controller->save_config( $this->config->get_option( 'sphinx_conf' ), $config_content );
				ManticoreSearch::$plugin->sphinxQL
					->query( 'RELOAD INDEXES' )
					->execute();
			} else {
				$result = ManticoreSearch::$plugin->sphinxQL->updateConfig( $config_content );
			}
		}
	}


	/**
	 * Update Options
	 *
	 */
	function update_network_options() {
		//get options array

		$need_rebuild_config = false;
		$devOptions          = $this->config->admin_options;


		$before_saving = $devOptions['exclude_blogs_from_search'];

		$devOptions['exclude_blogs_from_search'] = [];
		if ( empty( $_POST['exclude_blogs_from_search'] ) ) {
			$_POST['exclude_blogs_from_search'] = [];
		}
		$all_blogs = ManticoreSearch::get_network_sites();

		foreach ( $all_blogs as $blog_id => $blog_url ) {
			if ( ! in_array( $blog_url, $_POST['exclude_blogs_from_search'] ) ) {
				$devOptions['exclude_blogs_from_search'][] = $blog_id;
			}
		}


		$this->config->update_admin_options( $devOptions );

		if ( $before_saving !== $devOptions['search_in_blogs'] ) {
			$need_rebuild_config = true;
		}

		if ( $need_rebuild_config ) {
			$wizard_controller = new WizardController( $this->config );
			$config_content    = $wizard_controller->generate_config_file_content();
			$wizard_controller->save_config( $this->config->get_option( 'sphinx_conf' ), $config_content );
			ManticoreSearch::$plugin->sphinxQL
				->query( 'RELOAD INDEXES' )
				->execute();
		}
	}


	/**
	 * Checks weither sphinx_path is closed from outside
	 *
	 * @param &array list of unsecured files
	 *
	 * @return bool
	 */
	private function _isSphinxPathSecure() {
		$devOptions = $this->config->get_admin_options();
		$docRoot    = realpath( $_SERVER['DOCUMENT_ROOT'] );

		$tests[] = $devOptions['sphinx_path'] . '/var/log/query.log';
		$tests[] = $devOptions['sphinx_conf'];

		$insecureFiles = array();

		foreach ( $tests as $i => $test ) {
			if ( strpos( $test, $docRoot ) !== 0 ) {
				continue;
			}

			$path = str_replace( $docRoot, '', $test );
			$url  = "http://{$_SERVER['HTTP_HOST']}$path";
			$f    = @fopen( $url );

			if ( $f ) {
				fclose( $f );
				$insecureFiles[] = $url;
			}
		}

		$this->view->assign( 'insecure_files', $insecureFiles );

		return count( $insecureFiles ) == 0;
	}


	function getHighlighting() {
		$highlighting = [];
		foreach ( [ 'title', 'text' ] as $type ) {
			$highlighting[ $type ] = $this->prepareHighlightingByType( $type );
		}

		return $highlighting;
	}

	function prepareHighlightingByType( $type = 'text' ) {
		if ( ! empty( $_REQUEST[ 'highlighting_' . $type . '_type' ] ) ) {
			switch ( $_REQUEST[ 'highlighting_' . $type . '_type' ] ) {
				case 'mark':
					return [ 'before' => '<mark>', 'after' => '</mark>' ];
					break;
				case 'em':
					return [ 'before' => '<em>', 'after' => '</em>' ];
					break;
				case 'u':
					return [ 'before' => '<u>', 'after' => '</u>' ];
					break;
				case 'strong':
					return [ 'before' => '<strong>', 'after' => '</strong>' ];
					break;

				case 'text_color':
					return [
						'before' => '<span style="color: ' . $_REQUEST[ 'before_' . $type . '_match' ] . ';">',
						'after'  => '</span>'
					];
					break;
				case 'background_color':
					return [
						'before' => '<span style="background: ' . $_REQUEST[ 'before_' . $type . '_match' ] . ';">',
						'after'  => '</span>'
					];
					break;
				case 'style':
					return [
						'before' => '<span style="' . $_REQUEST[ 'before_' . $type . '_match' ] . '">',
						'after'  => '</span>'
					];
					break;
				case 'class':
					return [
						'before' => '<span class="' . $_REQUEST[ 'before_' . $type . '_match' ] . '">',
						'after'  => '</span>'
					];
					break;
				case 'custom':
					return [
						'before' => $_REQUEST[ 'before_' . $type . '_match' ],
						'after'  => $_REQUEST[ 'after_' . $type . '_match' ]
					];
					break;
			}
		}

		return [ 'before' => '', 'after' => '' ];
	}


	private function get_all_custom_fields() {
		global $wpdb, $table_prefix;

		return $wpdb->get_results( 'SELECT DISTINCT(`meta_key`) FROM `' . $table_prefix . 'postmeta` ' .
		                           'ORDER BY meta_key REGEXP \'^[a-z]\' DESC, meta_key', ARRAY_A );
	}

	private function get_mime_types_fields() {
		global $wpdb, $table_prefix;

		return $wpdb->get_results( 'SELECT DISTINCT(`post_mime_type`) FROM `' . $table_prefix . 'posts` ' .
		                           'WHERE `post_type` LIKE \'attachment\'', ARRAY_A );
	}
}
