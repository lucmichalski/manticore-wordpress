<?php

class ManticoreHttpConnector implements ManticoreConnector {

	public $execution_time = [];
	public $execution_time_inner = [];
	private $apiHost = '';
	private $secureToken = '';
	private $config;
	private $cacertPath;

	function __construct( $config ) {

		$this->config      = $config;
		$this->apiHost     = $config->api_host;

		$this->apiHost  ='http://wpcloud2.manticoresearch.com';
		$this->secureToken = $config->secure_token;
		$this->cacertPath  = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
		                     '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'ca-cert.pem';
	}

	function keywords( $index, $text, $options = [] ) {
		$parameters = [ 'index' => $index, 'text' => $text, 'options' => $options ];

		return $this->request( $this->apiHost . '/' . $this->secureToken . '/json/keywords/',
			json_encode( $parameters ) );
	}

	function qsuggest( $index, $word, $options ) {
		$parameters = [ 'index' => $index, 'text' => $word, 'options' => $options ];

		return $this->request( $this->apiHost . '/' . $this->secureToken . '/json/qsuggest/',
			json_encode( $parameters ) );
	}

	function snippets( $index, $query, $data, $options ) {
		$parameters = [ 'index' => $index, 'query' => $query, 'data' => $data, 'options' => $options ];

		return $this->request( $this->apiHost . '/' . $this->secureToken . '/json/snippets/',
			json_encode( $parameters ) );
	}

	/**
	 * @param $data array contains of ['index'=>'myIndex', 'match'=>'matchQuery', 'where' => ['id','>',0]]
	 *
	 * @return array
	 */
	function select( $data ) {
		$index = $data['index'];


		$search = [
			'index' => $index,
		];

		if ( isset( $data['match_phrase'] ) ) {
			$search['query'] = [
				'match_phrase' => [
					"_all" => $data['match_phrase']
				]
			];
		}

		if ( isset( $data['match'] ) ) {
			$search['query'] = [
				'match' => [
					"*" => $data['match']
				]
			];
		}


		if ( isset( $data['where'] ) ) {
			$where = $data['where'];

			$field   = $where[0];
			$operand = '=';
			if ( $where[1] === '<' || $where[1] === '>' || $where[1] === 'IN' ) {
				$operand = $where[1];
				$value   = $where[2];
			} else {
				$value = $where[1];
			}

			$search['query'][ $field ] = [ $operand => $value ];
		}

		if ( isset( $data['limit'] ) ) {
			$search['limit'] = $data['limit'];
		}

		return $this->request( $this->apiHost . '/' . $this->secureToken . '/json/search/',
			json_encode( $search ) );
	}

	function request( $url, $post, $headers = 'Content-Type: text/json' ) {

		$start = microtime( true );


		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, [ $headers ] );

/*
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 1 );
		curl_setopt( $curl, CURLOPT_CAINFO, $this->cacertPath );
		curl_setopt( $curl, CURLOPT_CAPATH, $this->cacertPath );
*/
		$server_output = curl_exec( $curl );
		$status        = curl_getinfo( $curl, CURLINFO_RESPONSE_CODE );

		curl_close( $curl );

		if ( $status != 200 ) {
			throw new Exception( 'Server returned error. Status: ' . $status . ' ' . $server_output );
		}


		if ( empty( $this->execution_time[ $url ]) ) {
			$attempt = 0;
			$this->execution_time[ $url ]['summary_time'] = 0;
		}else{
			$attempt = (count($this->execution_time[ $url ])+1);
		}

		$tme = ( microtime( true ) - $start );

		$this->execution_time[ $url ][$attempt] = $tme;
		$this->execution_time[ $url ]['summary_time'] += $tme;


		if ( ! empty( $server_output ) ) {
			$results = json_decode( $server_output, true );

			if (!empty($results['time'])){

				if ( empty( $this->execution_time_inner[ $url ]) ) {
					$attempt = 0;
					$this->execution_time_inner[ $url ]['summary_time'] = 0;
				}else{
					$attempt = count($this->execution_time_inner[ $url ])+1;
				}

				$this->execution_time_inner[ $url ][$attempt] = $results['time'];
				$this->execution_time_inner[ $url ]['summary_time'] += $results['time'];
			}


			if ( ! empty( $results['hits']['hits'] ) ) {
				$result = [];
				foreach ( $results['hits']['hits'] as $hit ) {
					$result[] = $hit['_source'];
				}

				return $result;
			}

			if ( isset( $results['results'] ) ) {
				return $results['results'];
			}
		}

		return false;


	}
}