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

class WizardController {
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

	public function __construct( Manticore_Config $config ) {
		$this->view   = $config->get_view();
		$this->config = $config;
		$this->view->assign( 'header', 'Manticore Search :: Wizard' );
	}

	public function stop_action() {

		$options = [ 'wizard_done' => 'true', 'configured' => 'false', 'activation_error_message' => '' ];
		$this->config->update_admin_options( $options );

		return $this->_next_action( 'config' );
	}

	public function start_action() {
		if ( ! empty( $_POST['start_process'] ) ) {
			ManticoreSearch::$plugin->service->stop();

			$options = [ 'wizard_done' => 'false', 'configured' => 'false', 'activation_error_message' => '', 'manticore_use_http'=> 'false' ];
			$this->config->update_admin_options( $options );

			return $this->_next_action( 'start' );
		}

		return false;
	}

	public function connection_socket_action() {
		if ( ! empty( $_POST['connection_process'] ) ) {
			if ( empty( $_POST['sphinx_socket'] ) ) {
				$this->view->assign( 'error_message', 'Connection parameters can\'t be empty' );

			} else {
				$this->_set_sphinx_connect();
				$this->view->assign( 'success_message', 'Connection parameters successfully set.' );

				return $this->_next_action( 'connection' );
			}
		}

		$this->view->assign( 'sphinx_socket', $this->config->get_option( 'sphinx_socket' ) );

		$this->view->render( 'admin/wizard/sphinx_connection_socket.phtml' );
		exit();
	}

	public function connection_port_action() {
		if ( ! empty( $_POST['skip_wizard_connection'] ) ) {
			$this->view->assign( 'success_message', 'Step was skipped.' );

			return $this->_next_action( 'connection' );
		}
		if ( ! empty( $_POST['connection_process'] ) ) {
			if ( empty( $_POST['sphinx_host'] ) ||
			     empty( $_POST['sphinx_port'] ) ) {
				$this->view->assign( 'error_message', 'Connection parameters can\'t be empty' );
				$this->view->assign( 'sphinx_host', $_POST['sphinx_host'] );
				$this->view->assign( 'sphinx_port', $_POST['sphinx_port'] );
				$this->view->render( 'admin/wizard/sphinx_connection.phtml' );
			} else {
				$this->_set_sphinx_connect();
				$this->view->assign( 'success_message', 'Connection parameters successfully set.' );

				return $this->_next_action( 'connection' );
			}
		} else {
			$this->view->assign( 'sphinx_host', $this->config->get_option( 'sphinx_host' ) );
			$this->view->assign( 'sphinx_port', $this->config->get_option( 'sphinx_port' ) );
			$this->view->render( 'admin/wizard/sphinx_connection_port.phtml' );
		}
		exit;
	}

	public function add_secure_key_action() {

		if ( ! empty( $_POST['secure_key'] ) ) {
			$request = ManticoreSearch::$plugin->api->get( ManticoreSearch::LICENSE_SECTION,
				[ 'secure_key' => htmlentities( $_POST['secure_key'], ENT_QUOTES ), 'first' => true ] );

			if ( ! empty( $request ) ) {
				if ( empty( $request['w_time'] ) ) {
					$this->view->assign( 'error_message', NEED_UPDATE_WORKER );
				}
				if ( $request['status'] == 'success' ) {
					unset( $request['status'] );
					$devOptions = array_merge( $this->config->admin_options, $request );
					$this->config->update_admin_options( $devOptions );
					if ( $this->config->admin_options[ WORKER_CACHE_ILS ] != WORKER_CACHE ) {
						return $this->_next_action( 'start' );
					}
				} else {
					$this->view->assign( 'error_message', $request['message'] );
				}
			} else {
				$this->view->assign( 'error_message', 'Service unavailable! Try again later...' );
			}
		}

		$this->view->render( 'admin/wizard/manticore_add_secure_key.phtml' );
		exit();
	}

	function select_connection_type_action() {
		if ( ! empty( $_POST['connect_type'] ) ) {
			if ( $_POST['connect_type'] == 'socket' ) {
				return $this->_next_action( 'connection_socket' );
			} elseif ( $_POST['connect_type'] == 'port' ) {
				return $this->_next_action( 'connection_port' );
			} elseif ( $_POST['connect_type'] == 'cluster' ) {
				$this->config->update_admin_options([ 'manticore_use_http'=> 'true' ] );
				return $this->_next_action( 'config_http_action' );
			} else {
				$this->view->assign( 'error_message', 'Connection type can be only "Cluster", "Socket" or "Port"' );
			}
		}
		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
			$connect_to = 'cluster';
		} elseif ( $this->config->admin_options['sphinx_use_socket'] == 'true' ) {
			$connect_to = 'socket';
		} else {
			$connect_to = 'port';
		}

		$this->view->assign( 'connect_to', $connect_to );
		$this->view->render( 'admin/wizard/manticore_select_connect_type.phtml' );
		exit();
	}

	function detection_action() {
		$detect_system_searchd    = $this->detect_program( 'searchd' );
		$detect_installed_searchd = $this->config->get_option( 'sphinx_searchd' );
		if ( ! file_exists( $detect_installed_searchd ) ) {
			$detect_installed_searchd = '';
		}

		$this->view->assign( 'detect_system_searchd', $detect_system_searchd );
		$this->view->assign( 'detect_installed_searchd', $detect_installed_searchd );
		$this->view->assign( 'install_path',
			WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'manticore' );

		if ( ! empty( $_POST['skip_wizard_detection'] ) ) {
			if ( empty( $detect_installed_searchd ) ) {
				$this->view->assign( 'success_message', 'Manticore is not installed. All step was skipped.' );

				return $this->_next_action( 'config' );
			} else {
				$this->view->assign( 'success_message', 'Step was skipped.' );

				return $this->_next_action( 'detection' );
			}
		}


		if ( ! empty( $_POST['detection_process'] ) ) {
			if ( 'detect_system' == $_POST['detected_install'] ) {
				if ( empty( $_POST['detected_system_searchd'] ) ) {
					$this->view->assign( 'error_message', 'Path to searchd can\'t be empty' );
					$this->view->render( 'admin/wizard/sphinx_detect.phtml' );
					exit;
				} else {
					$this->_set_sphinx_detected( $_POST['detected_system_searchd'] );
					$this->view->assign( 'success_message', 'Manticore binaries are set.' );

					return $this->_next_action( 'detection' );
				}
			} elseif ( 'detect_installed' == $_POST['detected_install'] ) {
				if ( empty( $_POST['detected_installed_searchd'] ) ) {
					$this->view->assign( 'error_message', 'Path to searchd can\'t be empty' );
					$this->view->render( 'admin/wizard/sphinx_detect.phtml' );
					exit;
				} else {
					$this->_set_sphinx_detected( $_POST['detected_installed_searchd'] );
					$this->view->assign( 'success_message', 'Manticore binaries are set.' );

					return $this->_next_action( 'detection' );
				}
			} elseif ( $_POST['detected_install'] == 'download' ) {
				$sphinxInstall = new Manticore_Install( $this->config );
				$path          = $sphinxInstall->download_manticore( $_POST['download_binares_path'] );

				if ( ! isset( $path['error'] ) ) {
					$this->_set_sphinx_detected( $path['searchd_path'] );
					$this->view->assign( 'success_message',
						'Manticore Search daemon binary has been saved in ' . $path['searchd_path'] );

					return $this->_next_action( 'detection' );
				} else {
					$this->view->assign( 'error_message',
						'An error occurred while downloading the binaries. ' . $path['error'] );
					$this->view->render( 'admin/wizard/sphinx_detect.phtml' );
				}


			}
		}
		$this->view->render( 'admin/wizard/sphinx_detect.phtml' );
		exit;
	}

	function configure_autocomplete_action() {

		if ( ! empty( $_POST['skip_wizard_configure_autocomplete'] ) ) {
			$this->_next_action( 'indexing' );
		}
		$autocomplete_config_name = $this->config->get_option( 'autocomplete_conf' );
		if ( ! empty( $autocomplete_config_name ) ) {

			$autocomplete_config_content = $this->_generate_autocomplete_config_content();
			$configFolder                = SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'autocomplete' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;

			$res = $this->save_config( $configFolder . $autocomplete_config_name, $autocomplete_config_content );

			if ( false == $res ) {
				$options['is_autocomplete_configured'] = 'false';
			} else {
				$options['is_autocomplete_configured'] = 'true';
			}

			$this->config->update_admin_options( $options );
		}

		return $this->_next_action( 'indexing' );
	}


	private function checkFolders( $sphinx_install_path ) {
		$error_message = '';

		if ( empty( $sphinx_install_path ) ) {
			$error_message = 'Path can\'t be empty';
		}
		if ( ! file_exists( $sphinx_install_path ) ) {
			@mkdir( $sphinx_install_path, 0777, true );
		}

		if ( ! file_exists( $sphinx_install_path ) ) {
			$error_message = 'Path ' . $sphinx_install_path . ' does not exist! Permission denied.';
		} elseif ( ! is_writable( $sphinx_install_path ) ) {
			$error_message = 'Path ' . $sphinx_install_path . ' is not writeable!';
		}

		if ( empty( $error_message ) ) {
			if ( ! file_exists( $sphinx_install_path . '/reindex' ) ) {
				mkdir( $sphinx_install_path . '/reindex' );
				chmod( $sphinx_install_path . '/reindex', 0777 );
			}
			if ( ! file_exists( $sphinx_install_path . '/reindex' ) ) {
				$error_message = 'Path ' . $sphinx_install_path . '/reindex does not exist!';
			}
			if ( ! file_exists( $sphinx_install_path . '/var' ) ) {
				mkdir( $sphinx_install_path . '/var' );
			}
			if ( ! file_exists( $sphinx_install_path . '/var' ) ) {
				$error_message = 'Path ' . $sphinx_install_path . '/var does not exist!';
			}

			if ( ! file_exists( $sphinx_install_path . '/var/data' ) ) {
				mkdir( $sphinx_install_path . '/var/data' );
			}
			if ( ! file_exists( $sphinx_install_path . '/var/data' ) ) {
				$error_message .= '<br/>Path ' . $sphinx_install_path . '/var/data does not exist!';
			}

			if ( ! file_exists( $sphinx_install_path . '/var/log' ) ) {
				mkdir( $sphinx_install_path . '/var/log' );
			}

			if ( ! file_exists( $sphinx_install_path . '/var/log' ) ) {
				$error_message .= '<br/>Path ' . $sphinx_install_path . '/var/log does not exist!';
			}
		}

		if ( empty( $error_message ) ) {
			$this->_setup_sphinx_path();
			$config_file_name = $this->_generate_config_file_name();
			if ( empty( $config_file_name ) ) {
				$error_message = 'Path ' . $sphinx_install_path . ' is not writeable!';
			}
		}

		if ( empty( $error_message ) ) {
			$config_file_content = $this->generate_config_file_content();
			$res                 = $this->save_config( $config_file_name, $config_file_content );

			if ( false == $res ) {
				$error_message = 'Path ' . $sphinx_install_path . '/manticore.conf is not writeable!';
			}
		}

		return $error_message;
	}

	function folder_action() {

		if ( ! empty( $_POST['skip_wizard_folder'] ) ) {
			$currrent_path_value = $this->config->get_option( 'sphinx_path' );

			if ( empty( $currrent_path_value ) or "false" == $currrent_path_value ) {
				$this->view->assign( 'success_message',
					'Manticore is not installed. Some significant steps were skipped.' );

				return $this->_next_action( 'config' );
			} else {
				$this->view->assign( 'success_message', 'Step was skipped.' );

				$this->_next_action( 'indexing' );
			}
			exit;

		}

		if ( ! empty( $_POST['folder_process'] ) ) {
			$error_message = $this->checkFolders( $_POST['sphinx_path'] );

			if ( empty( $error_message ) ) {

				$this->view->assign( 'success_message',
					'Path to index files has been set as ' . $_POST['sphinx_path'] );

				return $this->_next_action( 'folder' );
			}

			$this->view->assign( 'error_message', $error_message );
			$this->view->assign( 'install_path', $_POST['sphinx_path'] );
		} else {
			$this->view->assign( 'install_path',
				WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'manticore' );

		}

		$this->view->render( 'admin/wizard/sphinx_folder.phtml' );
		exit;
	}


	function config_http_action() {

		$config = $this->generate_config_file_content();
		$this->view->assign( 'config_content', $config );
		$this->view->assign( 'sphinx_conf', 'remote_cluster' );
		$this->view->render( '/admin/wizard/sphinx_config.phtml' );

		$result = ManticoreSearch::$plugin->sphinxQL->updateConfig( $config );
		exit;
	}

	function config_action() {
		if ( ! empty( $_POST['skip_wizard_config'] ) ) {
			$this->view->assign( 'success_message', 'Step was skipped.' );

			return $this->_next_action( 'config' );
		}
		if ( ! empty( $_POST['config_process'] ) ) {
			$this->_next_action( 'indexing' );
		}
		$this->view->assign( 'config_content', $this->generate_config_file_content() );
		$this->view->assign( 'sphinx_conf', $this->config->get_option( 'sphinx_conf' ) );
		$this->view->render( '/admin/wizard/sphinx_config.phtml' );
		exit;
	}

	function finish_action() {
		if ( ! ManticoreSearch::$plugin->sphinxQL->is_active() ) {
			$res = ManticoreSearch::$plugin->service->start();

			$options = [ 'wizard_done' => 'true', 'configured' => 'false' ];
			if ( $res === true ) {
				$options['configured'] = 'true';
			} else {
				$this->view->assign( 'error_message', $res['err'] );
			}
		} else {
			$options['configured'] = 'true';
			$options['wizard_done'] = 'true';
		}

		$this->config->update_admin_options( $options );
		$this->view->render( '/admin/wizard/sphinx_finish.phtml' );
		exit;
	}

	function indexing_action() {
		$this->view->assign( 'devOptions', $this->config->get_admin_options() );
		if ( ! empty( $_POST['skip_wizard_indexsation'] ) ) {
			$this->view->assign( 'success_message', 'Step was skipped.' );

			return $this->_next_action( 'finish' );
		}

		if ( ! empty( $this->config->admin_options['secure_key'] ) && $this->config->admin_options['secure_key'] != 'false' ) {
			$this->view->assign( 'entered_key', 'true' );
		}
		$this->view->assign( 'indexing_data', ManticoreSearch::$plugin->backend->get_indexing_count() );
		$this->view->assign( 'indexsation_done', false );
		$this->view->render( 'admin/wizard/sphinx_indexsation.phtml' );
		exit;
	}

	function detect_program( $progname ) {
		$progname = escapeshellcmd( $progname );
		$res      = exec( "whereis {$progname}" );
		if ( ! preg_match( "#{$progname}:\s?([\w/]+)\s?#", $res, $matches ) ) {
			return false;
		}

		return $matches[1];
	}

	/**
	 * @access private
	 */
	function _set_sphinx_connect() {
		if ( ! empty( $_POST['sphinx_host'] ) && ! empty( $_POST['sphinx_port'] ) ) {
			$options['sphinx_use_socket'] = 'false';
			$options['sphinx_host']       = $_POST['sphinx_host'];
			$options['sphinx_port']       = $_POST['sphinx_port'];
		}

		if ( ! empty( $_POST['sphinx_socket'] ) ) {
			$options['sphinx_use_socket'] = 'true';
			$options['sphinx_socket']     = $_POST['sphinx_socket'];

			$path_to_socket = explode( DIRECTORY_SEPARATOR, $options['sphinx_socket'] );
			array_pop( $path_to_socket );
			$path_to_socket = implode( DIRECTORY_SEPARATOR, $path_to_socket );
			if ( ! file_exists( $path_to_socket ) ) {
				if ( ! @mkdir( $path_to_socket, 0777, true ) ) {
					if ( ! is_writable( $path_to_socket ) ) {
						return false;
					}
				}
			}
		}

		$this->config->update_admin_options( $options );

		return true;
	}

	function _generate_config_file_name() {
		$options  = $this->config->get_admin_options();
		$filename = $options['sphinx_path'] . '/manticore.conf';
		file_put_contents( $filename, '' );
		$options['sphinx_conf'] = $filename;
		$this->config->update_admin_options( $options );

		return $filename;
	}

	/**
	 * @return string - config file content
	 */
	public function generate_config_file_content() {

		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
			$config_maker = new Manticore_Http_Config_Maker( $this->config );
		} else {
			$config_maker = new Manticore_Ql_Config_Maker( $this->config );
		}


		return $config_maker->get_config();
	}

	function _generate_autocomplete_config_content() {
		global $table_prefix;
		$options = $this->config->get_admin_options();

		$tags_names = get_tags();
		$tags       = [];
		if ( ! empty( $tags_names ) ) {
			foreach ( $tags_names as $tag ) {
				$tags[] = $tag->slug;
			}
		}

		if ( empty( $options['search_in_blogs'] ) ) {
			$options['search_in_blogs'] = [ get_current_blog_id() ];
		}

		if ( $options['manticore_use_http'] == 'true' ) {

			$connect = 'use_remote = 1' . PHP_EOL .
			           'api_host = "' . $this->config->admin_options['api_host'] . '"' . PHP_EOL .
			           'secure_token = "' . $this->config->admin_options['secure_key'] . '"' . PHP_EOL;
		} else {
			$connect = ( $options['sphinx_use_socket'] == 'true'
					? 'searchd_socket = "' . $options['sphinx_socket'] . '"'
					: 'searchd_host = "' . $options['sphinx_host'] . '"' . PHP_EOL .
					  'searchd_port = ' . $options['sphinx_port'] ) . PHP_EOL;
		}

		return ';<?die;?>' . PHP_EOL .
		       $connect .
		       'main_index = "' . Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX . get_current_blog_id() . '"' . PHP_EOL .
		       'autocomplete_index = "' . Manticore_Config_Maker::AUTOCOMPLETE_DISTRIBUTED_INDEX_PREFIX . get_current_blog_id() . '"' . PHP_EOL .
		       'search_in_blogs = "' . implode( ' |*| ', $options['search_in_blogs'] ) . '"' . PHP_EOL .
		       'blog_id = ' . get_current_blog_id() . PHP_EOL .
		       'tags_names = "' . implode( ' |*| ', $tags ) . '"' . PHP_EOL .
		       'taxonomy_names = "' . implode( ' |*| ', $options['taxonomy_indexing_fields'] ) . '"' . PHP_EOL .
		       'custom_fields_names = "' . implode( ' |*| ', $options['custom_fields_for_indexing'] ) . '"' . PHP_EOL .
		       'suggest_on = "edge"' . PHP_EOL .
		       'corrected_dic_size = 10000000' . PHP_EOL .
		       'corrected_str_mlen = 2' . PHP_EOL .
		       'corrected_levenshtein_limit = 20' . PHP_EOL .
		       'corrected_levenshtein_min = 5' . PHP_EOL .
		       'mysql_host = "' . DB_HOST . '"' . PHP_EOL .
		       'mysql_posts_table = "' . $table_prefix . 'posts' . '"' . PHP_EOL .
		       'mysql_user = "' . DB_USER . '"' . PHP_EOL .
		       'mysql_pass = "' . DB_PASSWORD . '"' . PHP_EOL .
		       'mysql_db = "' . DB_NAME . '"';

	}


	/**
	 * @param $filename
	 * @param $content
	 *
	 * @return bool
	 */
	public function save_config( $filename, $content ) {
		if ( ! is_writable( $filename ) ) {
			return false;
		}
		file_put_contents( $filename, $content );

		ManticoreSearch::clear_autocomplete_config();

		return true;
	}

	/**
	 * @access private
	 * @return void
	 */
	private function _setup_sphinx_path() {
		$options['sphinx_path'] = $_POST['sphinx_path'];
		$this->config->update_admin_options( $options );
	}


	/**
	 * @param $searchd
	 */
	private function _set_sphinx_detected( $searchd ) {
		$options['sphinx_searchd'] = $searchd;
		$this->config->update_admin_options( $options );
	}

	/**
	 * @return array|bool
	 */
	function automatic_wizard() {

		$options = [ 'wizard_done' => 'false', 'configured' => 'false', 'activation_error_message' => '' ];
		$this->config->update_admin_options( $options );

		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {

			if ( ! empty( $this->config->admin_options['secure_key'] ) && $this->config->admin_options['secure_key'] !== 'false' ) {

				$config = $this->generate_config_file_content();
				ManticoreSearch::$plugin->sphinxQL->updateConfig( $config );

				$options = [ 'wizard_done' => 'true', 'configured' => 'true', 'activation_error_message' => '' ];
				$this->config->update_admin_options( $options );
			}

		} else {
			$_POST['sphinx_socket'] = $this->config->admin_options['sphinx_socket'];

			if ( ! $this->_set_sphinx_connect() ) {
				return [ 'error' => 'Uploads dir isn\'t writable' ];
			}


			$detect_installed_searchd = $this->config->get_option( 'sphinx_searchd' );
			if ( file_exists( $detect_installed_searchd ) ) {

				$this->_set_sphinx_detected( $detect_installed_searchd );
			} else {

				$sphinxInstall = new Manticore_Install( $this->config );
				$path          = $sphinxInstall->download_manticore( SPHINXSEARCH_SPHINX_INSTALL_DIR . DIRECTORY_SEPARATOR );

				if ( $path ) {
					$this->_set_sphinx_detected( $path['searchd_path'] );
				} else {
					return [ 'error' => 'An error occurred while downloading the binaries' ];
				}
			}

			$_POST['sphinx_path'] = SPHINXSEARCH_SPHINX_INSTALL_DIR;
			$error_message        = $this->checkFolders( $_POST['sphinx_path'] );
			if ( ! empty( $error_message ) ) {
				return [ 'error' => $error_message ];
			}

			$serviceStart = ManticoreSearch::$plugin->service->start();
			if ( ! empty( $serviceStart['err'] ) ) {
				return [ 'error' => $serviceStart['err'] ];
			}

			$options = [ 'wizard_done' => 'true', 'configured' => 'true', 'activation_error_message' => '' ];
			$this->config->update_admin_options( $options );
		}

		$autocomplete_config_name = $this->config->get_option( 'autocomplete_conf' );
		if ( ! empty( $autocomplete_config_name ) ) {

			$autocomplete_config_content = $this->_generate_autocomplete_config_content();
			$configFolder                = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autocomplete' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
			$res                         = $this->save_config( $configFolder . $autocomplete_config_name,
				$autocomplete_config_content );

			if ( false == $res ) {
				$options['is_autocomplete_configured'] = 'false';
			} else {
				$options['is_autocomplete_configured'] = 'true';
			}

			$this->config->update_admin_options( $options );
		}


		return true;
	}

	/**
	 * @access private
	 */
	function _next_action( $prevAction ) {


		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
			switch ( $prevAction ) {
				case 'start':
					if ( empty( $this->config->admin_options['secure_key'] ) || $this->config->admin_options['secure_key'] == 'false' ) {
						return $this->add_secure_key_action();
					}

					return $this->select_connection_type_action();
					break;
				case 'config_http_action':
					return $this->config_http_action();
					break;
				case 'configure_autocomplete':
					return $this->configure_autocomplete_action();
					break;
				case 'indexing':
					return $this->indexing_action();
					break;
				case 'config':
					return $this->indexing_action();
					break;
				case 'finish':
					$this->finish_action();

					return true;
					break;
				default:
					return $this->start_action();
					break;
			}
		} else {
			switch ( $prevAction ) {
				case 'start':
					if ( empty( $this->config->admin_options['secure_key'] ) || $this->config->admin_options['secure_key'] == 'false' ) {
						return $this->add_secure_key_action();
					}

					return $this->select_connection_type_action();
					break;
				case 'connection_port':
					return $this->connection_port_action();
					break;
				case 'connection_socket':
					return $this->connection_socket_action();
					break;
				case 'connection':
					return $this->detection_action();
					break;
				case 'install':
				case 'folder':
					return $this->config_action();
					break;
				case 'configure_autocomplete':
					return $this->configure_autocomplete_action();
					break;
				case 'indexing':
					return $this->indexing_action();
					break;
				case 'config':
					return $this->indexing_action();
					break;
				case 'detection':
					return $this->folder_action();
					break;
				case 'finish':
					$this->finish_action();

					return true;
					break;
				default:
					return $this->start_action();
					break;
			}
		}
	}

}
