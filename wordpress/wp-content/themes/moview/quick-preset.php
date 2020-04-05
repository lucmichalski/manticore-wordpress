<?php
header('Content-type: text/css');

$parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
$wp_load = $parse_uri[0].'wp-load.php';
require_once($wp_load);

$output = '';

if( moview_options('preset') ){
	$link_color = esc_attr(moview_options('link-color'));

	if( moview_options('custom-preset-en') ) {

		if(isset($link_color)){
			$output .= 'a,a:focus,.top-user-login i,.themeum-title .title-link:hover,
			.recommend-movies .movie-details .movie-title a:hover,.item:hover .play-icon:hover,.item .movie-details a:hover,
			.movie-featured .item .movie-details a:hover,.moview-common-layout .movie-details .movie-title a:hover,
			.moview-common-layout .owl-controls .owl-prev:hover::after,
			.moview-common-layout .owl-controls .owl-next:hover::after,.trailers-videos .trailer-item .movie-title a:hover,
			.moview-filters .list-inline>li.active a,.moview-search-results ul li a:hover,.movie-celebrities .movie-celebrity-name a:hover,
			.sub-title .breadcrumb>.active,.modal-content .lost-pass:hover,#mobile-menu ul li:hover > a,#mobile-menu ul li.active > a,
			.top-align .sp-moview-icon-user,#sidebar .widget ul li a:hover,.moview-social-icon ul li a:hover,
			.moview-info-sidebar a:hover,.trailer-item .play-icon:hover,.continue:hover,.continue:hover:after,
			.themeum-pagination ul li .current,.themeum-pagination ul li a:hover,
			#buddypress div.item-list-tabs ul li.current a, #buddypress div.item-list-tabs ul li.selected a,
			#buddypress ul.item-list li div.item-title a:hover,
			#buddypress div.activity-meta a.acomment-reply,#buddypress div.activity-meta a.bp-secondary-action,
			.buddy-sidebar .widget.buddypress div.item-options a:hover,
			.buddy-sidebar .widget.buddypress div.item-options a.selected,
			.woocommerce div.product p.price, .woocommerce div.product span.price,
			.product-thumbnail-outer h3 a:hover,#bbpress-forums li.bbp-topic-title .bbp-topic-permalink:hover, #bbpress-forums li.bbp-forum-info .bbp-forum-title:hover,
			#bbpress-forums li.bbp-topic-title p.bbp-topic-meta .bbp-topic-started-by a:hover { color: '. esc_attr($link_color) .'; }';
		}	

		if(isset($link_color)){
			$output .= '.error-page-inner a.btn.btn-primary.btn-lg,.btn.btn-primary,#buddypress button,.news-feed .news-feed-item .news-feed-info .meta-category a:hover,
			.spotlight-post .list-inline>li>a:after,input[type=submit],.form-submit input[type=submit],
			.widget .tagcloud a,.carousel-left:hover, .carousel-right:hover,.entry-link-post-format,.entry-quote-post-format,input[type=button],
			#buddypress input[type=submit],#buddypress input[type=submit]:focus,#buddypress input[type=button],
			#buddypress .generic-button a,#buddypress .activity-list li.load-more,#buddypress a.button,
			.woocommerce div.product form.cart .button,.woocommerce #respond input#submit,.woocommerce a.button, .woocommerce button.button, 
			.woocommerce input.button,#buddypress ul.button-nav li.current a,#bbpress-forums li.bbp-topic-title  p.bbp-topic-meta .bbp-topic-started-in a,
			.bbpress-sidebar button,.widget.widget_search #searchform .btn-search{ background-color: '. esc_attr($link_color) .'; }';
		}	

		if(isset($link_color)){
			$output .= '.moview-filters .list-inline>li.active>a { border-bottom-color: '. esc_attr($link_color) .'; }';
			$output .= 'input:focus, textarea:focus, keygen:focus, select:focus  { border-color: '. esc_attr($link_color) .'; }';
		}
	}

	if( moview_options('custom-preset-en') ) {
		
		if( moview_options('hover-color') ){
			$output .= 'a:hover, .widget.widget_rss ul li a,
			#buddypress div.activity-meta a.acomment-reply,#buddypress div.activity-meta a.bp-secondary-action,
			#buddypress ul.item-list li div.item-title span.activity-read-more a:hover{ color: '.esc_attr( moview_options('hover-color') ) .'; }';
			$output .= '.error-page-inner a.btn.btn-primary.btn-lg:hover,.btn.btn-primary:hover,input[type=button]:hover,
			#buddypress input[type=submit]:hover,#buddypress input[type=button]:hover,#buddypress div.generic-button a:hover,
			#buddypress .activity-list li.load-more:hover,#buddypress a.button:hover,
			.woocommerce div.product form.cart .button:hover,.woocommerce #respond input#submit:hover,
			.woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce input.button:hover,
			#buddypress ul.button-nav li.current a:hover,#buddypress button:hover,.bbpress-sidebar button:hover,
			.widget.widget_search #searchform .btn-search:hover{ background-color: '.esc_attr( moview_options('hover-color') ) .'; }';
			$output .= '.btn.btn-primary{ border-color: '.esc_attr( moview_options('hover-color') ) .'; }';
		}
	}

} else {
$output ='a,a:focus,.top-user-login i,.themeum-title .title-link:hover,
.recommend-movies .movie-details .movie-title a:hover,.item:hover .play-icon:hover,.item .movie-details a:hover,
.movie-featured .item .movie-details a:hover,.moview-common-layout .movie-details .movie-title a:hover,
.moview-common-layout .owl-controls .owl-prev:hover::after,
.moview-common-layout .owl-controls .owl-next:hover::after,.trailers-videos .trailer-item .movie-title a:hover,
.moview-filters .list-inline>li.active a,.moview-search-results ul li a:hover,.movie-celebrities .movie-celebrity-name a:hover,
.sub-title .breadcrumb>.active,.modal-content .lost-pass:hover,#mobile-menu ul li:hover > a,#mobile-menu ul li.active > a,
.top-align .sp-moview-icon-user,#sidebar .widget ul li a:hover,.moview-social-icon ul li a:hover,
.moview-info-sidebar a:hover,.trailer-item .play-icon:hover,.continue:hover,.continue:hover:after,
.themeum-pagination ul li .current,.themeum-pagination ul li a:hover,
#buddypress div.item-list-tabs ul li.current a, #buddypress div.item-list-tabs ul li.selected a,
#buddypress ul.item-list li div.item-title a:hover,
#buddypress div.activity-meta a.acomment-reply,#buddypress div.activity-meta a.bp-secondary-action,
.buddy-sidebar .widget.buddypress div.item-options a:hover,
.buddy-sidebar .widget.buddypress div.item-options a.selected,
.woocommerce div.product p.price, .woocommerce div.product span.price,
.product-thumbnail-outer h3 a:hover,#bbpress-forums li.bbp-topic-title .bbp-topic-permalink:hover, #bbpress-forums li.bbp-forum-info .bbp-forum-title:hover,
#bbpress-forums li.bbp-topic-title p.bbp-topic-meta .bbp-topic-started-by a:hover { color: #f26522; }
.error-page-inner a.btn.btn-primary.btn-lg,.btn.btn-primary,#buddypress button,.news-feed .news-feed-item .news-feed-info .meta-category a:hover,
.spotlight-post .list-inline>li>a:after,input[type=submit],.form-submit input[type=submit],
.widget .tagcloud a,.carousel-left:hover, .carousel-right:hover,.entry-link-post-format,.entry-quote-post-format,input[type=button],
#buddypress input[type=submit],#buddypress input[type=submit]:focus,#buddypress input[type=button],
#buddypress .generic-button a,#buddypress .activity-list li.load-more,#buddypress a.button,
.woocommerce div.product form.cart .button,.woocommerce #respond input#submit,.woocommerce a.button, .woocommerce button.button, 
.woocommerce input.button,#buddypress ul.button-nav li.current a,#bbpress-forums li.bbp-topic-title  p.bbp-topic-meta .bbp-topic-started-in a,
.bbpress-sidebar button,.widget.widget_search #searchform .btn-search { background-color: #f26522; }
.moview-filters .list-inline>li.active>a { border-bottom-color: #f26522; }
input:focus, textarea:focus, keygen:focus, select:focus  { border-color: #f26522; }
a:hover, .widget.widget_rss ul li a,
#buddypress div.activity-meta a.acomment-reply,#buddypress div.activity-meta a.bp-secondary-action,
#buddypress ul.item-list li div.item-title span.activity-read-more a:hover { color: #d54d0d; }
.error-page-inner a.btn.btn-primary.btn-lg:hover,.btn.btn-primary:hover,input[type=button]:hover,
#buddypress input[type=submit]:hover,#buddypress input[type=button]:hover,#buddypress div.generic-button a:hover,
#buddypress .activity-list li.load-more:hover,#buddypress a.button:hover,
.woocommerce div.product form.cart .button:hover,.woocommerce #respond input#submit:hover,
.woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce input.button:hover,
#buddypress ul.button-nav li.current a:hover,#buddypress button:hover,.bbpress-sidebar button:hover,
.widget.widget_search #searchform .btn-search:hover { background-color: #d54d0d; }
.btn.btn-primary{ border-color: #d54d0d; }';
}

echo $output;