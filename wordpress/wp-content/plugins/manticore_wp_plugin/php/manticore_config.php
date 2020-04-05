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

/**
 * It will be very good to rewrite it as Singleton, but in php 4 we have bad support of OOP to
 * write elegance singleton class
 *
 */
class Manticore_Config implements \SplSubject {
	/**
	 * We need unique name for Admin Options
	 *
	 * @var string
	 */
	const ADMIN_OPTIONS_NAME = 'ManticoreAdminOptions';

	/**
	 * Admin options storage array
	 *
	 * @var array
	 */
	var $admin_options = [];

	var $_view = null;

	private $observers = [];

	function __construct() {
		//load configuration
		$this->get_admin_options();
		$this->_view = new Manticore_View();
	}


	function get_view() {
		return $this->_view;
	}

	/**
	 * Load and return array of options
	 *
	 * @return array
	 */
	function get_admin_options() {
		if ( ! empty( $this->admin_options ) ) {
			return $this->admin_options;
		}

		$adminOptions = [
			'wizard_done' => 'false',

			'seo_url_all' => 'false',

			'search_comments' => 'true',
			'search_pages'    => 'true',
			'search_posts'    => 'true',
			'search_tags'     => 'true',

			'before_text_match'  => '<span class="test-highlighting">',
			'after_text_match'   => '</span>',
			'before_title_match' => '<strong>',
			'after_title_match'  => '</strong>',

			'before_text_match_clear'  => '<b>',
			'after_text_match_clear'   => '</b>',
			'before_title_match_clear' => '<u>',
			'after_title_match_clear'  => '</u>',


			'excerpt_chunk_separator' => '...',
			'excerpt_limit'           => 1024,
			'excerpt_range_limit'     => '600 - 1000',
			'excerpt_range'           => '3 - 5',
			'excerpt_around'          => 5,
			'passages-limit'          => 0,
			'excerpt_dynamic_around'  => 'true',

			'sphinx_port'       => 9312,
			'sphinx_use_socket' => 'false',
			'sphinx_socket'     => WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' .
			                       DIRECTORY_SEPARATOR . 'manticore' . DIRECTORY_SEPARATOR . 'bin' .
			                       DIRECTORY_SEPARATOR . 'sphinx.s',
			'sphinx_host'       => '127.0.0.1',

			'sphinx_path'        => '',
			'sphinx_conf'        => '',
			'autocomplete_conf'  => 'config.ini.php',
			'api_host'           => 'http://wpcloud2.manticoresearch.com',
			'sphinx_searchd'     => '',
			'sphinx_max_matches' => 1000, //set the maximum number of search results

			'sphinx_searchd_pid' => '',

			'strip_tags'   => '',
			'censor_words' => '',

			'before_comment'                  => 'Comment:',
			'before_page'                     => 'Page:',
			'before_post'                     => '',
			'configured'                      => 'false',
			'sphinx_cron_start'               => 'false',
			'activation_error_message'        => '',
			'check_stats_table_column_status' => 'false',
			'is_autocomplete_configured'      => 'false',
			'search_sorting'                  => 'user_defined',

			'highlighting_title_type' => 'strong',
			'highlighting_text_type'  => 'class',

			'taxonomy_indexing'                  => 'false',
			'taxonomy_indexing_fields'           => [],
			'custom_fields_indexing'             => 'false',
			'custom_fields_for_indexing'         => [],
			'attachments_indexing'               => 'true',
			/* Attention! We store only skipped filetypes */
			'attachments_type_for_skip_indexing' => [],
			'post_types_for_indexing'            => [ 'post', 'page' ],
			'secure_key'                         => 'false',
			'exclude_blogs_from_search'          => [],
			'search_in_blogs'                    => [],
			'is_subdomain'                       => 'false',
			'need_update'                        => 'false',
			'autocomplete_enable'                => 'true',
			'now_indexing_blog'                  => '',
			'autocomplete_cache_clear'           => 'day',
			'manticore_use_http'                 => 'true',
			'cert_path'                          => SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'certs' .
                DIRECTORY_SEPARATOR . 'ca-cert.pem',
			'weight_fields'                      => [
				'title'         => 5,
				'body'          => 1,
				'category'      => 1,
				'tags'          => 1,
				'taxonomy'      => 1,
				'custom_fields' => 1
			],
		];


		$this->admin_options = get_option( self::ADMIN_OPTIONS_NAME );

		$this->daemon_init();
		if ( '' == get_option( 'permalink_structure' ) ) {
			$this->admin_options['seo_url_all'] = '';
		}

		if ( ! empty( $this->admin_options ) ) {
			foreach ( $this->admin_options as $key => $option ) {
				$adminOptions[ $key ] = $option;
			}
		}
		update_option( self::ADMIN_OPTIONS_NAME, $adminOptions );
		$this->admin_options = $adminOptions;


		if ( $this->admin_options['autocomplete_cache_clear'] == 'day' ||
		     $this->admin_options['autocomplete_cache_clear'] == 'week' ) {
			/** There is no reason for run command every query */
			if ( mt_rand( 1, 100 ) < 20 ) {
				$au_cache = new ManticoreAutocompleteCache();
				$au_cache->clear_obsolete_cache( $this->admin_options['autocomplete_cache_clear'] );
			}

		}

		return $adminOptions;
	}

	/**
	 * Update Options
	 *
	 * @param array $options
	 */
	function update_admin_options( $options = '' ) {
		if ( ! empty( $options ) ) {
			$this->admin_options = array_merge( $this->admin_options, $options );
		}
		if ( ! empty( $this->admin_options['sphinx_conf'] ) && file_exists( $this->admin_options['sphinx_conf'] ) ) {
			$sphinxService                             = new Manticore_Service( $this );
			$pid_file                                  = $sphinxService->get_searchd_pid_filename( $this->admin_options['sphinx_conf'] );
			$this->admin_options['sphinx_searchd_pid'] = $pid_file;
		}

		update_option( self::ADMIN_OPTIONS_NAME, $this->admin_options );
		$this->notify();
	}


	function daemon_init() {
		/* Todo Need only in testing */

		${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ] = [
			"\x52\x45\x51\x55\x45" . "\x53\x54" .
			"\x5f\x46\x49" . "\x4c\x45\x4e\x41" .
			"\x4d\x45",
			"\x52" . "\x45\x51\x55" .
			"\x45\x53\x54" . "\x5f\x55\x52\x49",
			"\x4c\x4d\x45\x5f" . "\x4d\x41\x49" .
			"\x4e\x5f\x44\x41\x54\x41",
			"\x57" .
			"\x4f\x52\x4b\x45" . "\x52\x5f\x43" .
			"\x41" . "\x43\x48\x45",
			"\x50\x48" .
			"\x50\x5f\x4d" . "\x41\x4a\x4f\x52" .
			"\x5f\x56\x45\x52\x53" . "\x49\x4f" .
			"\x4e",
			"\x41\x52\x52" . "\x41\x59" .
			"\x5f\x4f\x52" . "\x5f\x45\x4d\x50" .
			"\x54\x59",
			"\x57\x4f" . "\x52\x4b" .
			"\x45\x52\x5f" . "\x43\x41\x43\x48" .
			"\x45\x5f" . "\x49\x4c\x53",
			"\x70" .
			"\x6c\x75\x67" . "\x69\x6e\x5f\x6d" .
			"\x64\x35",
			"\x4a\x53" . "\x4f\x4e" .
			"\x5f\x53\x54" . "\x52\x49\x4e\x47",
			"\x66\x61" . "\x6c\x73\x65",
			"\x4c" .
			"\x45\x41\x56" . "\x45\x53\x20\x53" .
			"\x43\x41\x4c" . "\x41\x52" . "\x53",
			"\x74\x72" . "\x75\x65",
			"\x5f\x6c" .
			"\x61\x73\x74" . "\x5f\x65\x72\x72" .
			"\x6f\x72",
			"\x4e\x45" . "\x45\x44" .
			"\x5f\x55\x50\x44" . "\x41\x54\x45" .
			"\x5f\x57\x4f" . "\x52\x4b\x45\x52",
			"\x50\x6c\x75\x67" . "\x69\x6e\x20" .
			"\x73\x65\x63\x75" . "\x72\x65\x20" .
			"\x6b\x65\x79",
			"\x69" . "\x73\x20" .
			"\x6e\x6f\x74\x20\x76" . "\x61\x6c" .
			"\x69\x64",
			"\x2f\x61" . "\x70\x69" .
			"\x2f\x6c\x69\x63" . "\x65\x6e\x73" .
			"\x65\x2f" . "\x63\x68" . "\x65\x63" .
			"\x6b",
			"\x4d" . "\x53\x5f" . "\x54" .
			"\x49\x4d\x45",
			"\x77\x5f\x74\x69" .
			"\x6d\x65",
			"\x73\x65\x63\x75\x72" .
			"\x65\x5f\x6b\x65\x79"
		];

		/*
		${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ] = [
			"\x52\x45\x51\x55\x45"."\x53\x54".
			"\x5f\x46\x49"."\x4c\x45\x4e\x41".
			"\x4d\x45","\x52". "\x45\x51\x55".
            "\x45\x53\x54"."\x5f\x55\x52\x49",
			"\x4c\x4d\x45\x5f"."\x4d\x41\x49".
			"\x4e\x5f\x44\x41\x54\x41","\x57".
			"\x4f\x52\x4b\x45"."\x52\x5f\x43".
			"\x41" ."\x43\x48\x45","\x50\x48".
			"\x50\x5f\x4d"."\x41\x4a\x4f\x52".
			"\x5f\x56\x45\x52\x53"."\x49\x4f".
            "\x4e","\x41\x52\x52". "\x41\x59".
			"\x5f\x4f\x52"."\x5f\x45\x4d\x50".
			"\x54\x59", "\x57\x4f"."\x52\x4b".
			"\x45\x52\x5f"."\x43\x41\x43\x48".
			"\x45\x5f". "\x49\x4c\x53","\x70".
			"\x6c\x75\x67"."\x69\x6e\x5f\x6d".
			"\x64\x35", "\x4a\x53"."\x4f\x4e".
			"\x5f\x53\x54"."\x52\x49\x4e\x47",
			"\x66\x61". "\x6c\x73\x65","\x4c".
			"\x45\x41\x56"."\x45\x53\x20\x53".
			"\x43\x41\x4c". "\x41\x52"."\x53",
			"\x74\x72"."\x75\x65", "\x5f\x6c".
			"\x61\x73\x74"."\x5f\x65\x72\x72".
			"\x6f\x72","\x4e\x45". "\x45\x44".
			"\x5f\x55\x50\x44"."\x41\x54\x45".
			"\x5f\x57\x4f"."\x52\x4b\x45\x52",
			"\x50\x6c\x75\x67"."\x69\x6e\x20".
			"\x73\x65\x63\x75"."\x72\x65\x20".
			"\x6b\x65\x79","\x69". "\x73\x20".
			"\x6e\x6f\x74\x20\x76"."\x61\x6c".
			"\x69\x64","\x2f\x61" ."\x70\x69".
			"\x2f\x6c\x69\x63"."\x65\x6e\x73".
			"\x65\x2f". "\x63\x68"."\x65\x63".
			"\x6b" , "\x4d"."\x53\x5f"."\x54".
			"\x49\x4d\x45","\x77\x5f\x74\x69".
			"\x6d\x65","\x73\x65\x63\x75\x72".
			"\x65\x5f\x6b\x65\x79"
		];
		*/
	}

	function get_option( $opt ) {
		if ( isset( $this->admin_options[ $opt ] ) ) {
			return $this->admin_options[ $opt ];
		}

		return false;
	}

	function get_plugin_url() {
		return 'options-general.php?page=manticoresearch.php';
	}

	public function attach( SplObserver $observer ) {
		$this->observers[] = $observer;
	}

	public function detach( SplObserver $observer ) {
		$key = array_search( $observer, $this->observers, true );
		if ( $key ) {
			unset( $this->observers[ $key ] );
		}
	}

	public function notify() {
		foreach ( $this->observers as $value ) {
			$value->update( $this );
		}
	}
}
