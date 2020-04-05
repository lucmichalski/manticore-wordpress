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
function ss_isComment() {
	global $post;

	return $post->comment_id;
}

function ss_search_bar( $is_sidebar = false ) {
	global $defaultObjectSphinxSearch;

	if ( 'true' == $defaultObjectSphinxSearch->frontend->params['search_posts'] ) {
		$search_posts = "checked='checked'";
	} else {
		$search_posts = '';
	}

	if ( 'true' == $defaultObjectSphinxSearch->frontend->params['search_pages'] ) {
		$search_pages = "checked='checked'";
	} else {
		$search_pages = '';
	}

	if ( 'true' == $defaultObjectSphinxSearch->frontend->params['search_comments'] ) {
		$search_comments = "checked='checked'";
	} else {
		$search_comments = '';
	}

	$search_sorting = $defaultObjectSphinxSearch->config->admin_options['search_sorting'];
	$search_sortby_date_relevance = $search_sortby_relevance = $search_sortby_date = '';
	if ( ! empty( $defaultObjectSphinxSearch->frontend->params['search_sortby'] ) ) {
		$ss_sort_by = $defaultObjectSphinxSearch->frontend->params['search_sortby'];
	}
	if ( ! empty( $ss_sort_by ) && $ss_sort_by == 'date' ) {
		$search_sortby_date = 'checked="true"';
	} elseif ( ! empty( $ss_sort_by ) && $ss_sort_by == 'relevance' ) {
		$search_sortby_relevance = 'checked="true"';
	} else {
		$search_sortby_date_relevance = 'checked="true"';
	}

	if ( $is_sidebar ) {
		require_once( SPHINXSEARCH_PLUGIN_DIR . '/templates/sphinx_search_bar.htm' );
	} else {
		require_once( SPHINXSEARCH_PLUGIN_DIR . '/templates/sphinx_search_panel.htm' );
	}
}

function ss_top_searches( $limit = 10, $width = 0, $break = '...' ) {
	global $defaultObjectSphinxSearch;

	$result = $defaultObjectSphinxSearch->frontend->sphinx_stats_top_ten( $limit, $width, $break );
	echo "<ul>";
	foreach ( $result as $res ) {
		echo "<li><a href='/?s=" . urlencode( stripslashes( $res->keywords_full ) ) . "' title='" . htmlspecialchars( stripslashes( $res->keywords ),
				ENT_QUOTES ) . "'>" . htmlspecialchars( stripslashes( $res->keywords_cut ), ENT_QUOTES ) . "</a></li>";
	}
	echo "</ul>";
}

function ss_latest_searches( $limit = 10, $width = 0, $break = '...' ) {
	global $defaultObjectSphinxSearch;
	$result = $defaultObjectSphinxSearch->frontend->sphinx_stats_latest( $limit, $width, $break );
	echo "<ul>";
	foreach ( $result as $res ) {
		echo "<li><a href='/?s=" . urlencode( stripslashes( $res->keywords_full ) ) . "' title='" . htmlspecialchars( stripslashes( $res->keywords ),
				ENT_QUOTES ) . "'>" . htmlspecialchars( stripslashes( $res->keywords_cut ), ENT_QUOTES ) . "</a></li>";
	}
	echo "</ul>";
}

function ss_isSphinxUp() {
	global $defaultObjectSphinxSearch;

	return $defaultObjectSphinxSearch->frontend->sphinx_is_up();
}

function ss_top_ten_is_related() {
	global $defaultObjectSphinxSearch;

	return $defaultObjectSphinxSearch->frontend->sphinx_stats_top_ten_is_related();
}

function sphinx_the_title() {
	global $defaultObjectSphinxSearch;
	echo $defaultObjectSphinxSearch->frontend->sphinx_the_title();
}

function sphinx_get_type_count( $type ) {
	global $defaultObjectSphinxSearch;
	echo $defaultObjectSphinxSearch->frontend->get_type_count( $type );
}


function ss_top_searches_pager( $max_per_page = 10, $show_all = false ) {
	global $defaultObjectSphinxSearch;

	if ( $_GET['toppage'] > 1 ) {
		$current = (int) $_GET['toppage'];
		$start   = (int) ( $_GET['toppage'] - 1 ) * $max_per_page;
	} else {
		$current = 1;
		$start   = 0;
	}

	$result = $defaultObjectSphinxSearch->frontend->sphinx_stats_top( $max_per_page, 0, '...', false, 0, $start );

	$html = "<ul>";
	foreach ( $result as $res ) {
		$html .= "<li><a href='" . get_bloginfo( 'url' ) . "/?s=" . urlencode( stripslashes( $res->keywords_full ) ) . "' title='" . htmlspecialchars( stripslashes( $res->keywords ),
				ENT_QUOTES ) . "'>" . htmlspecialchars( stripslashes( $res->keywords_cut ), ENT_QUOTES ) . "</a></li>";
	}
	$html .= "</ul>";

	$pagination = array(
		'base'     => @add_query_arg( 'toppage', '%#%' ),
		'format'   => '',
		'total'    => $defaultObjectSphinxSearch->frontend->get_top_ten_total() / $max_per_page,
		'current'  => $current,
		'show_all' => false,
		'type'     => 'list'
	);

	$html .= paginate_links( $pagination );

	echo $html;
}