<?php
/*
Plugin Name: Manticore Search Plugin
Plugin URI: https://manticoresearch.com/
Description: This plugin brings all the best practices of search functionality to your site: keyword highlighting, autocomplete, search statistics and control. It also makes your search blazingly fast.<strong>During first activation plugin will upload manticore binary, cause activation can take 30-60 seconds</strong>
Version: 3.10.5
Author: Manticore Software
Author URI: contact@manticoresearch.com
License: A GPL2

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
/**
 * Define path to Plugin Directory
 *
 */

define( 'SPHINXSEARCH_PLUGIN_DIR', dirname( __FILE__ ) );

/**
 * Define path to Sphinx Install Directory
 * Sphinx will install in Wordpress default upload directory
 *
 */

define( 'SPHINXSEARCH_SPHINX_INSTALL_DIR',
	WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'manticore' );

/**
 * Use latest sphinx API from Sphinx distributive directory
 * otherwise use it from plugin directory which come with plugin
 */


include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_container.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_config.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_frontend.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_backend.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_indexing.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_install.php' );

include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'wizard-controller.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'stats-controller.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'terms-editor-controller.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_service.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_view.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'manticore_api.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'manticore_config_maker.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'manticore_ql_config_maker.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'manticore_http_config_maker.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'connectors' . DIRECTORY_SEPARATOR . 'manticore_connector.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'connectors' . DIRECTORY_SEPARATOR . 'manticore_http_api.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'connectors' . DIRECTORY_SEPARATOR . 'sphinxQL.php' );


include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'autocomplete' .
              DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'ManticoreAutocompleteCache.php' );

include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR . 'latest-searches.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR . 'top-searches.php' );
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR . 'search-sidebar.php' );
/**
 * load tags - each tag you can use in your theme template
 * see README
 */
include_once( SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'tags' . DIRECTORY_SEPARATOR . 'sphinxsearch_tags.php' );


class ManticoreSearch {

	/**
	 * Max number of sites in network
	 *
	 * @var int
	 */
	const SHARDS_COUNT = 500;

	const INDEXING_ATTACHMENS_SECTION = '';
	const LICENSE_SECTION = '/api/license/check';
	const LICENSE_SECTION_DELETE = '/api/license/delete';

	/**
	 * List of system taxonomy what's don't indexing
	 *
	 * @var array
	 */
	public static $system_taxonomies = [ 'category', 'post_tag', 'nav_menu', 'link_category', 'post_format' ];

	/**
	 * Manticore Container
	 *
	 * @var StdClass
	 */
	public static $plugin;

	/**
	 * ManticoreSearch constructor.
	 */
	public function __construct() {

		self::$plugin = new Manticore_Container();

		//prepare post results
		add_filter( 'posts_request', array( &$this, 'posts_request' ) );
		add_filter( 'posts_results', array( &$this, 'posts_results' ) );

		//return number of found posts
		add_filter( 'found_posts', array( &$this, 'found_posts' ) );

		//content filters
		add_filter( 'wp_title', array( &$this, 'wp_title' ) );

		//bind neccessary actions
		add_filter( 'post_link', array( &$this, 'post_link' ) );
		add_filter( 'the_permalink', array( &$this, 'the_permalink' ) );
		add_filter( 'the_title', array( &$this, 'the_title' ) );
		add_filter( 'the_content', array( &$this, 'the_content' ) );
		add_filter( 'the_author', array( &$this, 'the_author' ) );
		add_filter( 'the_time', array( &$this, 'the_time' ) );
		add_filter( 'get_search_query', array( &$this, 'get_search_query' ) );
		add_action( 'wp_print_styles', array( &$this, 'add_my_stylesheet' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_my_enqueue' ) );


		// add_action('loop_start',  array(&$this, 'add_actions_filters'));
		add_action( 'loop_end', array( &$this, 'remove_actions_filters' ) );

		//action to prepare admin menu
		add_action( 'admin_menu', array( &$this, 'options_page' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		//frontend actions
		//add_action( 'comment_post', array( &$this, 'comment_post' ) );
		add_action( 'delete_post', array( &$this, 'delete_post' ) );
		add_action( 'transition_post_status', array( self::$plugin->indexer, 'on_all_status_transitions' ), 10, 3 );
		add_action( 'transition_comment_status', array( self::$plugin->indexer, 'on_all_status_transitions' ), 10, 3 );


		add_action( 'add_attachment', array( self::$plugin->indexer, 'on_add_attachment' ) );
		add_action( 'edit_attachment', array( self::$plugin->indexer, 'on_add_attachment' ) );
		add_action( 'delete_attachment', array( self::$plugin->indexer, 'on_delete_attachment' ) );

		add_action( 'wp_insert_comment', array( self::$plugin->indexer, 'on_comment_inserted' ), 99, 2 );
		//widgets
		add_action( 'widgets_init', array( &$this, 'load_widgets' ) );

		//seo urls
		add_action( 'template_redirect', array( &$this, 'sphinx_search_friendly_redirect' ) );

		add_action( 'wp_ajax_get_snippet', array( &$this, 'get_snippet' ) );
		add_action( 'wp_ajax_get_autocomplete', array( &$this, 'get_autocomplete' ) );
		add_action( 'wp_ajax_get_indexing_result', array( &$this, 'get_indexing_result' ) );
		add_action( 'wp_ajax_start_indexing', array( &$this, 'start_indexing' ) );
		add_action( 'wp_ajax_start_daemon', array( &$this, 'start_daemon' ) );
		add_action( 'wp_ajax_nopriv_get_autocomplete', array( &$this, 'get_autocomplete' ) );


		add_filter( 'the_content', array( &$this, 'filter_add_anchors' ) );

		$path = plugin_basename( __FILE__ );
		add_action( "after_plugin_row_{$path}", array( &$this, 'check_by_updates' ), 10, 3 );

		add_action( 'wpmu_new_blog', array( &$this, 'on_add_blog' ), 10, 6 );
		add_action( 'deleted_blog', array( &$this, 'on_delete_blog' ), 10, 2 );
	}

	public function on_add_blog( $blog_id, $user_id, $domain, $path, $network_id, $meta ) {
		$this->rebuild_config();
	}

	public function on_delete_blog( $blog_id, $drop ) {
		self::$plugin->sphinxQL->query( 'TRUNCATE RTINDEX ' . Manticore_Config_Maker::MAIN_INDEX_PREFIX . $blog_id )->execute();
		$this->rebuild_config();
	}

	private function rebuild_config() {
		$wizard_controller = new WizardController( self::$plugin->config );

		$config_content = $wizard_controller->generate_config_file_content();
		$wizard_controller->save_config( self::$plugin->config->get_option( 'sphinx_conf' ), $config_content );
		self::$plugin->service->stop();
		$started = self::$plugin->service->start();

		return true;
	}

	function check_by_updates( $plugin_file, $plugin_data, $status ) {
		if ( self::$plugin->config->admin_options['need_update'] == 'true' ) {
			echo '<tr class="plugin-update-tr active">' .
			     '<td class="plugin-update colspanchange" colspan="3">' .
			     '<div class="update-message notice inline notice-warning notice-alt">' .
			     '<p>New version of plugin available. Check <a href="https://manticoresearch.com">manticoresearch.com</a> for download</p>' .
			     '</div>' .
			     '</td>' .
			     '</tr>';
		}
	}


	public function filter_add_anchors( $content ) {

		preg_match_all( '#<h[1-6]+.{0,25}>(.{1,20})</h[1-6]+>#usi', $content, $matches );

		if ( ! empty( $matches[0][0] ) ) {
			$replace = [];
			foreach ( $matches[0] as $k => $match ) {
				$replace[ $match ] = '<a name="s_' . $k . '"></a>' . $match;
			}
			$content = str_replace( array_keys( $replace ), array_values( $replace ), $content );
		}

		return $content;
	}

	public function get_snippet() {
		include_once 'php/manticore_live_snippet.php';
		wp_die();
	}

	public function get_autocomplete() {
		global $table_prefix;
		include_once 'autocomplete' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'ManticoreAutocompleteTest.php';

		$config = new \StdClass;


		if ( self::$plugin->config->admin_options['manticore_use_http'] == 'true' ) {
			$config->use_remote = 1;
			$config->api_host = ManticoreSearch::$plugin->config->admin_options['api_host'];
			$config->secure_token = ManticoreSearch::$plugin->config->admin_options['secure_key'];


		} else {
			if ( self::$plugin->config->admin_options['sphinx_use_socket'] == 'true' ) {
				$config->searchd_socket = self::$plugin->config->admin_options['sphinx_socket'];
			} else {
				$config->searchd_host = self::$plugin->config->admin_options['sphinx_port'];
				$config->searchd_port = self::$plugin->config->admin_options['sphinx_host'];
			}
		}




		$tags_names = get_tags();
		$tags       = [];
		if ( ! empty( $tags_names ) ) {
			foreach ( $tags_names as $tag ) {
				$tags[] = $tag->slug;
			}
		}

		if ( empty( ManticoreSearch::$plugin->config->admin_options['search_in_blogs'] ) ) {
			ManticoreSearch::$plugin->config->admin_options['search_in_blogs'] = [ get_current_blog_id() ];
		}



		$config->main_index                  = Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX . get_current_blog_id();
		$config->autocomplete_index          = Manticore_Config_Maker::AUTOCOMPLETE_DISTRIBUTED_INDEX_PREFIX . get_current_blog_id();
		$config->search_in_blogs             = implode( ' |*| ',
			ManticoreSearch::$plugin->config->admin_options['search_in_blogs'] );
		$config->blog_id                     = get_current_blog_id();
		$config->suggest_on                  = "edge";
		$config->tags_names                  = implode( ' |*| ', $tags );
		$config->taxonomy_names              = implode( ' |*| ',
			self::$plugin->config->admin_options['taxonomy_indexing_fields'] );
		$config->custom_fields_names         = implode( ' |*| ',
			self::$plugin->config->admin_options['custom_fields_for_indexing'] );
		$config->corrected_dic_size          = 10000000;
		$config->corrected_str_mlen          = 2;
		$config->corrected_levenshtein_limit = 20;
		$config->corrected_levenshtein_min   = 5;
		$config->mysql_host                  = DB_HOST;
		$config->mysql_posts_table           = $table_prefix . 'posts';
		$config->mysql_user                  = DB_USER;
		$config->mysql_pass                  = DB_PASSWORD;
		$config->mysql_db                    = DB_NAME;

		$autocomplete_engine = new ManticoreAutocomplete( $config );
		wp_die( $autocomplete_engine->request( $_REQUEST ) );
	}

	public function get_indexing_result() {
		wp_send_json( [ 'results' => self::$plugin->indexer->get_results() ] );
	}

	public function start_indexing() {
		wp_send_json( self::$plugin->indexer->reindex() );
	}

	public function start_daemon() {
		wp_send_json( [ 'results' => ManticoreSearch::$plugin->service->start() ] );
	}

	function add_actions_filters() {

	}

	public function remove_actions_filters() {
		remove_filter( 'posts_request', array( &$this, 'posts_request' ) );
		remove_filter( 'posts_results', array( &$this, 'posts_results' ) );
		remove_filter( 'found_posts', array( &$this, 'found_posts' ) );
		remove_filter( 'post_link', array( &$this, 'post_link' ) );
		remove_filter( 'the_permalink', array( &$this, 'the_permalink' ) );
		remove_filter( 'the_title', array( &$this, 'the_title' ) );
		remove_filter( 'the_content', array( &$this, 'the_content' ) );
		remove_filter( 'the_author', array( &$this, 'the_author' ) );
		remove_filter( 'the_time', array( &$this, 'the_time' ) );

	}

	public function add_my_stylesheet() {


		if ( is_active_widget( false, null, 'searchsidebarwidget', true ) ) {
			wp_enqueue_script( 'plugin-typeahead', plugin_dir_url( __FILE__ ) .
			                                       'templates' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' .
			                                       DIRECTORY_SEPARATOR . 'jquery.typeahead.js', [ 'jquery' ] );

			wp_enqueue_style( 'sphinxTypeahead', plugins_url( 'templates/assets/css/jquery.typeahead.css', __FILE__ ) );
			wp_enqueue_style( 'sphinxAutocomplete', plugins_url( 'templates/assets/css/autocomplete.css', __FILE__ ) );
		}


		wp_enqueue_style( 'sphinxStyleSheets', plugins_url( 'templates/assets/css/manticore.css', __FILE__ ) );
	}

	public function add_my_enqueue() {
		wp_enqueue_style( 'my_custom_script',
			plugin_dir_url( __FILE__ ) . 'templates' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'manticore-admin.css' );
		wp_enqueue_script( 'my_custom_script',
			plugin_dir_url( __FILE__ ) . 'templates' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'manticore-plugin.js',
			[
				'jquery',
				'jquery-ui-tooltip',
				'jquery-ui-progressbar'
			] );
	}

	/**
	 * Replace post time to commen time
	 *
	 * @param string $the_time - post time
	 * @param string $d - time format
	 *
	 * @return string
	 */
	public function the_time( $the_time, $d = '' ) {
		if ( ! $this->_sphinxRunning() ) {
			return $the_time;
		}

		return self::$plugin->frontend->the_time( $the_time, $d );
	}

	/**
	 * Replace post author name to comment author name
	 *
	 * @param string $display_name - post author name
	 *
	 * @return string
	 */
	public function the_author( $display_name ) {
		if ( ! $this->_sphinxRunning() ) {
			return $display_name;
		}

		return self::$plugin->frontend->the_author( $display_name );
	}

	/**
	 * Correct link in search results
	 *
	 * @param string $permalink
	 * @param object $post usually null so we use global post object
	 *
	 * @return string
	 */
	public function post_link( $permalink, $post = null ) {
		if ( ! $this->_sphinxRunning() ) {
			return $permalink;
		}

		return self::$plugin->frontend->post_link( $permalink, $post );
	}

	/**
	 * Clear content from user defined tags
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function the_content( $content = '' ) {
		if ( ! $this->_sphinxRunning() ) {
			return $content;
		}
		$content = self::$plugin->frontend->the_content( $content );

		return $content;
	}

	/**
	 * Query Sphinx for search result and parse results return empty query for WP
	 *
	 * @param string $sqlQuery - default sql query to fetch posts
	 *
	 * @return string $query
	 */
	public function posts_request( $sqlQuery ) {

		if ( ! $this->_sphinxRunning() ) {
			return $sqlQuery;
		}

		//Qeuery Sphinx for Search results
		if ( self::$plugin->frontend->query( stripslashes( get_search_query() ) ) ) {
			self::$plugin->frontend->parse_results();
		}
		//returning empty string we disabled to run default query
		//instead of that we add our owen search results
		return '';
	}

	public function get_search_query( $query ) {
		return urldecode( $query );
	}


	/**
	 * Generate new posts based on search results
	 *
	 * @param object $posts
	 *
	 * @return array|object $posts
	 */
	public function posts_results( $posts ) {
		if ( ! $this->_sphinxRunning() ) {
			return $posts;
		}

		return self::$plugin->frontend->posts_results();
	}

	/**
	 * Return total number of found posts
	 *
	 * @param int $found_posts
	 *
	 * @return int
	 */
	public function found_posts( $found_posts = 0 ) {
		if ( ! $this->_sphinxRunning() ) {
			return $found_posts;
		}

		return self::$plugin->frontend->post_count;
	}

	/**
	 * Query frontend for new permalink
	 *
	 * @param string $permalink
	 *
	 * @return string
	 */
	public function the_permalink( $permalink = '' ) {
		if ( ! $this->_sphinxRunning() ) {
			return $permalink;
		}

		return self::$plugin->frontend->the_permalink( $permalink );
	}

	/**
	 * Change blog title to: <keyword> - wp_title()
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function wp_title( $title = '' ) {
		if ( ! $this->_sphinxRunning() ) {
			return $title;
		}

		return self::$plugin->frontend->wp_title( $title );
	}

	public function the_title( $title = '' ) {
		if ( ! $this->_sphinxRunning() ) {
			return $title;
		}

		return self::$plugin->frontend->the_title( $title );
	}

	/**
	 * Show Admin Options
	 *
	 */
	public function print_admin_page() {

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script( 'jquery-ui-slider', false, array( 'jquery' ) );
		wp_enqueue_style( 'jquery-ui-slider-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );


		self::$plugin->backend->print_admin_page();
	}

	/**
	 * Bind printAdminPage to Show Admin Options
	 *
	 */
	public function options_page() {
		if ( function_exists( 'add_options_page' ) ) {
			add_options_page( 'Manticore Search', 'Manticore Search', 'manage_options', basename( __FILE__ ),
				array( &$this, 'print_admin_page' ) );
		}
	}

	public function delete_post( $pid ) {
		$indexResult = self::$plugin->sphinxQL
			->index( Manticore_Config_Maker::MAIN_INDEX_PREFIX . get_current_blog_id() )
			->delete( $pid * 2 + 1 )
			->execute()
			->get_results();

		return $indexResult;
	}

	public function admin_init() {
		//ajax wizard actions
		if ( ! empty( $_POST['action'] ) ) {
			$wizard = new WizardController( self::$plugin->config );
			add_action( 'wp_ajax_' . $_POST['action'],
				array( &$wizard, $_POST['action'] . '_action' ) );
		}
	}

	public function load_widgets() {
		global $wp_version;
		//widgets supported only at version 2.8 or higher
		if ( version_compare( $wp_version, '2.8', '>=' ) ) {
			register_widget( 'LatestSearchesWidget' );
			register_widget( 'TopSearchesWidget' );
			register_widget( 'SearchSidebarWidget' );
		}
	}

	public function get_search_string() {
		return self::$plugin->frontend->get_search_string();
	}

	/**
	 * @access private
	 * @return boolean
	 */
	public function _sphinxRunning() {
		if ( ! is_search() || self::$plugin->config->admin_options[ WORKER_CACHE_ILS ] == WORKER_CACHE
		     || ! ManticoreSearch::$plugin->sphinxQL->is_active() ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks weither redirect for friendly URLs is required
	 *
	 * @static
	 *
	 * @param string $seo_url_all
	 *
	 * @return bool
	 */
	static function sphinx_is_redirect_required( $seo_url_all ) {
		if ( ! is_search()
		     || strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) !== false
		     || strpos( $_SERVER['REQUEST_URI'], '/search/' ) !== false
		) {
			return false;
		}

		return $seo_url_all == 'true';
	}

	/**
	 * Templates redirect as action of WP
	 */
	public function sphinx_search_friendly_redirect() {
		if ( ! self::sphinx_is_redirect_required( self::$plugin->config->get_option( 'seo_url_all' ) ) ) {
			return false;
		}

		$query_array = array();
		if ( ! empty( $_GET['search_comments'] ) ) {
			$query_array[] = "search_comments=" . $_GET['search_comments'];
		}
		if ( ! empty( $_GET['search_posts'] ) ) {
			$query_array[] = "search_posts=" . $_GET['search_posts'];
		}
		if ( ! empty( $_GET['search_pages'] ) ) {
			$query_array[] = "search_pages=" . $_GET['search_pages'];
		}
		if ( ! empty( $_GET['search_tags'] ) ) {
			$query_array[] = "search_tags=" . $_GET['search_tags'];
		}
		if ( ! empty( $_GET['search_sortby'] ) ) {
			$query_array[] = "search_sortby=" . $_GET['search_sortby'];
		}
		$query_string = '';
		if ( ! empty( $query_array ) ) {
			$query_string = "?" . implode( "&", $query_array );
		}

		$permalinkOption = get_option( 'permalink_structure' );
		$permPrefix      = '';
		if ( false !== strpos( $permalinkOption, '/index.php' ) ) {
			$permPrefix = '/index.php';
		}

		if ( function_exists( 'home_url' ) ) {
			wp_redirect( home_url( $permPrefix . '/search/' . urlencode( get_query_var( 's' ) ) . '/' ) . $query_string );
		} else {
			wp_redirect( get_option( 'home' ) . $permPrefix . '/search/' . urlencode( get_query_var( 's' ) ) . '/' . $query_string );
		}

		exit();
	}


	public function network_activation( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		//replace with your base plugin path E.g. dirname/filename.php
		if ( is_plugin_active_for_network( 'WP/manticoresearch.php' ) ) {
			switch_to_blog( $blog_id );
			$this->sphinx_plugin_activation();
			restore_current_blog();
		}

		return true;
	}

	/**
	 * Install table structure*
	 *
	 */
	public function sphinx_plugin_activation() {

		$config = ManticoreSearch::$plugin->config;



		$config->admin_options['cert_path' ] = SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'certs' .
        DIRECTORY_SEPARATOR . 'ca-cert.pem';

		$config->update_admin_options();
		/* Getting SUBDOMAIN options */
		if ( $config->admin_options['configured'] == 'false' ) {
			//create neccessary tables

			if ( self::is_network() == 'true' && self::is_main_blog() == 'false' ) {
				$primary_blog_id = self::get_main_blog_id();
				if ( ! empty( $primary_blog_id ) ) {

					/* Getting PRIMARY options */
					$config->admin_options                 = get_blog_option( $primary_blog_id,
						Manticore_Config::ADMIN_OPTIONS_NAME );
					$config->admin_options['is_subdomain'] = 'true';
					ManticoreSearch::$plugin->config->update_admin_options( $config->admin_options );
				}
			}

			$sphinxInstall = new Manticore_Install( $config );
			$sphinxInstall->setup_sphinx_counter_tables();

			if ( $config->admin_options['is_subdomain'] == 'true' ) {
				return;
			}

			$wizard     = new WizardController( $config );
			$activation = $wizard->automatic_wizard();
			if ( ! empty( $activation['error'] ) ) {
				$config->update_admin_options(
					[
						'activation_error_message' => 'Automatic wizard failing. <br> Error: '
						                              . $activation['error'] . '<br>Run wizard manually'
					] );
			}
		}
	}

	public static function set_sharding_id( $id, $site_number, $is_comment = false ) {
		return ( $id + ( empty( $is_comment ) ? 1 : 0 ) ) * ( $site_number + self::SHARDS_COUNT );
	}

	public static function is_network() {
		return ( defined( 'MULTISITE' ) && MULTISITE == true ) ? 'true' : 'false';
	}

	public static function get_main_blog_id() {
		return get_user_meta( get_current_user_id(), 'primary_blog', true );
	}

	public static function is_main_blog() {
		if ( is_main_site() ) {
			return 'true';
		}

		return 'false';
	}

	public static function get_network_sites( $exclude_current = true ) {
		$blogs_list = [];

		if ( self::is_network() == 'true' ) {
			$raw_blog_list = get_sites();

			$current_blog = get_current_blog_id();
			foreach ( $raw_blog_list as $blog ) {
				if ( $exclude_current && $current_blog == $blog->blog_id ) {
					continue;
				}
				$blogs_list[ $blog->blog_id ] = $blog->domain;
			}
		} else {
			if ( ! $exclude_current ) {
				$blogs_list[ get_current_blog_id() ] = get_site_url();
			}
		}

		return $blogs_list;
	}


	public static function clear_autocomplete_config() {
		$tmpDir      = sys_get_temp_dir();
		$config_path = $tmpDir . DIRECTORY_SEPARATOR . 'autocomplete_' . get_current_blog_id() . '.tmp';
		if ( file_exists( $config_path ) ) {
			unlink( $config_path );

			return true;
		}

		return false;
	}

	public static function log( $data ) {
		return false;
		$path = wp_upload_dir();
		file_put_contents(
			$path['basedir'] . DIRECTORY_SEPARATOR . 'log.dat',
			date( 'Y-m-d H:i:s' ) . ' ' .
			$data . "\r\n", FILE_APPEND );
	}
}

/**
 * main Sphinx Search object
 */

$defaultObjectSphinxSearch = new ManticoreSearch();

register_activation_hook( __FILE__, [ $defaultObjectSphinxSearch, 'sphinx_plugin_activation' ] );
add_action( 'wpmu_new_blog', [ $defaultObjectSphinxSearch, 'network_activation' ], 10, 6 );
register_deactivation_hook( __FILE__, 'sphinx_plugin_deactivation' );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'manticore_plugin_action_links', 10, 4 );


/**
 * Output dashboard link in plugin actions
 */
function manticore_plugin_action_links( $plugin_actions, $plugin_file ) {

	if ( is_network_admin() ) {
		$url = admin_url( 'network/options-general.php?page=manticoresearch.php' );
	} else {
		$url = admin_url( 'options-general.php?page=manticoresearch.php' );
	}

	$new_actions = array();

	if ( basename( SPHINXSEARCH_PLUGIN_DIR ) . '/manticoresearch.php' === $plugin_file ) {
		$new_actions['ms_dashboard'] = sprintf( __( '<a href="%s">Dashboard</a>', 'manticoresearch' ),
			esc_url( $url ) );
	}

	return array_merge( $new_actions, $plugin_actions );
}

/**
 * Stop manticore daemon on plugin deactivation
 */
function sphinx_plugin_deactivation() {
	ManticoreSearch::$plugin->service->stop();
}



