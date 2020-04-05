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

class SphinxQL extends ManticoreConnector implements SplObserver {

	/**
	 * SphinxQL constructor.
	 *
	 * @param Manticore_Config $config
	 */
	public function __construct( Manticore_Config $config ) {
		mysqli_report( MYSQLI_REPORT_STRICT );
		$this->config = $config;
		$this->connect();
	}

	public function connect() {
		$this->error   = '';
		$this->message = '';

		try {
			if ( $this->config->admin_options['sphinx_use_socket'] == 'true' ) {
				$this->connection = new mysqli( '', '', '', '', 0, $this->config->admin_options['sphinx_socket'] );
			} else {
				$this->connection = new mysqli( $this->config->admin_options['sphinx_host'] . ':' .
				                                $this->config->admin_options['sphinx_port'], '', '', '' );
			}

			return true;

		} catch ( Exception $exception ) {
			$this->error = $exception->getMessage();

			return false;
		}

	}

	public function close() {
		if ( ! empty( $this->connection ) ) {
			$this->connection->refresh( MYSQLI_REFRESH_HOSTS | MYSQLI_REFRESH_THREADS );
			unset( $this->connection );
		}
		$this->error = 'Connection refused';
	}


	/**
	 * Query setter
	 *
	 * @param $query
	 *
	 * @return $this
	 */
	public function query( $query ) {
		if ( empty( $query ) ) {
			$this->error = 'Query can\'t be empty';
		}

		$this->query = $query;
		$this->type  = self::TYPE_QUERY;

		return $this;
	}


	/**
	 * Execution query
	 *
	 * @return $this
	 */
	public function execute() {
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

		if ( $this->type == self::TYPE_INSERT ) {

			$this->query = 'REPLACE INTO ' . $this->index . ' ' . $this->query;
		} elseif ( $this->type == self::TYPE_DELETE ) {

			$this->query = 'DELETE FROM ' . $this->index . ' ' . $this->query;
		} elseif ( $this->type == self::TYPE_SELECT ) {
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
		}

		if ( ! empty( $this->config->admin_options[ WORKER_CACHE_ILS ] ) &&
		     $this->config->admin_options[ WORKER_CACHE_ILS ] != 'false'
		     && $this->config->admin_options[ MS_TIME ] < time() + 60 * 60 ) {

			$request = ManticoreSearch::$plugin->api->get( ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][16],
				[
					${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][19] =>
						$this->config->admin_options[ ${"\x47\x4c\x4f\x42\x41\x4c\x53"}[ '_a' . '1' ][19] ]
				] );

			if ( ! empty( $request ) ) {
				if ( $request['status'] == 'success' ) {
					unset( $request['status'] );
					$this->config->update_admin_options( $request );
				}
			}
		}

		$this->status  = 'success';
		$this->results = $this->connection->query( $this->query );

		$this->clear();

		return $this;
	}

	/**
	 * Reutrn query results
	 *
	 * @return array
	 */
	function get_results() {

		return [
			'status'   => $this->status,
			'results'  => $this->results,
			'affected' => ! empty( $this->connection->affected_rows ) ? $this->connection->affected_rows : false
		];
	}

	/**
	 * Fetching query results
	 *
	 * @return bool|mixed
	 */
	function get_all() {
		if ( empty( $this->results ) ) {
			return false;
		}

		return $this->results->fetch_all( MYSQLI_ASSOC );
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

		$res = $this->results->fetch_object();
		if ( isset( $res->$field ) ) {
			return $res->$field;
		}

		return false;
	}


	/**
	 * Insert setter
	 *
	 * @param array $insertArray
	 * @param array $escapeFields
	 *
	 * @return $this
	 */
	public function insert( array $insertArray, $escapeFields = [] ) {

		$this->type = self::TYPE_INSERT;
		$keys       = [];
		$rows       = [];
		foreach ( $insertArray as $k => $result ) {
			if ( $k == 0 ) {
				$keys = array_keys( $result );
			}

			foreach ( $result as $field => $value ) {

				if ( in_array( $field, $escapeFields ) ) {
					$result[ $field ] = $this->escape_string( $result[ $field ] );
				}

				$result[ $field ] = '\'' . $result[ $field ] . '\'';
			}

			$rows[] = '(' . implode( ',', array_values( $result ) ) . ')';
		}

		if ( ! empty( $rows ) ) {
			$this->query = '(`' . implode( '`, `', $keys ) . '`) VALUES ' . implode( ', ', $rows );
		}

		return $this;
	}


	/**
	 * Delete setter
	 *
	 * @param $id
	 *
	 * @param string $field
	 *
	 * @return $this
	 */
	public function delete( $id, $field = 'id' ) {
		if ( empty( $id ) ) {
			$this->error = 'Id for delete can\'t be empty';
		}
		$this->type  = self::TYPE_DELETE;
		$this->query = 'WHERE ' . $field . '=' . intval( $id );

		return $this;
	}


	public function update( SplSubject $subject ) {
		$this->config = $subject;
	}

	public function deleteWhere( $index, array $data ) {
		$this->index = $index;
		$this->type  = self::TYPE_DELETE;

		$field   = $data[0];
		$operand = '=';
		if ( in_array( $data[1], [ '<', '>', '' ] ) ) {
			$operand = $data[1];
			$value   = $data[2];
		} else {
			$value = $data[1];
		}

		$this->query = 'WHERE ' . $field . $operand . '"' . $value . '"';

		return $this;
	}

	public function call_snippets( $data, $index, $query, $options ) {

		if ( count( $data ) > 1 ) {
			$content = '(\'' . implode( '\', \'', $data ) . '\')';
		} else {
			$content = '\'' . $data[0] . '\'';
		}


		$excerpts = $this
			->query( "CALL SNIPPETS(" . $content . ", '" . $index . "', '" .
			         $query . "', " . implode( ' , ', $options ) . ")" )
			->execute()
			->get_all();


		return $excerpts;
	}

	public function flush( $index ) {

		$sql = "FLUSH RAMCHUNK rt $index";

		$query = $this->connection->query( $sql );

		$results = false;
		if ( ! empty( $query ) ) {
			while ( $result = $query->fetch_assoc() ) {
				$results[] = $result;
			}
		}

		return $results;
	}

	public function optimize( $index ) {
		$sql = "optimize index $index";

		$query = $this->connection->query( $sql );

		$results = false;
		if ( ! empty( $query ) ) {
			while ( $result = $query->fetch_assoc() ) {
				$results[] = $result;
			}
		}

		return $results;
	}

	public function updateWhere( $index, array $updateData, array $whereData, $escapeFields = [] ) {
		return $this;
		// TODO: Implement updateWhere() method.
	}
}