<?php

abstract class Manticore_Config_Maker {

	const MAIN_INDEX_PREFIX = 'main_blog_';
	const STATS_INDEX_PREFIX = 'stats_blog_';
	const AUTOCOMPLETE_INDEX_PREFIX = 'autocomplete_blog_';
	const MAIN_DISTRIBUTED_INDEX_PREFIX = 'blog_';
	const AUTOCOMPLETE_DISTRIBUTED_INDEX_PREFIX = 'au_blog_';

	protected $config = null;
	protected $index_path;
	private $search_in_blogs = [];

	private $indexes = [];
	private $distributed_indexes = [];

	public function __construct( Manticore_Config $config ) {
		$this->config = $config;
		$this->setIndexPath();


		$exclude_blogs = $this->config->get_option( 'exclude_blogs_from_search' );
		$blogs         = ManticoreSearch::get_network_sites( false );

		foreach ( $blogs as $blog_id => $blog_url ) {
			if ( in_array( $blog_id, $exclude_blogs ) ) {
				unset( $blogs[ $blog_id ] );
			}
		}


		$this->search_in_blogs[ get_current_blog_id() ] = $this->config->admin_options['search_in_blogs'];


		foreach ( $blogs as $blog_id => $blog ) {
			if ( function_exists( 'get_blog_option' ) ) {
				$option = get_blog_option( $blog_id, Manticore_Config::ADMIN_OPTIONS_NAME );
			} else {
				$option = $this->config->admin_options;
			}

			if ( ! empty( $option['search_in_blogs'] ) ) {
				$this->search_in_blogs[ $blog_id ] = $option['search_in_blogs'];
			} else {
				$this->search_in_blogs[ $blog_id ] = [ $blog_id ];
			}
		}
	}

	abstract protected function setIndexPath();


	public function get_config() {

		foreach ( $this->search_in_blogs as $blog => $search_in ) {
			$this->indexes[]             = $this->get_main_index_data( $blog );
			$this->indexes[]             = $this->get_autocomplete_index( $blog );
			$this->indexes[]             = $this->get_stats_section( $blog );
			$this->distributed_indexes[] = $this->get_main_distributed_index( $blog, $search_in );
			$this->distributed_indexes[] = $this->get_autocomplete_distributed_index( $blog, $search_in );
		}


		return implode( "\t\n", $this->indexes ) . "\t\n" .
		       implode( "\t\n", $this->distributed_indexes ) .
		       $this->get_searchd_section();
	}

	protected function get_main_index_data( $id ) {
		return "index " . self::MAIN_INDEX_PREFIX . $id . "\r\n"
		       . "{\r\n"
		       . "\t type                = rt\r\n"
		       . "\t rt_attr_uint        = blog_id\r\n"
		       . "\t rt_attr_uint        = comment_ID\r\n"
		       . "\t rt_attr_uint        = post_ID\r\n"
		       . "\t rt_field            = title\r\n"
		       . "\t rt_field            = body\r\n"
		       . "\t rt_field            = category\r\n"
		       . "\t rt_field            = tags\r\n"
		       . "\t rt_field            = taxonomy\r\n"
		       . "\t rt_field            = attachments\r\n"
		       . "\t rt_field            = custom_fields\r\n"
		       . "\t rt_attr_uint        = isPost\r\n"
		       . "\t rt_attr_uint        = isPage\r\n"
		       . "\t rt_attr_uint        = isComment\r\n"
		       . "\t rt_attr_uint        = post_type\r\n"
		       . "\t rt_attr_timestamp   = date_added\r\n"

		       . $this->get_charset_table() . "\r\n"

		       . "\t min_infix_len	    = 2\r\n"
		       . "\t path                = " . $this->index_path . "/data/main_" . $id . "\r\n"
		       . "\t docinfo             = extern\r\n"
		       . "\t morphology          = stem_enru\r\n"
		       . "\t html_strip          = 1\r\n"
		       . "}\r\n\r\n";
	}

	protected function get_autocomplete_index( $id ) {
		return "index " . self::AUTOCOMPLETE_INDEX_PREFIX . $id . "\r\n"
		       . "{\r\n"
		       . "\t type                 = rt\r\n"
		       . "\t bigram_index         = all\r\n"

		       . "\t rt_attr_uint         = post_ID\r\n"
		       . "\t rt_attr_uint         = advanced\r\n"
		       . "\t rt_field             = content\r\n"
		       . "\t rt_attr_string       = string_content\r\n"

		       . $this->get_charset_table() . "\r\n"

		       . "\t min_infix_len	    = 2\r\n"
		       . "\t path                = " . $this->index_path . "/data/autocomplete_" . $id . "\r\n"
		       . "\t docinfo             = extern\r\n"
		       . "\t morphology          = stem_enru\r\n"
		       . "\t html_strip          = 1\r\n"
		       . "}\r\n\r\n";
	}

	protected function get_main_distributed_index( $blog_id, $search_in ) {
		if ( empty( $search_in ) ) {
			$search_in = [ $blog_id ];
		}

		$index_data = "index " . self::MAIN_DISTRIBUTED_INDEX_PREFIX . $blog_id . " {\r\n" .
		              "\ttype \t\t= distributed\r\n";

		foreach ( $search_in as $item ) {
			$index_data .= "\tlocal \t\t= " . self::MAIN_INDEX_PREFIX . $item . "\r\n";
		}
		$index_data .= "}\r\n\r\n";

		return $index_data;
	}


	protected function get_autocomplete_distributed_index( $blog_id, $search_in ) {
		if ( empty( $search_in ) ) {
			$search_in = [ $blog_id ];
		}

		$index_data = "index " . self::AUTOCOMPLETE_DISTRIBUTED_INDEX_PREFIX . $blog_id . " {\r\n" .
		              "\ttype \t\t= distributed\r\n";

		foreach ( $search_in as $item ) {
			$index_data .= "\tlocal \t\t= " . self::AUTOCOMPLETE_INDEX_PREFIX . $item . "\r\n";
		}
		$index_data .= "}\r\n\r\n";

		return $index_data;
	}

	protected function get_stats_section( $blog_id ) {
		return "index " . self::STATS_INDEX_PREFIX . $blog_id . "\r\n" .
		       "{\r\n" .
		       "\t type= rt\r\n" .

		       "\t rt_field            = keywords\r\n" .
		       "\t rt_attr_uint        = status\r\n" .
		       "\t rt_attr_uint        = keywords_crc\r\n" .
		       "\t rt_attr_timestamp   = date_added\r\n" .

		       "\t path                = " . $this->index_path . "/data/stats_" . $blog_id . "\r\n" .
		       "\t docinfo             = extern\r\n" .
		       "\t morphology          = stem_enru\r\n" .
		       "\t html_strip          = 1\r\n" .
		       $this->get_charset_table() . "\r\n" .
		       "}\r\n\r\n";
	}

	abstract protected function get_searchd_section();


	protected function get_charset_table() {
		return
			"\tcharset_table       = non_cjk

        ngram_len          = 1
        ngram_chars        = cjk\r\n\r\n";
	}
}
