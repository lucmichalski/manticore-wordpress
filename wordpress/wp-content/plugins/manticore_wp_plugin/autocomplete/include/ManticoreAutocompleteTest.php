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
	const MAX_SUGGESTS = 8;
	protected $original_query = '';
	/** @var ManticoreAutocompleteCache */
	protected $cache = null;
	protected $config = null;
	/** @var ManticoreHttpConnector */
	protected $sphinxQL = null;
	protected $request = null;
	protected $sphinx_error = '';
	protected $mysql_error = '';


	protected $mysql = null;

	function __construct( $config, ManticoreAutocompleteCache $autocomplete_cache ) {
		$this->config = $config;

		$this->cache = $autocomplete_cache;

		try {
			if ( !empty($config->use_remote) ) {
				$this->connection = new ManticoreHttpConnector( $this->config );
			} else {
				$this->connection = new ManticoreQlConnector( $this->config );
			}

		} catch ( mysqli_sql_exception $exception ) {
			$this->sphinx_error = $exception->getMessage();
		}


		try {
			mysqli_report( MYSQLI_REPORT_STRICT );

			$this->mysql = new mysqli( $this->config->mysql_host, $this->config->mysql_user, $this->config->mysql_pass,
				$this->config->mysql_db );

		} catch ( mysqli_sql_exception $exception ) {
			$this->mysql_error = $exception->getMessage();
		}
	}


	public function setRequest( $request ) {
		$this->request = $request;
	}

	public function request( $request ) {

		$start = microtime(true);

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
			'correct' => $corrected,
			'time' => $this->connection->execution_time,
			'alltime' => (microtime(true) - $start),
			'innertime' => $this->connection->execution_time_inner
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

	protected function get_sorted_suggestions( $results, $phrase, $advanced, $limit = 0 ) {
		$suggestions = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$suggestion = trim( $this->get_match( addslashes( $result['string_content'] ), $phrase, $advanced ) );
				if ( empty( $suggestions[ $suggestion ] ) ) {
					$suggestions[ $suggestion ] = array(
						'rank'     => 0,
						'docs'     => 0,
						'hits'     => 0,
						'docs_ids' => array()
					);
				}
				if ( ! in_array( $result['post_id'], $suggestions[ $suggestion ]['docs_ids'] ) ) {
					$suggestions[ $suggestion ]['docs'] ++;
					$suggestions[ $suggestion ]['docs_ids'][] = $result['post_id'];
				}
				$suggestions[ $suggestion ]['hits'] ++;
				$suggestions[ $suggestion ]['rank'] = $suggestions[ $suggestion ]['docs'] - ( 1 / $suggestions[ $suggestion ]['hits'] );
			}
		}
		uksort( $suggestions, function ( $a, $b ) use ( $suggestions ) {
			if ( $suggestions[ $a ]['rank'] == $suggestions[ $b ]['rank'] ) {
				// if suggestions have equal rank then sort them by words count
				$len_a = str_word_count( $a );
				$len_b = str_word_count( $b );
				if ( $len_a == $len_b ) {
					// if suggestions have equal words count then sort them by symbols order
					return strnatcmp( $a, $b );
				}

				return $len_a < $len_b ? - 1 : 1;
			}

			return $suggestions[ $a ]['rank'] > $suggestions[ $b ]['rank'] ? - 1 : 1;
		} );
		if ( $limit ) {
			$suggestions = array_slice( array_keys( $suggestions ), 0, $limit );
		}

		return $suggestions;
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
		// sorting suggestions here
		usort( $ret, "strnatcmp" );

		return $ret;
	}

	public function getTokenizedText( $text, $plain = true ) {

		$get_tokenized_text_result = $this->connection->keywords( $this->config->autocomplete_index, $text );
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
		foreach ( $get_tokenized_text_result as $t_keyword ) {

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

		$l_keyword_lev = null;
		$l_keyword     = null;

		foreach ( $this->config->search_in_blogs as $blog ) {

			$get_corrected_keyword_result = $this->connection->qsuggest( 'autocomplete_blog_' . $blog, $keyword,
				[ '1 as non_char' ] );
			if ( ! $get_corrected_keyword_result ) {
				$corrected_keyword = false;
				continue;
			}

			foreach ( $get_corrected_keyword_result as $c_keyword ) {

				if ( $c_keyword['distance'] == 0 ) {
					$c_keyword['distance'] = 0.5;
				}
				$k = $c_keyword['suggest'];

				if ( strpos( $k, chr( 3 ) ) ) {
					$k = str_replace( chr( 3 ), " ", $k );
				}

				//$l = levenshtein( $keyword, $k );
				$l = $c_keyword['distance'] / $c_keyword['docs'];

				if ( ( is_null( $l_keyword_lev ) || ! is_null( $l_keyword_lev ) && $l_keyword_lev > $l ) && ( ! $skip_the_same_keyword || $k != $keyword ) ) {
					$l_keyword     = $k;
					$l_keyword_lev = $l;
				}
			}
		}


		if ( ! is_null( $l_keyword_lev ) && $l_keyword_lev < $this->config->corrected_levenshtein_min ) {
			$corrected_keyword = $l_keyword;
		} elseif ( $use_edgegrams ) {
			$corrected_keyword = $this->getCorrectedKeyword( $keyword, $use_edgegrams = false );
		}

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
		return $this->mysql->escape_string( $str );
	}

	public function get_single_word_suggest( $query, $limit = self::MAX_SUGGESTS ) {

		$cached_suggests = $this->cache->check_cache( $query );
		if ( $cached_suggests ) {
			return $cached_suggests;
		}


		$get_suggestions_query_result = $this->connection->keywords( $this->config->autocomplete_index, $query . '*',
			[ "'docs' as sort_mode", '1 as stats', '5 as expansion_limit' ] );


		if ( $get_suggestions_query_result && count( $get_suggestions_query_result ) == 1 ) {
			if ( empty( $get_suggestions_query_result[0]['docs'] ) ) {
				$get_suggestions_query_result = false;
			}
		}

		if ( ! $get_suggestions_query_result ) {
			return false;
		}


		$suggest_count = 0;
		foreach ( $get_suggestions_query_result as $suggestion ) {

			$suggest_count ++;

			if ( ! empty( $limit ) && $suggest_count >= $limit ) {
				break;
			}


			if ( isset( $suggestion['normalized'] ) ) {

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

			/* if suggests taxonomy, custom field what stored in system format d*/
			if ( strpos( $suggestion, '43m4z87' ) !== false ) {
				continue;
			}
			if ( strpos( $suggestion, "'" ) ) {
				$suggestion = str_replace( "'", "\'", $suggestion );
			}

			if ( strpos( $suggestion, chr( 3 ) ) ) {
				$suggestion = str_replace( chr( 3 ), " ", $suggestion );
			}

			$suggestions[] = $suggestion;
		}

		if ( ! empty( $suggestions ) ) {
			$this->cache->set_cache( $query, $suggestions );

			return $suggestions;
		}

		return false;
	}

	public function suggest( $query, $advanced = 0 ) {

		/* Check cache for all query */
		$cached_suggests = $this->cache->check_cache( $query );
		if ( $cached_suggests ) {
			return $cached_suggests;
		}


		$all_words = explode( ' ', $query );

		$words_count = count( $all_words );
		if ( $words_count == 1 ) {
			return $this->get_single_word_suggest( $query );
		} elseif ( $words_count < 1 ) {
			return [];
		}

		$suggestions      = [];
		$weak_suggestions = [];

		$all_phrase = explode( ' ', $query );

		if ( ! empty( $all_phrase ) ) {
			$all_weak_phrase = implode( ' NEAR/3 ', $all_phrase );
			$all_phrase      = implode( ' ', $all_phrase );
		} else {
			$all_phrase      = '';
			$all_weak_phrase = '';
		}


		$parameters  =
			[
				'index'        => $this->config->autocomplete_index,
				'match_phrase' => $all_phrase . '*',
				'where'        => [ 'advanced', $advanced ]
			];
		$results     = $this->connection->select( $parameters );
		$suggestions = $this->get_sorted_suggestions( $results, $all_phrase, $advanced, 20 );

		if ( ! empty( $suggestions ) ) {
			$this->cache->set_cache( $query, $suggestions );

			return $suggestions;
		}


		/* If we don't find any exact match, try to find weak matches */

		$parameters =
			[
				'index' => $this->config->autocomplete_index,
				'match' => $all_weak_phrase . '*',
				'where' => [ 'advanced', $advanced ],
				'limit' => 10
			];
		$results    = $this->connection->select( $parameters );

		$suggestions = $this->get_sorted_suggestions( $results, $all_weak_phrase, $advanced, 10 );

		$this->cache->set_cache( $query, $suggestions );

		return $suggestions;
	}


	private function get_match( $content, $query, $advanced ) {
		if ( $advanced ) {
			if ( preg_match( '/START.*\|(.*?)\|.*END/usi', $content, $matches ) ) {
				$content = trim( $matches[1] );
			}
		} else {
			$exploded = explode( ' ', $query );

			$first_word = array_shift( $exploded );
			$last_word  = array_pop( $exploded );
		}


		$result = $this->connection->snippets( 'main_blog_' . $this->config->blog_id, $query, $content,
			[
				' 1 AS around',
				'1 as force_passages',
				'1 as limit_passages',
				"'' as chunk_separator",
				"'' as before_match",
				"'' as after_match"
			] );
		if ( ! empty( $result[0]['snippet'] ) ) {

			$result[0]['snippet'] = str_replace(
				[ ',', '.', '=', ']', '[', ')', '(', '*' ],
				[ '', '', '', '', '', '', '', '' ],
				$result[0]['snippet'] );

			if ( ! $advanced ) {
				$exploded = explode( strtolower( $first_word ), strtolower( $result[0]['snippet'] ) );
				if ( ! empty( $exploded[1] ) ) {
					unset( $exploded[0] );
					$result[0]['snippet'] = $first_word . implode( ' ', $exploded );
				}
			}

			return $result[0]['snippet'];
		}

		return '';
	}


}