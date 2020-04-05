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

class Manticore_Install {

	/**
	 * Directory location of Sphinx Search plugin
	 *
	 * @var string
	 */
	var $plugin_sphinx_dir = '';

	/**
	 * Url to download searchd
	 *
	 * @var string
	 */
	var $url_download_searchd = 'http://dev.manticoresearch.com/searchd';

	/**
	 * Config object
	 */
	var $config = '';

	/**
	 * Constructor
	 *
	 * @param Manticore_Config $config
	 *
	 * @return void
	 */
	function __construct( Manticore_Config $config ) {
		$this->config            = $config;
		$this->plugin_sphinx_dir = SPHINXSEARCH_PLUGIN_DIR;
	}

	/**
	 * Return installation directory for Sphinx
	 * by default it /wp-content/uploads/sphinx_install/
	 *
	 * @return string
	 */
	function get_install_dir() {
		//save install dir in admin options
		$this->config->admin_options['sphinx_path'] = SPHINXSEARCH_SPHINX_INSTALL_DIR;

		return $this->config->admin_options['sphinx_path'];
	}


	function download_manticore( $path ) {
		if ( empty( $path ) ) {
			$path = $this->get_install_dir();
		}

		if ( substr( $path, - 1 ) != DIRECTORY_SEPARATOR ) {
			$path = $path . DIRECTORY_SEPARATOR;
		}
		if ( ! empty( $path ) ) {

			$path = $path . 'bin';

			if ( ! file_exists( $path ) ) {
				if ( ! @mkdir( $path, 0777, true ) ) {
					return [ 'error' => 'Path ' . $path . 'is not writable' ];
				}
			}

			$searchd_path = $path . DIRECTORY_SEPARATOR . 'searchd';
			$result       = file_put_contents( $searchd_path, fopen( $this->url_download_searchd, 'r' ) );
			if ( $result ) {
				chmod( $searchd_path, 0777 );

				return [ 'searchd_path' => $searchd_path ];
			}
		}

		$error = error_get_last();
		if ( ! empty( $error['message'] ) ) {
			$error = $error['message'];
		} else {
			$error = 'Undefined error';
		}

		return [ 'error' => $error ];
	}


	function setup_sphinx_counter_tables() {
		global $table_prefix, $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		//////////////////
		//Create sph_stats
		//table
		//////////////////
		$sql = "CREATE TABLE IF NOT EXISTS  `" . $table_prefix . "sph_stats`(
				`id` int(11) unsigned NOT NULL auto_increment,
				`keywords` varchar(255) NOT NULL default '',
				`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
				`keywords_full` varchar(255) NOT NULL default '',
                                `status` tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY  (`id`),
				KEY `keywords` (`keywords`),
				FULLTEXT `ft_keywords` (`keywords`)
				) ENGINE=MyISAM;";
		dbDelta( $sql );


		$sql = "CREATE TABLE IF NOT EXISTS  `" . $table_prefix . "sph_indexing_counters`(
				`id` int(11) unsigned NOT NULL auto_increment,
				`finished` tinyint(1) unsigned NOT NULL DEFAULT 1,
				`type` ENUM('posts','comments','attachments','stats') NOT NULL DEFAULT 'posts',
				`index_name` ENUM('main','stats') NOT NULL DEFAULT 'main',
				`indexed` int(11) unsigned NOT NULL default 0,
				`all_count` int(11) unsigned NOT NULL default 0,
				PRIMARY KEY  (`id`)
				) ENGINE=InnoDB;";
		dbDelta( $sql );

		$sql = 'CREATE TABLE IF NOT EXISTS  `' . $table_prefix . 'sph_indexing_attachments` ( 
				`id` INT(11) NOT NULL , 
				`indexed` TINYINT(1) NOT NULL DEFAULT 0,
				`parent_id` INT(11) NOT NULL , 
				`content` TEXT NOT NULL , 
				`hash` CHAR(40) NOT NULL, 
				PRIMARY KEY  (`id`)
				) ENGINE = InnoDB;';
		dbDelta( $sql );

		$wpdb->replace( $table_prefix . 'sph_indexing_counters', [
			'id'         => 1,
			'type'       => 'posts',
			'index_name' => 'main',
			'indexed'    => 0,
			'all_count'  => 0
		] );
		$wpdb->replace( $table_prefix . 'sph_indexing_counters', [
			'id'         => 2,
			'type'       => 'comments',
			'index_name' => 'main',
			'indexed'    => 0,
			'all_count'  => 0
		] );
		$wpdb->replace( $table_prefix . 'sph_indexing_counters', [
			'id'         => 3,
			'type'       => 'attachments',
			'index_name' => 'main',
			'indexed'    => 0,
			'all_count'  => 0
		] );
		$wpdb->replace( $table_prefix . 'sph_indexing_counters', [
			'id'         => 4,
			'type'       => 'stats',
			'index_name' => 'stats',
			'indexed'    => 0,
			'all_count'  => 0
		] );

		return true;
	}
}
