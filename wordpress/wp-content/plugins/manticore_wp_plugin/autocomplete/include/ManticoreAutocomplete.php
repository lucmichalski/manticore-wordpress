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
class ManticoreAutocomplete {

	const EXPANSION_LIMIT = 5;
	protected $original_query = '';
	protected $config = null;
	protected $sphinxQL = null;
	protected $request = null;
	protected $index;
	protected $sphinx_error = '';
	protected $mysql_error = '';

	protected $mysql = null;

	function __construct( $config ) {
		$this->config = $config;
		$this->index  = $config->table;

		try {
			mysqli_report( MYSQLI_REPORT_STRICT );
			if ( ! empty( $config->searchd_socket ) ) {
				$this->sphinxQL = new mysqli( '', '', '', '', 0, $config->searchd_socket );
			} else {
				$this->sphinxQL = new mysqli( $config->searchd_host . ':' . $config->searchd_port, '', '', '' );
			}

		} catch ( mysqli_sql_exception $exception ) {
			$this->sphinx_error = $exception->getMessage();
		}


		try {
			mysqli_report( MYSQLI_REPORT_STRICT );

			$this->mysql = new mysqli( $this->config->mysql_host, $this->config->mysql_user, $this->config->mysql_pass, $this->config->mysql_db );

		} catch ( mysqli_sql_exception $exception ) {
			$this->mysql_error = $exception->getMessage();
		}
	}


	public function setRequest( $request ) {
		$this->request = $request;
	}

	public function request( $request ) {

		if ( ! empty( $this->sphinx_error ) || empty( $request["q"] ) || ! empty( $this->sphinx_error ) ) {
			return "[]";
		}
		$request['q']         = trim( $request['q'] );
		$this->request        = $request;
		$this->original_query = $request['q'];

		list( $corrected, $query, $original_query ) = $this->correct_query();
		$ret = $this->get_suggests( $query, $original_query );

		return ( json_encode( [
			'result'  => $ret,
			'correct' => $corrected
		] ) );
	}

	protected function correct_query() {
		$query                = $this->escapeString( strip_tags( $this->request["q"] ) );
		$input_query          = preg_split( "/\s+/", $this->request["q"] );
		$tokenized_query      = $this->getTokenizedText( $query, false );
		$corrected_query      = array();
		$query_to_correct     = $tokenized_query;
		$highligh_input_query = true;


		foreach ( $query_to_correct as $i => $item ) {
			$c_query = $this->getCorrectedKeyword( $item );
			if ( ! preg_match( "%" . preg_quote( $item ) . "%", $c_query ) ) {
				$query_to_correct[ $i ] = $c_query;
			}
			$tt_query = $highligh_input_query ? $input_query[ $i ] : $item;
			if ( $item != $query_to_correct[ $i ] ) {
				$corrected_query[] = "<span class='tt-corrected'>{$tt_query}</span>";
			} else {
				$corrected_query[] = "<span>{$tt_query}</span>";
			}
		}

		$original_query = implode( " ", $tokenized_query );
		$query          = implode( " ", $query_to_correct );

		return [
			[
				"result"    => implode( " ", $corrected_query ),
				"corrected" => $query !== $original_query ? 1 : 0,
			],
			$query,
			$original_query
		];
	}

	protected function get_suggests( $query, $original_query ) {
		$suggestions        = $this->suggest( $query );
		$ret                = [];
		$unique_suggestions = array();
		if ( ! empty( $suggestions ) ) {
			foreach ( $suggestions as $i => $term ) {
				$tokenized_term = $this->getTokenizedText( $term );
				if ( ! empty( $unique_suggestions[ $tokenized_term ] ) || $tokenized_term === $original_query ) {
					continue;
				}
				$unique_suggestions[ $tokenized_term ] = true;

				$h_terms = preg_split( "%(" . preg_quote( $original_query ) . ")%", $tokenized_term );

				if ( ! empty( $h_terms ) ) {
					$tokenized_term = "";
					$j              = 0;
					do {
						$j ++;
						$t         = array_shift( $h_terms );
						$t_trimmed = trim( $t );
						if ( ! empty( $t_trimmed ) ) {
							$tokenized_term .= "<span class='tt-suggestion-rest'>{$t}</span>";
						}
						if ( ! empty( $h_terms ) ) {
							if ( $j == 1 && $this->config->suggest_on == "edge" || $this->config->suggest_on == "any" ) {
								$tokenized_term .= "<span class='tt-suggestion-match'>{$original_query}</span>";
							} else {
								$tokenized_term .= "<span class='tt-suggestion-rest'>{$original_query}</span>";
							}
						}
					} while ( ! empty( $h_terms ) );
				}

				$ret[] = $tokenized_term;
			}
		}

		return $ret;
	}

	public function getTokenizedText( $text, $plain = true ) {
		$get_tokenized_text_query  = "call keywords('{$text}', '" . $this->index . "')";
		$get_tokenized_text_result = $this->sphinxQL->query( $get_tokenized_text_query );
		if ( ! $get_tokenized_text_result ) {
			return false;
		}
		$tokenized_text = array();
		/*
			$tokenized_map need to overwrite existed tokenized  keyword, could appear if use blended chars

			mysql>  call keywords('*холод,*чувства', 'auto_correct');show meta;
			+------+----------------------------+----------------------------+
			| qpos | tokenized                  | normalized                 |
			+------+----------------------------+----------------------------+
			| 1    | холод,*чувства             | холод,*чувства             |
			| 1    | холод                      | холод                      |
			| 2    | чувства                    | чувства                    |
			+------+----------------------------+----------------------------+
		*/
		$tokenized_map = array();
		$i_token       = 0;
		while ( $t_keyword = $get_tokenized_text_result->fetch_assoc() ) {

			if ( isset( $t_keyword["qpos"] ) && ! isset( $tokenized_map[ $t_keyword["qpos"] ] ) ) {
				$tokenized_map[ $t_keyword["qpos"] ] = $i_token;
			}
			if ( isset( $t_keyword["qpos"] ) ) {
				$tokenized_text[ $tokenized_map[ $t_keyword["qpos"] ] ] = $t_keyword["tokenized"];
			} else {
				$tokenized_text[ $i_token ] = $t_keyword["tokenized"];
			}
			$i_token ++;
		}
		$get_tokenized_text_result->free();
		if ( $plain ) {
			return implode( " ", $tokenized_text );
		} else {
			return $tokenized_text;
		}
	}

	public function getCorrectedKeyword( $keyword, $use_edgegrams = true, $skip_the_same_keyword = false ) {
		$corrected_keyword = $keyword;
		if ( strlen( $keyword ) < $this->config->corrected_str_mlen ) {
			return $corrected_keyword;
		}

		$get_corrected_keyword_query = "CALL QSUGGEST('" . $keyword . "','" . $this->index . "', 1 as non_char)";

		$get_corrected_keyword_result = $this->sphinxQL->query( $get_corrected_keyword_query );
		if ( ! $get_corrected_keyword_result ) {
			return false;
		}
		$l_keyword_lev = null;
		$l_keyword     = null;
		while ( $c_keyword = $get_corrected_keyword_result->fetch_assoc() ) {
			$k = $c_keyword['suggest'];
			$l = levenshtein( $keyword, $k );

			if ( ( is_null( $l_keyword_lev ) || ! is_null( $l_keyword_lev ) && $l_keyword_lev > $l ) && ( ! $skip_the_same_keyword || $k != $keyword ) ) {
				$l_keyword     = $k;
				$l_keyword_lev = $l;
			}
		}

		if ( ! is_null( $l_keyword_lev ) && $l_keyword_lev < $this->config->corrected_levenshtein_min ) {
			$corrected_keyword = $l_keyword;
		} elseif ( $use_edgegrams ) {
			$corrected_keyword = $this->getCorrectedKeyword( $keyword, $use_edgegrams = false );
		}
		$get_corrected_keyword_result->free();

		if ( ! empty( $this->field_name ) ) {
			$corrected_keyword = str_replace( $this->field_name, '', $corrected_keyword );
		}

		return $corrected_keyword;
	}

	public static function prepareText( $text ) {
		$encoding = mb_detect_encoding( $text, 'auto' );
		$encoding = $encoding ?: 'ISO-8859-1';
		if ( $encoding == 'UTF-8' ) {
			if ( ! mb_check_encoding( $text, $encoding ) ) {
				$text = utf8_encode( $text );
			} else {
				$text = iconv( $encoding, 'UTF-8', $text );
			}
		}
		$ret = @htmlspecialchars( $text, ENT_QUOTES | 'ENT_XML1' );

		return $ret;
	}

	public function escapeString( $str ) {
		return $this->sphinxQL->escape_string( $str );
	}

	public function suggest( $query ) {

		$get_suggestions_query = "CALL KEYWORDS ( '" . $query . "*', '" . $this->index . "', 'docs' as sort_mode, 1 as stats)";

		$get_suggestions_query_result = $this->sphinxQL->query( $get_suggestions_query );


		if ( $get_suggestions_query_result && $get_suggestions_query_result->num_rows == 1 ) {
			$suggestion = $get_suggestions_query_result->fetch_assoc();
			if ( $suggestion['docs'] > 0 ) {
				$get_suggestions_query_result = $this->sphinxQL->query( $get_suggestions_query );
			} else {
				$get_suggestions_query_result = false;
			}
		}

		if ( ! $get_suggestions_query_result ) {
			$get_suggestions_query = "CALL QSUGGEST('" . $query . "','" . $this->index . "', 1 as non_char)";

			$get_suggestions_query_result = $this->sphinxQL->query( $get_suggestions_query );
		}


		if ( ! $get_suggestions_query_result ) {
			return false;
		}
		$suggestions      = [];
		$weak_suggestions = [];

		$all_phrase       = explode( ' ', $query );
		$all_phrase_count = count( $all_phrase );
		array_pop( $all_phrase );
		if ( ! empty( $all_phrase ) ) {
			$all_weak_phrase = implode( ' NEAR/3 ', $all_phrase ) . ' NEAR/3 ';
			$all_phrase      = implode( ' ', $all_phrase ) . ' ';
		} else {
			$all_phrase      = '';
			$all_weak_phrase = '';
		}

		$suggest_count = 0;
		while ( $suggestion = $get_suggestions_query_result->fetch_assoc() ) {

			$suggest_count ++;

			/* TODO delete when iss460 are fixed */

			if ( $suggest_count >= ( self::EXPANSION_LIMIT + $all_phrase_count ) ) {
				break;
			}

			if ( isset( $suggestion['normalized'] ) ) {

				if ( $all_phrase_count > $suggest_count ) {
					continue;
				}

				$suggestion = $suggestion['normalized'];

				/* TODO delete when iss460 are fixed */

				if ( $suggestion[0] < 0x20 ) {
					$suggestion = substr( $suggestion, 1 );
				}

				if ( substr( $suggestion, - 1 ) == '*' ) {
					continue;
				}

			} else {
				$suggestion = $suggestion['suggest'];
			}

			if ( strpos( $suggestion, "'" ) ) {
				$suggestion = str_replace( "'", "\'", $suggestion );
			}
			$suggestions[]      = $all_phrase . $suggestion;
			$weak_suggestions[] = [ 'suggest' => $all_weak_phrase . $suggestion ];

		}
		$get_suggestions_query_result->free();

		/* If typed only one word and no need to check logic */

		if ( empty( $all_phrase ) ) {
			return $suggestions;
		}

		if ( ! empty( $_REQUEST['search_in'] ) ) {
			$_REQUEST['search_in'] = explode( ',', $_REQUEST['search_in'] );
			foreach ( $_REQUEST['search_in'] as $k => $item ) {
				$item = intval( $item );
				if ( ! empty( $item ) ) {
					$search_in[] = $item;
				}
			}

			if ( ! empty( $search_in ) && is_array( $search_in ) ) {
				$search_in = ' AND blog_id in (' . implode( ',', $search_in ) . ')';
			}
		}

		$queries = [];
		foreach ( $suggestions as $suggestion ) {
			$queries[] = "SELECT * FROM " . $this->index . " WHERE match('\"" . $suggestion . "\"')" . $search_in;
		}
		$queries = implode( '; ', $queries );

		$i = 0;
		if ( $this->sphinxQL->multi_query( $queries ) ) {
			do {
				/* получаем первый результирующий набор */
				if ( $result = $this->sphinxQL->store_result() ) {
					if ( empty( $result->num_rows ) ) {
						unset( $suggestions[ $i ] );
					}
					$result->free();
				}
				$i ++;
			} while ( $this->sphinxQL->more_results() && $this->sphinxQL->next_result() );
		}

		if ( ! empty( $suggestions ) ) {
			return $suggestions;
		}


		/* If we don't find any exact match, try to find weak matches */

		if ( empty( $this->sphinx_error ) ) {

			$queries = [];
			foreach ( $weak_suggestions as $weak_suggestion ) {
				$queries[] = "SELECT * FROM " . $this->index . " WHERE match('" . $weak_suggestion['suggest'] . "')" . $search_in;
			}
			$queries = implode( '; ', $queries );

			$i = 0;
			if ( $this->sphinxQL->multi_query( $queries ) ) {
				do {
					/* получаем первый результирующий набор */
					if ( $result = $this->sphinxQL->store_result() ) {
						if ( empty( $result->num_rows ) ) {
							unset( $weak_suggestions[ $i ] );
						} else {
							$row                               = $result->fetch_assoc();
							$weak_suggestions[ $i ]['id']      = $row['id'];
							$weak_suggestions[ $i ]['post_id'] = $row['post_id'];
						}
						$result->free();
					}
					$i ++;
				} while ( $this->sphinxQL->more_results() && $this->sphinxQL->next_result() );
			}


			if ( ! empty( $weak_suggestions ) ) {
				$i = 0;
				foreach ( $weak_suggestions as $weak_suggestion ) {
					if ( ! empty( $weak_suggestion['id'] ) ) {
						$i ++;
						$post_id = intval( $weak_suggestion['post_id'] );
						$post    = $this->mysql->query( 'SELECT post_content FROM `' . $this->config->mysql_posts_table . '` WHERE ID = ' . $post_id )->fetch_assoc();
						if ( ! empty( $post['post_content'] ) ) {
							$suggestions[] = $this->get_match( addslashes( strip_tags( $post['post_content'] ) ), $weak_suggestion['suggest'] );
						}
					}
				}

			}
		}

		return $suggestions;
	}


	private function get_match( $content, $query ) {
		$first_word = explode( ' ', $query );
		$first_word = $first_word[0];

		$sql    = "CALL SNIPPETS('" . $content . "', '" . $this->index . "', '" . $query . "', " .
		          " 3 AS around, 500 AS limit, 1 as limit_passages, '' as chunk_separator, '' as before_match, '' as after_match)";
		$result = $this->sphinxQL->query( $sql )->fetch_assoc();
		if ( ! empty( $result['snippet'] ) ) {

			if ( preg_match( '#^.*?(' . $first_word . '.*$)#usi', $result['snippet'], $matches ) ) {
				return $matches[1];
			}
		}

		return '';
	}
}