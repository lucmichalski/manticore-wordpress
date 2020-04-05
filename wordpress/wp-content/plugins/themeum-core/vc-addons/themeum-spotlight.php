<?php
add_shortcode( 'themeum_spotlight', function($atts, $content = null) {

	extract(shortcode_atts(
        array(
            'latest_section'    => 'on',
    		'coming_soon'	    => 'on',
            'top_rated'         => 'on',
            'recently_released' => 'on',
            'number'            => '20',
		), $atts));

	$output = '';

    $output .= '<div class="spotlight-post tabpanel">';
        $output .= '<ul class="list-unstyled list-inline text-left">';
            if( $latest_section == 'on' ) { $output .= '<li class="active"><a href="#latest" data-toggle="tab">'.__('Latest','themeum-core').'</a></li>'; }
            if( $coming_soon == 'on' ) { $output .= '<li><a href="#coming-soon" data-toggle="tab">'.__('Coming Soon','themeum-core').'</a></li>'; }
            if( $top_rated == 'on' ) { $output .= '<li><a href="#top-rated" data-toggle="tab">'.__('Top Rated','themeum-core').'</a></li>'; }
            if( $recently_released == 'on' ) { $output .= '<li><a href="#recently-released" data-toggle="tab">'.__('Recently Released','themeum-core').'</a></li>'; }
        $output .= '</ul>';
        $output .= '<div class="tab-content">';
           
            # latest open
            if( $latest_section == 'on' ) {
                $output .= '<div class="spotlight moview-common-layout tab-pane active fade in" id="latest">';
                    $arr = array(
                                    'post_type'     => 'movie',
                                    'posts_per_page'=> $number
                                );
                    query_posts( $arr ); while (have_posts()) : the_post();
                        $movie_type      = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                        $movie_trailer_info     = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                        $release_year    = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_release_year',true));
                       $output .= '<div class="item">';
                            $output .= '<div class="movie-poster">';
                            $output .= get_the_post_thumbnail(get_the_ID(),'moview-profile', array('class' => 'img-responsive'));
                            if( is_array(($movie_trailer_info)) ) {
                                if(!empty($movie_trailer_info)) {
                                    foreach( $movie_trailer_info as $key=>$value ){
                                        if ($key==0) {
                                            if (isset($value["themeum_video_link"]) && $value["themeum_video_link"] != '') {
                                                $output .= '<a class="play-icon play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr($value["themeum_video_source"]).'">
                                                <i class="themeum-moviewplay"></i>
                                                </a>';
                                                $output .= '<div class="content-wrap">';
                                                    $output .= '<div class="video-container">';
                                                        $output .= '<span class="video-close">x</span>';
                                                    $output .= '</div>';
                                                $output .= '</div>'; //content-wrap
                                            }
                                             else {
                                                $output .= '<a class="play-icon" href="'.get_permalink().'">
                                                <i class="themeum-moviewenter"></i>
                                                </a>';
                                            }
                                        }
                                    }
                                }
                            }
                            $output .= '</div>';//movie-poster
                            $output .= '<div class="movie-details">';
                                $output .= '<div class="movie-rating-wrapper">';
                                if (function_exists('themeum_wp_rating')) {
                                    $output .= '<div class="movie-rating">';
                                        $output .= '<span class="themeum-moviewstar active"></span>';
                                    $output .= '</div>';
                                    $output .= '<span class="rating-summary"><span>'.themeum_wp_rating(get_the_ID(),'single').'</span>/'.__('10','themeum-core').'</span>';
                                }
                                $output .= '</div>';//movie-rating-wrapper
                                $year ='';
                                if ($release_year) { 
                                    $year =  '('.esc_attr($release_year).')'; 
                                }
                                $output .= '<div class="movie-name">';
                                    $output .= '<h4 class="movie-title"><a href="'.get_the_permalink().'">'.get_the_title().$year.'</a></h4>';
                                    if ($movie_type) { 
                                        $output .= '<span class="tag">'.esc_attr($movie_type).'</span>';
                                    }
                                $output .= '</div>';//movie-name
                            $output .= '</div>';//movie-details
                        $output .= '</div> '; //item 
                    endwhile;
                    wp_reset_query();
                $output .= '</div>'; //moview-common-layout 
            }
            // latest close




            // coming soon open
            if( $coming_soon == 'on' ) { 
                $output .= '<div class="spotlight moview-common-layout tab-pane fade in" id="coming-soon">';
                    $arr = array(
                                    'post_type'     => 'movie',
                                    'meta_query'    => array(
                                                        array(
                                                        'key'       => 'themeum_release_date',
                                                        'value'     => date("Y-m-d", strtotime( date("Y-m-d") )),
                                                        'compare'   =>'>',
                                                        'type'      =>'date',
                                                    )),
                                    'post_status'       => 'publish',
                                    'orderby'           => 'meta_value',
                                    'order'             => 'ASC',
                                    'meta_value'        => '1',
                                    'posts_per_page'    => $number
                                );
                    query_posts( $arr ); while (have_posts()) : the_post();
                    $movie_type      = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                    $movie_trailer_info     = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                    $release_year    = get_post_meta(get_the_ID(),'themeum_movie_release_year',true);
                       $output .= '<div class="item">';
                            $output .= '<div class="movie-poster">';
                                $output .= get_the_post_thumbnail(get_the_ID(),'moview-profile', array('class' => 'img-responsive'));
                                if( is_array(($movie_trailer_info)) ) {
                                    if(!empty($movie_trailer_info)) {
                                        foreach( $movie_trailer_info as $key=>$value ){
                                            if ($key==0) {
                                                if (isset($value["themeum_video_link"]) && $value["themeum_video_link"] != '') {
                                                    $output .= '<a class="play-icon play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr($value["themeum_video_source"]).'">
                                                    <i class="themeum-moviewplay"></i>
                                                    </a>';
                                                    $output .= '<div class="content-wrap">';
                                                        $output .= '<div class="video-container">';
                                                            $output .= '<span class="video-close">x</span>';
                                                        $output .= '</div>';
                                                    $output .= '</div>'; //content-wrap
                                                }
                                                 else {
                                                    $output .= '<a class="play-icon" href="'.get_permalink().'">
                                                    <i class="themeum-moviewenter"></i>
                                                    </a>';
                                                }
                                            }
                                        }
                                    }
                                }
                            $output .= '</div>';//movie-poster
                            $output .= '<div class="movie-details">';
                                $output .= '<div class="movie-rating-wrapper">';
                                if (function_exists('themeum_wp_rating')) {
                                    $output .= '<div class="movie-rating">';
                                        $output .= '<span class="themeum-moviewstar active"></span>';
                                    $output .= '</div>';
                                    $output .= '<span class="rating-summary"><span>'.themeum_wp_rating(get_the_ID(),'single').'</span>/10</span>';
                                }
                                $output .= '</div>';//movie-rating-wrapper
                                $year ='';
                                if ($release_year) { 
                                    $year =  '('.esc_attr($release_year).')'; 
                                }
                                $output .= '<div class="movie-name">';
                                    $output .= '<h4 class="movie-title"><a href="'.get_the_permalink().'">'.get_the_title().$year.'</a></h4>';
                                    if ($movie_type) { 
                                        $output .= '<span class="tag">'.esc_attr($movie_type).'</span>';
                                    }
                                $output .= '</div>';//movie-name
                            $output .= '</div>';//movie-details
                        $output .= '</div> '; //item
                    endwhile;
                    wp_reset_query();
                $output .= '</div>';//moview-common-layout
            }
            // coming soon close




            // Top Rated close
            if( $top_rated == 'on' ) { 
                $output .= '<div class="spotlight moview-common-layout tab-pane fade in" id="top-rated">';
                    $arr = array(
                                    'post_type'         => 'movie',
                                    'post_status'       => 'publish',
                                    'orderby'           => 'meta_value_num',
                                    // 'meta_value'        => '1',
                                    'order'             => 'DESC',
                                    'meta_key'          => 'rating',
                                    'posts_per_page'    => $number
                                );
                    query_posts( $arr ); while (have_posts()) : the_post();
                    $movie_type      = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                    $movie_trailer_info     = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                    $release_year     = get_post_meta(get_the_ID(),'themeum_movie_release_year',true);
                       $output .= '<div class="item">';
                            $output .= '<div class="movie-poster">';
                                $output .= get_the_post_thumbnail(get_the_ID(),'moview-profile', array('class' => 'img-responsive'));
                                if( is_array(($movie_trailer_info)) ) {
                                    if(!empty($movie_trailer_info)) {
                                        foreach( $movie_trailer_info as $key=>$value ){
                                            if ($key==0) {
                                                if (isset($value["themeum_video_link"]) && $value["themeum_video_link"] != '') {
                                                    $output .= '<a class="play-icon play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr($value["themeum_video_source"]).'">
                                                    <i class="themeum-moviewplay"></i>
                                                    </a>';
                                                    $output .= '<div class="content-wrap">';
                                                        $output .= '<div class="video-container">';
                                                            $output .= '<span class="video-close">x</span>';
                                                        $output .= '</div>';
                                                    $output .= '</div>'; //content-wrap
                                                }
                                                 else {
                                                    $output .= '<a class="play-icon" href="'.get_permalink().'">
                                                    <i class="themeum-moviewenter"></i>
                                                    </a>';
                                                }
                                            }
                                        }
                                    }
                                }
                            $output .= '</div>';//movie-poster
                            $output .= '<div class="movie-details">';
                                $output .= '<div class="movie-rating-wrapper">';
                                if (function_exists('themeum_wp_rating')) {
                                    $output .= '<div class="movie-rating">';
                                        $output .= '<span class="themeum-moviewstar active"></span>';
                                    $output .= '</div>';
                                    $output .= '<span class="rating-summary"><span>'.themeum_wp_rating(get_the_ID(),'single').'</span>/10</span>';
                                }
                                $output .= '</div>';//movie-rating-wrapper
                                $year ='';
                                if ($release_year) { 
                                    $year =  '('.esc_attr($release_year).')'; 
                                }
                                $output .= '<div class="movie-name">';
                                    $output .= '<h4 class="movie-title"><a href="'.get_the_permalink().'">'.get_the_title().$year.'</a></h4>';
                                    if ($movie_type) { 
                                        $output .= '<span class="tag">'.esc_attr($movie_type).'</span>';
                                    }
                                $output .= '</div>';//movie-name
                            $output .= '</div>';//movie-details
                        $output .= '</div> ';//item
                    endwhile;
                    wp_reset_query();
                $output .= '</div>';//moview-common-layout
            }
            // Top Rated close
            

            // coming soon open
            if( $recently_released == 'on' ) { 
                $output .= '<div class="spotlight moview-common-layout tab-pane fade in" id="recently-released">';
                    $arr = array(
                                    'post_type'     => 'movie',
                                    /*'meta_query'    => array(
                                                        array(
                                                        'key'       => 'themeum_release_date',
                                                        'value'     => date("Y-m-d", strtotime( date("Y-m-d") )),
                                                        'compare'   =>'<',
                                                        'type'      =>'date',
                                                    )),*/
                                    'meta_key'       => 'themeum_release_date',
                                    'meta_value'     => date("Y-m-d", strtotime( date("Y-m-d") )),
                                    'meta_type'      => 'DATE',
                                    'meta_compare'   => '<',
                                    'orderby'        => 'meta_value',
                                    'order'          => 'DESC',

                                    'post_status'       => 'publish',
                                    // 'orderby'           => 'meta_value_date',
                                    // 'order'             => 'ASC',
                                    // 'meta_key'          => 'themeum_release_date',
                                    // 'meta_value'        => date("Y-m-d", strtotime( date("Y-m-d") )),
                                    // 'meta_type'         => 'DATE',
                                    'posts_per_page'    => $number
                                );
                    query_posts( $arr ); while (have_posts()) : the_post();
                    $movie_type      = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                    $movie_trailer_info     = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                    $release_year    = get_post_meta(get_the_ID(),'themeum_movie_release_year',true);
                       $output .= '<div class="item">';
                            $output .= '<div class="movie-poster">';
                                $output .= get_the_post_thumbnail(get_the_ID(),'moview-profile', array('class' => 'img-responsive'));
                                if( is_array(($movie_trailer_info)) ) {
                                    if(!empty($movie_trailer_info)) {
                                        foreach( $movie_trailer_info as $key=>$value ){
                                            if ($key==0) {
                                                if ($value["themeum_video_link"]) {
                                                    $output .= '<a class="play-icon play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr($value["themeum_video_source"]).'">
                                                    <i class="themeum-moviewplay"></i>
                                                    </a>';
                                                    $output .= '<div class="content-wrap">';
                                                        $output .= '<div class="video-container">';
                                                            $output .= '<span class="video-close">x</span>';
                                                        $output .= '</div>';
                                                    $output .= '</div>'; //content-wrap
                                                }
                                                 else {
                                                    $output .= '<a class="play-icon" href="'.get_permalink().'">
                                                    <i class="themeum-moviewenter"></i>
                                                    </a>';
                                                }
                                            }
                                        }
                                    }
                                }
                            $output .= '</div>';//movie-poster
                            $output .= '<div class="movie-details">';
                                $output .= '<div class="movie-rating-wrapper">';
                                if (function_exists('themeum_wp_rating')) {
                                    $output .= '<div class="movie-rating">';
                                        $output .= '<span class="themeum-moviewstar active"></span>';
                                    $output .= '</div>';
                                    $output .= '<span class="rating-summary"><span>'.themeum_wp_rating(get_the_ID(),'single').'</span>/10</span>';
                                }
                                $output .= '</div>';//movie-rating-wrapper
                                $year ='';
                                if ($release_year) { 
                                    $year =  '('.esc_attr($release_year).')'; 
                                }
                                $output .= '<div class="movie-name">';
                                    $output .= '<h4 class="movie-title"><a href="'.get_the_permalink().'">'.get_the_title().$year.'</a></h4>';
                                    if ($movie_type) { 
                                        $output .= '<span class="tag">'.esc_attr($movie_type).'</span>';
                                    }
                                $output .= '</div>';//movie-name
                            $output .= '</div>';//movie-details
                        $output .= '</div> '; //item
                    endwhile;
                    wp_reset_query();
                $output .= '</div>';//moview-common-layout
            }
            // coming soon close

        $output .= '</div>'; //tab-content
    $output .= '</div>';//spotlight-post



	return $output;

});


//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
vc_map(array(
	"name"         => __("Spotlight", "themeum-core"),
	"base"         => "themeum_spotlight",
	'icon'         => 'icon-thm-title',
	"class"        => "",
	"description"  => __("Widget Title Heading", "themeum-core"),
	"category"     => __('Moview', "themeum-core"),
	"params"       => array(

    array(
        "type"          => "textfield",
        "heading"       => esc_html__("Number of items", 'themeum-core'),
        "param_name"    => "number",
        "value"         => "20",
    ),  

    array(
        "type" => "dropdown",
        "heading" => __("Latest Section:", "themeum-core"),
        "param_name" => "latest_section",
        "value" => array('On'=>'on','OFF'=>'off'),
        ),
    array(
        "type" => "dropdown",
        "heading" => __("Coming Soon:", "themeum-core"),
        "param_name" => "coming_soon",
        "value" => array('On'=>'on','OFF'=>'off'),
        ),
    array(
        "type" => "dropdown",
        "heading" => __("Top Rated:", "themeum-core"),
        "param_name" => "top_rated",
        "value" => array('On'=>'on','OFF'=>'off'),
        ),


		)
	));
}