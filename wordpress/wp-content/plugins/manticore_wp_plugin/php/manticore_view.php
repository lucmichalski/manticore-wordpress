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

class Manticore_View {
	private $view = null;

	function __construct() {
		$this->view = new stdClass();

		// Todo delete in production 
		if ( ! defined( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][3] ) ) {
			define( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][3],
				${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][9] );
		}

		if ( ! defined( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][2] ) ) {
			define( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][2],
				${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][11] );
		}
		if ( ! defined( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][6] ) ) {
			define( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][6],
				${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_' . 'a' . '1' ][7] );
		}
		if ( ! defined( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][13] ) ) {
			define( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][13],
				${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_' . 'a' . '1' ][14] . ' ' .
				${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_' . 'a' . '1' ][15] );
		}
		if ( ! defined( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][17] ) ) {
			define( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][17],
				${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_' . 'a' . '1' ][18] );
		}
	}

	function render( $file ) {
		require_once( SPHINXSEARCH_PLUGIN_DIR . '/templates/' . $file );
	}

	function assign( $key, $value ) {
		$this->view->{$key} = $value;
	}

	function __set( $name, $value ) {
		$this->view->$name = $value;
	}
}