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

class Manticore_Service implements SplObserver {
	/**
	 * @access private
	 * @var object
	 */
	private $config = null;

	function __construct( Manticore_Config $config ) {
		$this->config = $config;
	}

	/**
	 * Start Sphinx search daemon
	 *
	 * @return array|bool - output of start command
	 */
	function start() {

		if ( $this->config->get_option( 'is_subdomain' ) == 'true' ) {
			return [ 'err' => 'Subdomains can\'t start Manticore daemon. Contact wordpress network admin' ];
		}

		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
			return true;
		}

		$this->stop(); //kill daemon if runned
		$command = $this->config->get_option( 'sphinx_searchd' ) . ' -v';
		exec( $command, $output, $retval );

		if ( ! empty( $output[0] ) ) {
			preg_match( '#^[a-zA-Z]+\s(\d+\.\d+\.\d+)#usi', $output[0], $version_matches );
			if ( ! empty( $version_matches[1] ) ) {
				if ( $version_matches[1] <= "2.8.0" ) {
					ManticoreSearch::$plugin->sphinxQL->close();

					return [
						'err' => "Engine version is deprecated. Min version 2.8.0"
					];
				}
			}
		}

		$command = $this->config->get_option( 'sphinx_searchd' ) . " --config " .
		           $this->config->get_option( 'sphinx_conf' );


		exec( $command, $output, $retval );
		if ( preg_match( "#FATAL:(.*)\n#i", implode( " \n ", $output ), $matches ) || $retval != 0 ) {
			ManticoreSearch::$plugin->sphinxQL->close();

			return array(
				'err' => "Can not start searchd, try to start it manually." .
				         '<br/>Command: ' . $command .
				         ( ! empty( $matches[1] ) ? '<br/>Fatal error: ' . $matches[1] : '' )
			);
		}

		if ( ManticoreSearch::$plugin->sphinxQL->connect() ) {
			ManticoreSearch::$plugin->indexer->check_raw_data();

			return true;
		} else {
			return [ 'err' => ManticoreSearch::$plugin->sphinxQL->get_query_error() ];
		}

	}

	/**
	 * Stop Sphinx search daemon
	 *
	 * @return bool|array - output of stop command
	 */
	function stop() {
		//stop Sphinx search daemon

		if ( $this->config->get_option( 'is_subdomain' ) == 'true' ) {
			return [ 'err' => 'Subdomains can\'t stop Manticore daemon. Contact wordpress network admin' ];
		}

		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
			return [ 'err' => 'You using Manticore Remote Cluster. You can\'t stop him' ];
		}

		$output = '';
		if ( $this->is_sphinx_running() ) {
			ManticoreSearch::$plugin->sphinxQL->close();

			$command = $this->config->get_option( 'sphinx_searchd' ) . " --config " .
			           $this->config->get_option( 'sphinx_conf' ) . " --stop";
			exec( $command, $output, $retval );
			if ( preg_match( "#FATAL:(.*)\n#i", implode( " \n ", $output ), $matches ) || $retval != 0 ) {
				return array(
					'err' => "Can not stop searchd, try to stop it manually. " .
					         '<br/>Command: ' . $command .
					         ( ! empty( $matches[1] ) ? '<br/>Fatal error: ' . $matches[1] : '' )
				);
			}
		}

		return true;
	}

	/**
	 * Check running sphinx search daemon or not
	 *
	 * @return boolean
	 */
	function is_sphinx_running() {

		$res = ManticoreSearch::$plugin->sphinxQL->is_active();
		if ( $res ) {
			return true;
		}

		if ( ini_get( "open_basedir" ) == "" && is_readable( "/proc/" ) ) {
			$pid_filename = $this->config->get_option( 'sphinx_searchd_pid' );
			if ( file_exists( $pid_filename ) && is_readable( $pid_filename ) ) {
				$pid = file_get_contents( $pid_filename );
				$pid = trim( $pid );
				if ( file_exists( "/proc/$pid" ) ) {
					return true;
				}
			}

			$pid_filename = $this->get_searchd_pid_filename( $this->config->get_option( 'sphinx_conf' ) );
			if ( file_exists( $pid_filename ) && is_readable( $pid_filename ) ) {
				$pid = file_get_contents( $pid_filename );
				$pid = trim( $pid );
				if ( file_exists( "/proc/$pid" ) ) {
					return true;
				}
			}
		}
		exec( "ps", $output, $retval );
		if ( 0 == $retval ) {
			$pid_filename = $this->config->get_option( 'sphinx_searchd_pid' );
			if ( file_exists( $pid_filename ) && is_readable( $pid_filename ) ) {
				$pid = file_get_contents( $pid_filename );
				$pid = trim( $pid );
				exec( "ps $pid", $output );
				if ( count( $output ) >= 2 ) {
					return true;
				}
			}

			$pid_filename = $this->get_searchd_pid_filename( $this->config->get_option( 'sphinx_conf' ) );
			if ( file_exists( $pid_filename ) && is_readable( $pid_filename ) ) {
				$pid = file_get_contents( $pid_filename );
				$pid = trim( $pid );
				exec( "ps $pid", $output );
				if ( count( $output ) >= 2 ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Parse sphinx conf and grab path to search pid file
	 *
	 * @param string $sphinx_conf filename
	 *
	 * @return string
	 */
	function get_searchd_pid_filename( $sphinx_conf ) {
		if ( ! file_exists( $sphinx_conf ) || ! is_readable( $sphinx_conf ) ) {
			return false;
		}
		$content = file_get_contents( $sphinx_conf );
		if ( preg_match( "#\bpid_file\s+=\s+'.*'(.*)\b#", $content, $m ) ) {
			return $this->config->admin_options['sphinx_path'] . $m[1];
		}

		return '';
	}

	public function update( SplSubject $subject ) {
		$this->config = $subject;
	}
}