<?php


class ManticoreHttpApi extends ManticoreConnector implements SplObserver {

	const MAX_REQUEST_SIZE = 524288;
	const CURL_TIMEOUT = 30;
	private $apiHost = '';
	public $execution_time;


	public function __construct( Manticore_Config $config ) {
		$this->config  = $config;
		$this->apiHost = $this->config->admin_options['api_host'];
	}


	public function updateConfig( $content ) {

		$args = $this->prepareRequestBody( [ 'config' => $content ] );

		return $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/json/config/',
			$args );
	}


	/**
	 * @param WP_Error|array $response
	 *
	 * @return string|null
	 * @throws Exception
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
					if ( ! empty( $content['attrs'] ) ) {
						$content['attrs'] = array_flip( $content['attrs'] );
					}

					if ( ! empty( $content['hits'] ) ) {
						$result = $content['hits']['hits'];
					} else {
						$result = $content;
					}
				}
			}

		} else {
			$this->errors[] = 'Error #' . $response_code . ' ';
			if ( isset( $response->errors->http_request_failed ) ) {
				return $this->execute();
			}

            if ( is_wp_error( $response ) ) {
                throw new Exception($response->get_error_message());
            }else{
                throw new Exception($response['body']);
            }
		}

		return $result;
	}

	public function update( SplSubject $subject ) {
		$this->config = $subject;
	}

	public function insert( array $insertArray, $escapeFields = [] ) {

		$this->type = self::TYPE_INSERT;

		$insert = [];

		if ( empty( $this->index ) ) {
			throw new Exception( 'index can\'t be empty!!!' );
		}

		foreach ( $insertArray as $k => $result ) {

			foreach ( $result as $field => $value ) {

				if ( in_array( $field, $escapeFields ) ) {
					$result[ $field ] = $this->escape_string( $result[ $field ] );
				}
			}

			if ( isset( $result['ID'] ) ) {
				$id = $result['ID'];
				unset( $result['ID'] );
			} else {
				$id = $result['id'];
				unset( $result['id'] );
			}

			$singleQuery     = [ 'replace' => [ 'index' => $this->index, 'id' => intval( $id ), 'doc' => $result ] ];
			$singleQueryJson = json_encode( $singleQuery );

			$insert[] = $singleQueryJson;
		}

		if ( ! empty( $insert ) ) {

			$insert      = $this->chunkExplode( $insert );
			$this->query = $insert;
		}

		return $this;
	}

	public function delete( $id, $field = 'id' ) {
		// TODO: Implement delete() method.
	}

	public function query( $query ) {

		throw new Exception( 'Query method are not allowed in Manticore http api' );

		return $this;
	}

	public function execute() {

		$this->results = [];
		if ( ! empty( $this->error ) ) {
			$this->status  = 'error';
			$this->message = $this->error;

			return $this;
		}
		if ( empty( $this->index ) && $this->type != self::TYPE_QUERY ) {
			$this->status  = 'error';
			$this->message = 'Index is undefined';

			return $this;
		}


		switch ( $this->type ) {
			case self::TYPE_SELECT:


				$appendSelect = ', WEIGHT() AS weight';
				if ( ! empty( $this->append_select ) ) {
					$appendSelect .= ', ' . implode( ', ', $this->append_select );
				}
				if ( isset( $this->sort['date_relevance'] ) ) {
					$appendSelect .= ', INTERVAL(date_added, NOW()-90*86400, NOW()-30*86400, ' .
					                 'NOW()-7*86400, NOW()-86400, NOW()-3600) AS date_relevance';
				}

				if ( ! empty( $this->sort ) && in_array( 'count', array_keys( $this->sort ) ) ) {
					$appendSelect .= ', count(*) AS count';
				}
				$this->query = 'SELECT *' . $appendSelect . ' FROM ' . $this->index . $this->get_where_clause() . ' ' . $this->get_group_by() . $this->get_limits();

				$this->results = $this->handle_server_response( $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/sql/?query=' . urlencode( $this->query ),
					[
						'timeout'         => self::CURL_TIMEOUT,
						//'sslcertificates' => $this->config->admin_options['cert_path']
					] ) );

				break;

			case self::TYPE_DELETE:

				$this->results = $this->handle_server_response( $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/json/delete/',
					$this->prepareRequestBody( $this->query ) ) );
				break;

			case self::TYPE_INSERT:
			case self::TYPE_UPDATE:

				foreach ( $this->query as $item ) {
					$args = [
						'headers'         => [
							'Content-Type' => 'application/x-ndjson',
						],
						'body'            => $item,
						'timeout'         => self::CURL_TIMEOUT,
						//'sslcertificates' => $this->config->admin_options['cert_path']
					];


					$responseResults = $this->handle_server_response( $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/json/bulk/',
						$args ) );

					if ( isset( $responseResults['errors'] ) ) {
						$this->results['errors'] = $responseResults['errors'];
					}
					if ( ! empty( $responseResults['items'] ) ) {
						foreach ( $responseResults['items'] as $items ) {
							$this->results['items'][] = $items;
						}
					}
				}
				break;

		}

		$this->status = 'success';

		$this->clear();

		return $this;
	}

	public function get_results() {

		$results  = [];
		$affected = 0;

		if ( ! empty( $this->results['items'] ) ) {
			foreach ( $this->results['items'] as $k => $itemType ) {
				if ( isset( $itemType['replace'] ) ) {
					if ( $itemType['replace']['result'] == "updated" ) {
						$affected ++;
					}
				}
			}
		}


		return [
			'status'   => $this->status,
			'results'  => $results,
			'affected' => ! empty( $affected ) ? $affected : false
		];

	}

	public function get_all() {

		$results = [];
		if ( ! empty( $this->results ) ) {

			foreach ( $this->results as $resultKey => $match ) {
				$match['_source']['id'] = $match['_id'];
				$results[]          = $match['_source'];
			}
		}

		return ! empty( $results ) ? $results : false;

	}

	/**
	 * Fetching query result
	 *
	 * @param $field string
	 *
	 * @return bool|mixed
	 */
	function get_column( $field ) {
		if ( empty( $this->results ) ) {
			return false;
		}

		if ( isset( $this->results[0]['_source'][ $field ] ) ) {
			return $this->results[0]['_source'][ $field ];
		}

		return false;
	}

	public function deleteWhere( $index, array $data ) {

		$this->index = $index;
		$this->type  = self::TYPE_DELETE;

		$field   = $data[0];
		$operand = '=';
		if ( in_array( $data[1], [ '<', '>', '', 'IN' ] ) ) {
			$operand = $data[1];
			$value   = $data[2];
		} else {
			$value = $data[1];
		}

		$this->query = [
			'index' => $this->index,
			'doc'   => [
				$field => [ $operand => $value ]
			]
		];

		return $this;
	}

	private function chunkExplode( $explodedQuery ) {
		$chunkedLength = 0;

		$i       = 0;
		$chunked = [];
		foreach ( $explodedQuery as $query ) {
			$chunkedLength += mb_strlen( $query, '8bit' );
			if ( $chunkedLength > self::MAX_REQUEST_SIZE ) {
				$i ++;
				$chunkedLength = mb_strlen( $query, '8bit' );
			}

			$chunked[ $i ][] = $query;

		}
		$result = [];

		foreach ( $chunked as $batch ) {
			$result[] = implode( "\n", $batch );
		}

		return $result;
	}

	public function call_snippets( $data, $index, $query, $options ) {

		$args = $this->prepareRequestBody( [
			'data'    => $data,
			'index'   => $index,
			'query'   => $query,
			'options' => $options
		] );

		$responseResults = $this->handle_server_response( $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/json/snippets/',
			$args ) );

		return $responseResults['results'];
	}

	public function flush( $index ) {
		$args = $this->prepareRequestBody( [ 'index' => $index ] );

		$responseResults = $this->handle_server_response( $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/json/flush/',
			$args ) );

		return $responseResults['results'];
	}

	public function optimize( $index ) {
		$args = $this->prepareRequestBody( [ 'index' => $index ] );

		$responseResults = $this->handle_server_response( $this->wp_remote_post( $this->apiHost . '/' . $this->config->admin_options['secure_key'] . '/json/optimize/',
			$args ) );

		return $responseResults['results'];
	}


	/**
	 * @param $index
	 * @param array $updateData ['key1' => 'value1', 'key2' => 'value2']
	 * @param array $whereData ['id', 'IN', '(1,2,3)']
	 * @param array $escapeFields
	 *
	 * @return $this
	 */
	public function updateWhere( $index, array $updateData, array $whereData, $escapeFields = [] ) {
		$this->index = $index;
		$this->type  = self::TYPE_UPDATE;


		foreach ( $updateData as $k => $v ) {
			if ( in_array( $k, $escapeFields ) ) {
				$updateData[ $k ] = $this->escape_string( $v );
			}
		}

		$field   = $whereData[0];
		$operand = '=';
		if ( in_array( $whereData[1], [ '<', '>', '', 'IN' ] ) ) {
			$operand = $whereData[1];
			$value   = $whereData[2];
		} else {
			$value = $whereData[1];
		}


		$this->query = [
			'index' => $this->index,
			'doc'   => [
				$field => $value
			],

			// WHERE CONDITION
			'query' => [
				$field => [ $operand => $value ]
			]
		];

		return $this;
	}


	private function prepareRequestBody( $parameters ) {
		return [
			'headers'         => [
				'Content-Type' => 'application/json',
			],
			'timeout'         => self::CURL_TIMEOUT,
			'body'            => json_encode( $parameters ),
			//'sslcertificates' => $this->config->admin_options['cert_path']
		];
	}


	private function wp_remote_post($url, $args= []){

		$start = microtime( true );
		$result = wp_remote_post($url, $args);

		if ( empty( $this->execution_time[ $url ] ) ) {
			$this->execution_time[ $url ] = 0;
		}
		$this->execution_time[ $url ] += ( microtime( true ) - $start );

		return $result;
	}

}