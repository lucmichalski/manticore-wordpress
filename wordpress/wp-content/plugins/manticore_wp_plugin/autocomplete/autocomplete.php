<?php
/**
 *
 *
 * @author        Ivinco LTD.
 * @copyright    Copyright (C) 2015 Ivinco Ltd. All rights reserved.
 *
 * The Ivinco Autocomplete widget is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * The Ivinco Autocomplete widget is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the Ivinco Autocomplete widget.  If not, see
 * <http://www.gnu.org/licenses/>.
 * Contributors
 * Please feel free to add your name and email (optional) here if you have
 * contributed any source code changes.
 * Name                            Email
 * Ivinco                    <opensource@ivinco.com>
 *
 */

/* This is way for getting autocomplete queries when config.ini.php succefully writed  */
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

header( 'Content-Type: application/json' );

$include_path = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "include" );
$config_path  = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "configs" );
$config       = (object) parse_ini_file( "{$config_path}/config.ini.php" );

if ( empty( $config->table ) ) {
	$tmpDir                      = sys_get_temp_dir();
	$filename                    = $tmpDir . DIRECTORY_SEPARATOR . 'autocomplete_' . $_REQUEST['search_in'] . '.tmp';
	$config                      = (object) parse_ini_file( $filename );
	$config->tags_names          = explode( ' |*| ', $config->tags_names );
	$config->taxonomy_names      = explode( ' |*| ', $config->taxonomy_names );
	$config->custom_fields_names = explode( ' |*| ', $config->custom_fields_names );
	$config->search_in_blogs     = explode( ' |*| ', $config->search_in_blogs );
}

include_once( "{$include_path}" . DIRECTORY_SEPARATOR . "connectors" . DIRECTORY_SEPARATOR . "ManticoreConnector.php" );
include_once( "{$include_path}" . DIRECTORY_SEPARATOR . "connectors" . DIRECTORY_SEPARATOR . "ManticoreQlConnector.php" );
include_once( "{$include_path}" . DIRECTORY_SEPARATOR . "connectors" . DIRECTORY_SEPARATOR . "ManticoreHttpConnector.php" );
include_once( "{$include_path}/ManticoreAutocompleteCache.php" );
include_once( "{$include_path}/ManticoreAutocompleteTest.php" );

$cache = new ManticoreAutocompleteCache();
if ( ! empty( $_REQUEST['q'] ) &&
     (
	     strpos( $_REQUEST['q'], 'tax:' ) === 0 ||
	     strpos( $_REQUEST['q'], 'field:' ) === 0 ||
	     strpos( $_REQUEST['q'], 'tag:' ) === 0
     ) ) {

	include_once( "{$include_path}/ManticoreAutocompleteAdvanced.php" );
	$autocomplete_engine = new ManticoreAutocompleteAdvanced( $config, $cache );
} else {
	$autocomplete_engine = new ManticoreAutocomplete( $config, $cache );
}


exit( $autocomplete_engine->request( $_REQUEST ) );