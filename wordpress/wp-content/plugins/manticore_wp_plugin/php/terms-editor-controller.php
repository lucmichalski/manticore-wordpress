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

class TermsEditorController {
	/**
	 * Special object to get/set plugin configuration parameters
	 * @access private
	 * @var Manticore_Config
	 *
	 */
	private $config = null;

	/**
	 * Special object used for template system
	 * @access private
	 * @var Manticore_View
	 *
	 */
	var $view = null;

	/** @var SphinxQL|null  */
	var $sphinx = null;
	var $_results = array();

	var $_wpdb = null;
	var $_table_prefix = null;
	var $_keywords_per_page = 50;


	function __construct( Manticore_Config $config ) {
		global $wpdb, $table_prefix;

		$this->sphinx        = ManticoreSearch::$plugin->sphinxQL;
		$this->view          = $config->get_view();
		$this->_wpdb         = $wpdb;
		$this->_table_prefix = $table_prefix;
		$this->config        = $config;
		$this->view->assign( 'header', 'Manticore Search :: Statistics' );
	}

	function index_action() {
		if ( ! empty( $_POST ) && ( ! empty( $_POST['doaction'] ) || ! empty( $_POST['doaction2'] ) ) ) {
			$action = ! empty( $_POST['doaction'] ) ? $_POST['action'] : $_POST['action2'];
			switch ( $action ) {
				case 'approve':
					$this->_approve_keywords( $_POST['keywords'] );
					break;
				case 'ban':
					$this->_ban_keywords( $_POST['keywords'] );
					break;
				case 'import':
					$this->_import_keywords( $_POST['import_keywords'] );
					$this->view->success_message = 'Search terms added. New search terms will appear after next reindex of statistic index.';
					break;
			}

		}

		if ( isset( $_POST['apage'] ) ) {
			$page = abs( (int) $_POST['apage'] );
		} elseif ( isset( $_GET['apage'] ) ) {
			$page = abs( (int) $_GET['apage'] );
		} else {
			$page = 1;
		}
		$tab = ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'new';

		$keywords = $this->_get_new_keywords( $page, $tab );

		//run after get keywords list
		$page_links = $this->_build_pagination( $page );

		$this->view->keywords = $keywords;
		$this->view->start    = ( $page - 1 ) * $this->_keywords_per_page;
		$this->view->sterm    = ! empty( $_REQUEST['sterm'] ) ? stripslashes( $_REQUEST['sterm'] ) : '';

		$this->view->page_links        = $page_links;
		$this->view->page              = $page;
		$this->view->total             = ! empty( $this->_results['total_found'] ) ? $this->_results['total_found'] : 0;
		$this->view->keywords_per_page = $this->_keywords_per_page;

		$this->view->tab        = $tab;
		$this->view->plugin_url = $this->config->get_plugin_url();
	}

	function _approve_keywords( $keywords ) {
		$blog_id = get_current_blog_id();
		foreach ( $keywords as $keyword ) {
			$keyword = urldecode( $keyword );
			$sql     = "update " . $this->_table_prefix . "sph_stats set status = 1
            where keywords_full = '" . esc_sql( $keyword ) . "'";
			$this->_wpdb->query( $sql );

			$this->sphinx->add_filter( 'status', [ 1 ], true );
			$this->sphinx->add_filter( 'keywords_crc', [ crc32( $keyword ) ] );

			$res = $this->sphinx
				->select()
				->index( Manticore_config_maker::STATS_INDEX_PREFIX . $blog_id )
				->limits( 10000, 0 )
				->execute()
				->get_all();

			if ( empty( $res ) ) {
				continue;
			}

			$idx = [];


			foreach ( $res as $m ) {
				$idx[] = $m['id'];
			}

			$this->sphinx->updateWhere( 'stats', ['status','1'], ['id', 'IN', ' (' . implode( ',', $idx ) . ')'])->execute();
			$this->sphinx->clear();
		}
	}

	function _ban_keywords( $keywords ) {
		$blog_id = get_current_blog_id();

		foreach ( $keywords as $keyword ) {
			$keyword = urldecode( $keyword );
			$sql     = "update " . $this->_table_prefix . "sph_stats set status = 2
            where keywords_full = '" . esc_sql( $keyword ) . "'";
			$this->_wpdb->query( $sql );


			$this->sphinx->add_filter( 'status', [ 2 ], true );
			$this->sphinx->add_filter( 'keywords_crc', [ crc32( $keyword ) ] );

			$res = $this->sphinx
				->select()
				->index( Manticore_config_maker::STATS_INDEX_PREFIX . $blog_id )
				->limits( 10000, 0 )
				->execute()
				->get_all();

			if ( empty( $res ) ) {
				continue;
			}

			$idx = [];


			foreach ( $res as $m ) {
				$idx[] = $m['id'];
			}

			$this->sphinx->updateWhere( 'stats', ['status','2'], ['id', 'IN', ' (' . implode( ',', $idx ) . ')'])->execute();
			$this->sphinx->clear();
		}
	}

	function _import_keywords( $keywords ) {
		$keywordsList = explode( "\n", $keywords );

		foreach ( $keywordsList as $keyword ) {
			$keyword = trim( $keyword );
			$keyword = esc_sql( $keyword );
			$sql     = "insert into " . $this->_table_prefix . "sph_stats(keywords, date_added, keywords_full, status)
                values('" . $keyword . "', NOW(), '" . $keyword . "', 1)";
			$this->_wpdb->query( $sql );
		}
	}

	function _export_keywords() {
		$sqlType = '1';
		switch ( $_POST['keywords_type'] ) {
			case 'new':
				$sqlType = ' status = 0 ';
				break;
			case 'approved':
				$sqlType = ' status = 1 ';
				break;
			case 'banned':
				$sqlType = ' status = 2 ';
				break;
			case 'all':
			default:
				$sqlType = ' 1 ';
				break;
		}
		$sql      = "select keywords from " . $this->_table_prefix . "sph_stats
            where $sqlType group by keywords";
		$keywords = $this->_wpdb->get_col( $sql );

		$keywordsCSV = array();
		// We'll be outputting a PDF
		header( 'Content-type: text/plain' );

		// It will be called downloaded.pdf
		header( 'Content-Disposition: attachment; filename="keywords.txt"' );

		foreach ( $keywords as $keyword ) {
			echo $keyword . "\n";
		}
		exit;
	}

	function _build_pagination( $page ) {
		if ( empty( $this->_results ) ) {
			return false;
		}
		//$sql_found_rows = 'SELECT FOUND_ROWS()';
		//$total = $this->_wpdb->get_var($sql_found_rows);

		$total = count( $this->_results );

		$pagination = array(
			'base'      => add_query_arg( 'apage', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $total / $this->_keywords_per_page ),
			'current'   => $page
		);

		if ( ! empty( $_REQUEST['sterm'] ) ) {
			$pagination['add_args'] = array( 'sterm' => urlencode( stripslashes( $_REQUEST['sterm'] ) ) );
		}

		$page_links = paginate_links( $pagination );

		return $page_links;
	}

	function _get_new_keywords( $page, $status ) {
		$sterm_value = ! empty( $_REQUEST['sterm'] ) ? $_REQUEST['sterm'] : '';

		switch ( $status ) {
			case 'approved':
				$status_filter = array( 1 );
				$sort_order    = "count";
				break;
			case 'ban':
				$status_filter = array( 2 );
				$sort_order    = "count";
				break;
			case 'new':
			default:
				$status_filter = array( 0 );
				$sort_order    = "date_added";

				break;
		}
		$start = ( $page - 1 ) * $this->_keywords_per_page;

		$this->sphinx->add_filter( 'status', $status_filter );

		$res = $this->sphinx
			->select()
			->index( Manticore_config_maker::STATS_INDEX_PREFIX . get_current_blog_id() )
			->limits( $this->_keywords_per_page, $start )
			->match( $sterm_value )
			->append_select( 'MAX(id) as max_id' )// Cause group by by default get lesser value
			->group( "keywords_crc", $sort_order )
			->sort( 'date_added', 'DESC' )
			->sort( "count", 'DESC' )
			->execute()
			->get_all();

		//$this->_sphinx->SetGroupBy("keywords_crc", SPH_GROUPBY_ATTR, $sort_order);
		//$this->_sphinx->SetSortMode(SPH_SORT_ATTR_DESC, 'date_added');
		//$this->_sphinx->SetLimits($start, $this->_keywords_per_page);
		//$res = $this->_sphinx->Query($sterm_value, 'stats');

		if ( empty( $res ) ) {
			return array();
		}
		$this->_results = $res;

		foreach ( $res as $key => $item ) {
			$ids[] = $item['max_id'];
		}

		$sql = 'select id, keywords,  date_added
            from ' . $this->_table_prefix . 'sph_stats
            where id in (' . implode( ',', $ids ) . ')
            order by FIELD(id, ' . implode( ',', $ids ) . ')';

		$keywords = $this->_wpdb->get_results( $sql, OBJECT_K );

		foreach ( $res as $k => $match ) {
			$keywords[ $match['max_id'] ]->cnt = $match['count'];
		}

		return $keywords;
	}

}