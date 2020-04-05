<?php


class Manticore_Api implements SplObserver {

	const API_URL = 'https://wp.manticoresearch.com';

	private $config;
	private $errors = [];

	public function __construct( Manticore_Config $config ) {
		$this->config = $config;
	}

	public function update( SplSubject $subject ) {
		$this->config = $subject;
	}

	public function get( $section, $data, $headers = [] ) {

		$data['is_network'] = defined( 'MULTISITE' );
		$headers            = array_merge( $headers, [
			'accept'        => 'application/json',
			/* TODO delete on prod */
			'Authorization' => 'Basic ' . base64_encode( "webuser:1tn@M" )
		] );
		$args               = [
			'headers' => $headers,
			'body'    => $data,
			'timeout' => 5,
		];

		return $this->handle_server_response( wp_remote_post( self::API_URL . $section, $args ) );
	}


	/**
	 * @param WP_Error|array $response
	 *
	 * @return string|null
	 */
	function handle_server_response( $response ) {
		$response_code = wp_remote_retrieve_response_code( $response );
		$result        = '';
		if ( $response_code == 200 ) {
			if ( is_wp_error( $response ) ) {
				$this->errors[] = $response->get_error_message();
			} else {
				if ( isset( $response['body'] ) ) {
					$content = $response['body'];
					$content = json_decode( $content, true );

					if ( isset( $content[ MS_TIME ] ) ) {
						$this->config->update_admin_options( [ MS_TIME => $content[ MS_TIME ] ] );
					}

					$result = $content;
					if ( isset( $content['status'] ) && $content['status'] == 'error' ) {
						$this->errors[] = $content['message'];
						if ( ! empty( $content['error_code'] ) && $content['error_code'] == 102 ) {
							unset( $content['status'], $content['error_code'], $content['message'] );
							$this->config->update_admin_options( $content );
						}
					}
				}
			}
		} else {
			$this->errors[] = 'Нет интернета';
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function get_errors() {
		return implode( "\n", $this->errors );
	}


}