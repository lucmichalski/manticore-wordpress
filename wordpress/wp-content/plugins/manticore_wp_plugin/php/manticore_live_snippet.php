<?php

header( 'Content-Type: application/json' );

$title_path = SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'rep' . DIRECTORY_SEPARATOR . 'snippet-titles.txt';
$text_path  = SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'rep' . DIRECTORY_SEPARATOR . 'snippet-text.txt';

if ( self::$plugin->sphinxQL->is_active() === false ) {
	http_response_code( 404 );
	echo json_encode( [ 'status' => 'error', 'message' => 'Start Manticore daemon in your Control panel' ] );
} else {
	$resultsCount = intval( $_REQUEST['results'] );
	if ( empty( $resultsCount ) ) {
		$resultsCount = 1;
	}


	$results      = [];
	$highlighting = getHighlighting();


	if ( ! empty( $_REQUEST['dynamic'] ) ) {

		$values = explode( '-', $_REQUEST['range'] );

		$min_around = intval( $values[0] );
		$max_around = intval( $values[1] );


		$around = $max_around - $resultsCount;
		if ( $around < $min_around ) {
			$around = $min_around;
		}

		$limitValues = explode( '-', $_REQUEST['range_limit'] );
		$min_limit   = intval( $limitValues[0] );
		$max_limit   = intval( $limitValues[1] );

		$limit = $max_limit - $resultsCount * 200;
		if ( $limit < $min_limit ) {
			$limit = $min_limit;
		}

	} else {
		$around = $_REQUEST['single'];
		$limit  = $_REQUEST['limit'];
	}


	$titleContent = file_get_contents( $title_path );
	$titleContent = explode( "\n", $titleContent );

	$textContent  = file_get_contents( $text_path );
	$textContent = explode( "/--+--/", $textContent );
	$titleSnippet = self::$plugin->sphinxQL
		->call_snippets( $titleContent, Manticore_Config_Maker::MAIN_INDEX_PREFIX . get_current_blog_id(), 'WordPress',
			[
				"'" . $_REQUEST['separator'] . "' AS chunk_separator",
				"'" . $highlighting['title']['before'] . "' AS before_match",
				"'" . $highlighting['title']['after'] . "' AS after_match",
				$around . " AS around ",
				$limit . " AS limit ",
				intval( $_REQUEST['limit_passages'] ) . " AS limit_passages"
			] );

	$contentSnippet = self::$plugin->sphinxQL
		->call_snippets( $textContent,
			Manticore_Config_Maker::MAIN_INDEX_PREFIX . get_current_blog_id(), 'WordPress',

			[
				"'" . $_REQUEST['separator'] . "' AS chunk_separator",
				"'" . $highlighting['text']['before'] . "' AS before_match",
				"'" . $highlighting['text']['after'] . "' AS after_match",
				$around . " AS around ",
				$limit . " AS limit ",
				intval( $_REQUEST['limit_passages'] ) . " AS limit_passages"
			] );


	$bottomLinks = getBottomLinks( $textContent, 'http://mysite.com' );
	for ( $i = 0; $i < $resultsCount; $i ++ ) {
		$link = '';
		if ( ! empty( $bottomLinks[ $i ] ) ) {
			$link = implode( ' ', $bottomLinks[ $i ] );
		}
		$results[] = generateSnippet( $titleSnippet[ $i ]['snippet'], $contentSnippet[ $i ]['snippet'], $link );
	}


	if ( ! empty( $results ) ) {
		echo json_encode( [ 'status' => 'success', 'result' => $results ] );
	} else {
		http_response_code( 404 );
		echo json_encode( [ 'status' => 'error' ] );
	}
}

function generateSnippet( $title, $text, $bottomLinks ) {

	$append = [];
	foreach ( [ 'before_comment', 'before_page', 'before_post' ] as $k ) {
		if ( ! empty( $_REQUEST[ $k ] ) ) {
			$append[] = $_REQUEST[ $k ];
		} else {
			$append[] = '';
		}
	}
	if ( ! empty( $append ) ) {
		$title = $append[ mt_rand( 0, 2 ) ] . $title;
	}

	return '<div class="live-example-snippet dynamic">' .
	       '<div class="caption">' . $title . '</div>' .
	       '<div class="link">http://' . $_SERVER['HTTP_HOST'] . '/some-link</div>' .
	       '<div>' . $text . '</div>' .
	       '<div class="bottom-links">' . $bottomLinks . '</div>' .
	       '</div>';
}

function getHighlighting() {
	$highlighting = [];
	foreach ( [ 'title', 'text' ] as $type ) {
		$highlighting[ $type ] = prepareHighlightingByType( $type );
	}

	return $highlighting;
}

function getBottomLinks( $content, $url ) {
	$links           = [];
	if ( ! empty( $content ) ) {
		foreach ( $content as $key => $item ) {
			$uniqueLinks = [];
			preg_match_all( '#<h[1-6]+.{0,25}>(.{1,20})</h[1-6]+>#usi', $item, $matches );
			if ( ! empty( $matches[0][0] ) && ! empty( $matches[1][0] ) ) {

				foreach ( $matches[0] as $k => $match ) {
					if ( in_array( $matches[1][ $k ], $uniqueLinks ) ) {
						continue;
					}
					$uniqueLinks[]   = $matches[1][ $k ];
					$links[ $key ][] = '<a href="' . $url . '#s_' . $k . '">' . $matches[1][ $k ] . '</a>';
				}
			}
		}
	}


	return $links;
}

function prepareHighlightingByType( $type = 'text' ) {
	if ( ! empty( $_REQUEST[ 'highlighting_' . $type . '_type' ] ) ) {
		switch ( $_REQUEST[ 'highlighting_' . $type . '_type' ] ) {
			case 'mark':
				return [ 'before' => '<mark>', 'after' => '</mark>' ];
				break;
			case 'em':
				return [ 'before' => '<em>', 'after' => '</em>' ];
				break;
			case 'u':
				return [ 'before' => '<u>', 'after' => '</u>' ];
				break;
			case 'strong':
				return [ 'before' => '<strong>', 'after' => '</strong>' ];
				break;

			case 'text_color':
				return [
					'before' => '<span style="color: ' . $_REQUEST[ 'before_' . $type . '_match' ] . ';">',
					'after'  => '</span>'
				];
				break;
			case 'background_color':
				return [
					'before' => '<span style="background: ' . $_REQUEST[ 'before_' . $type . '_match' ] . ';">',
					'after'  => '</span>'
				];
				break;
			case 'style':
				return [
					'before' => '<span style="' . $_REQUEST[ 'before_' . $type . '_match' ] . '">',
					'after'  => '</span>'
				];
				break;
			case 'class':
				return [
					'before' => '<span class="' . $_REQUEST[ 'before_' . $type . '_match' ] . '">',
					'after'  => '</span>'
				];
				break;
			case 'custom':
				return [
					'before' => $_REQUEST[ 'before_' . $type . '_match' ],
					'after'  => $_REQUEST[ 'after_' . $type . '_match' ]
				];
				break;
		}
	}

	return [ 'before' => '', 'after' => '' ];
}

