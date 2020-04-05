<?php

class ManticoreQlConnector implements ManticoreConnector {

	public $execution_time = [];
	public $execution_time_inner = [];
	private $config;
	private $connection;

	public function __construct( $config ) {
		$this->config = $config;

		mysqli_report( MYSQLI_REPORT_STRICT );
		if ( ! empty( $config->searchd_socket ) ) {
			$this->connection = new mysqli( '', '', '', '', 0, $config->searchd_socket );
		} else {
			$this->connection = new mysqli( $config->searchd_host . ':' . $config->searchd_port, '', '', '' );
		}
	}

	function keywords( $index, $text, $options = [] ) {

		$sql = "CALL KEYWORDS('" . $text . "', '" . $index . "' " . $this->getAppendOptions( $options ) . ")";

		return $this->fetch( $this->query( $sql ) );
	}

	function qsuggest( $index, $word, $options ) {
		$sql = "CALL QSUGGEST('" . $word . "', '" . $index . "' " . $this->getAppendOptions( $options ) . ")";

		return $this->fetch( $this->query( $sql ) );
	}

	function select( $data ) {

		$whereAdded = false;
		$index      = $data['index'];
		$query      = "SELECT * FROM $index";


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

			$query      = $query . " WHERE $field $operand $value";
			$whereAdded = true;
		}

		if ( isset( $data['match_phrase'] ) ) {
			$query = $query . " " . ( $whereAdded ? 'and' : 'where' ) . " match('\"" . $data['match_phrase'] . "\"')";

		} elseif ( isset( $data['match'] ) ) {
			$query = $query . " " . ( $whereAdded ? 'and' : 'where' ) . " match('" . $data['match'] . "')";
		}


		if ( isset( $data['limit'] ) ) {
			$query = $query . " limit = " . $data['limit'];
		}

		return $this->fetch( $this->query( $query ) );
	}

	function snippets( $index, $query, $data, $options ) {
		$sql = "CALL SNIPPETS('" . $data . "', '" . $index . "', '" . $query . "' " . $this->getAppendOptions( $options ) . ")";

		return $this->fetch( $this->query( $sql ) );
	}

	private function query( $sql ) {
		return $this->connection->query( $sql );
	}

	private function fetch( $stmt ) {
		$results = false;
		if ( ! empty( $stmt ) ) {
			while ( $stmt_return = $stmt->fetch_assoc() ) {
				$results[] = $stmt_return;
			}
		}

		return $results;
	}

	private function getAppendOptions( $options ) {
		$append = '';

		if ( ! empty( $options ) ) {
			$append = ', ' . implode( ', ', $options );
		}

		return $append;
	}
}