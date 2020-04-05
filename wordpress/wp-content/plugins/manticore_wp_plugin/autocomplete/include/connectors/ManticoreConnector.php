<?php

interface ManticoreConnector{

	public function __construct($config);

	function keywords($index, $text, $options = []);

	function qsuggest($index, $word, $options);

	function select($data );

	function snippets($index, $query, $data, $options );

}