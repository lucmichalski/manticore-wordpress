<?php
add_shortcode( 'themeum_movie_listig', function($atts, $content = null) {

	extract(shortcode_atts(array(
		'category' 		=> 'themeumall',
		'number' 		=> '8',
		'column'		=>	'3',
		'class' 		=> '',
		'year'			=> 'all',
		'order_by'		=> 'date',
		'order'			=> 'DESC',
		), $atts));

 	global $post;
 	$temp_post = $post;
 	$posts = 0;	
	global $wpdb;
	$output = '';


	# Basic Query
    $args = array(
		'post_type'			=> 'movie',
        'post_status'		=> 'publish',
		'posts_per_page'	=> esc_attr($number),
		'order'				=> $order,
		'orderby'			=> $order_by,
		'paged'				=> max( 1, get_query_var('paged') )
	);
    
    # Category Add
	if( $category != '' ){
		$args2 = array( 
			'tax_query' => array(
				array(
					'taxonomy' => 'movie_cat',
					'field'    => 'slug',
					'terms'    => $category,
				),
			),
		);
		if( $category != 'themeumall' ){
			$args = array_merge( $args,$args2 );
		}
	}


	if( $year!='' ){
		$args2 = array(
				'meta_query'    => array(
                        array(
                            'key'       => 'themeum_movie_release_year',
                            'value'     => $year,
                            'compare'   =>'=',
                        ),
                    ),
				);
		if( $year != 'all' ){
			$args = array_merge( $args , $args2 );
		}
	}

	$posts = get_posts( $args );

    if(count($posts)>0){

		// The Loop
		
			$output .= '<div class="clearfix"></div>';
			$output .= '<div class="moview-common-layout moview-celebrities-filters">';
			$x = 1;
			foreach ($posts as $key=>$mpost): setup_postdata($mpost);
				if( $x == 1 ){
			    	$output .= '<div class="row margin-bottom">';	
			    }
		        $movie_type      = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                $movie_trailer_info     = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                $release_year    = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_release_year',true));



				$output .= '<div class="item col-sm-2 col-md-'.esc_attr( $column ).'">';
                    $output .= '<div class="movie-poster">';
	                    $output .= get_the_post_thumbnail($mpost->ID,'moview-profile', array('class' => 'img-responsive'));
	                    if( is_array(($movie_trailer_info)) ) {
	                        if(!empty($movie_trailer_info)) {
	                            foreach( $movie_trailer_info as $key=>$value ){
	                                if ($key==0) {
	                                    if ($value["themeum_video_link"]) {
	                                        $output .= '<a class="play-icon play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr( $value["themeum_video_source"] ).'">
	                                        <i class="themeum-moviewplay"></i>
	                                        </a>';
	                                        $output .= '<div class="content-wrap">';
	                                            $output .= '<div class="video-container">';
	                                                $output .= '<span class="video-close">x</span>';
	                                            $output .= '</div>';
	                                        $output .= '</div>'; //content-wrap
	                                    }
	                                     else {
	                                        $output .= '<a class="play-icon" href="'.get_permalink($mpost->ID).'">
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
                            $output .= '<span class="rating-summary"><span>'.themeum_wp_rating($mpost->ID,'single').'</span>/'.__('10','themeum-core').'</span>';
                        }
                        $output .= '</div>';//movie-rating-wrapper
                        $year ='';
                        if ($release_year) { 
                            $year =  '('.esc_attr($release_year).')'; 
                        }
                        $output .= '<div class="movie-name">';
                            $output .= '<h4 class="movie-title"><a href="'.get_the_permalink($mpost->ID).'">'.get_the_title($mpost->ID).$year.'</a></h4>';
                            if ($movie_type) { 
                                $output .= '<span class="tag">'.esc_attr($movie_type).'</span>';
                            }
                        $output .= '</div>';//movie-name
                    $output .= '</div>';//movie-details					
				$output .= '</div>';//item
				
				if( $x == (12/$column) ){
					$output .= '</div>'; //row	
					$x = 1;	
				}else{
					$x++;	
				}				
			endforeach;
			$output .= '</div>';//spotlight-common
			if($x !=  1 ){
				$output .= '</div>'; //row	
			}	
		wp_reset_postdata();

		// Total Post
	    $args['posts_per_page'] = -1;
	    $total_post = get_posts( $args );
		$var = $number;
		if( $var == "" || $var == 0 ){
			$total_post = 1;
		}else{
			$total_post = ceil( count($total_post)/(int)$var );
		}

		# Query for FontPage and default template. 
	    if (is_front_page()) {
	        $paged = (get_query_var('page')) ? get_query_var('page') : 1;
	    }else{
	        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	    }
		
		$output .= '<div class="themeum-pagination">';
			$big 			= 999999999; # need an unlikely integer
			$output 		.= paginate_links( array(
				'type' 		=> 'list',
				'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) )),
				'format' 	=> '?paged=%#%',
				'current' 	=> $paged,
				'total' 	=> $total_post
			) );
		$output .= '</div>'; # pagination-in	
		
	}
	$post = $temp_post;

	return $output;
});

$year_list = themeum_get_movie_release_year();
$year_list_data = array( 'all' => 'all');
if(isset($year_list)){
	if(is_array($year_list)){
		if(!empty($year_list)){
			foreach ( $year_list as $value) {
				$year_list_data[ $value->meta_value ] = $value->meta_value;
			}
		}
	}
}

# Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" 			=> esc_html__("Movies Listing", 'themeum-core'),
		"base" 			=> "themeum_movie_listig",
		'icon' 			=> 'icon-thm-video_post',
		"class" 		=> "",
		"description" 	=> esc_html__("Widget Featured Movies", 'themeum-core'),
		"category" 		=> esc_html__('Moview', 'themeum-core'),
		"params" 		=> array(				

			array(
				"type" 			=> "dropdown",
				"heading" 		=> esc_html__("Category Filter", 'themeum-core'),
				"param_name" 	=> "category",
				"value" 		=> themeum_cat_list('movie_cat'),
			),
			array(
				"type" 			=> "textfield",
				"heading" 		=> esc_html__("Number of items", 'themeum-core'),
				"param_name" 	=> "number",
				"value" 		=> "8",
			),
			array(
				"type" 			=> "dropdown",
				"heading" 		=> esc_html__("Number Of Column", "themeum-core"),
				"param_name" 	=> "column",
				"value" 		=> array('Select'=>'','column 2'=>'6','column 3'=>'4','column 4'=>'3'),
			),
			array(
				"type" 			=> "dropdown",
				"heading" 		=> esc_html__("Year Filter", "themeum-core"),
				"param_name" 	=> "year",
				"value" 		=> $year_list_data
			),
			array(
				"type" 			=> "dropdown",
				"heading" 		=> esc_html__("OderBy", 'themeum-core'),
				"param_name" 	=> "order_by",
				"value" 		=> array('Select'=>'','Date'=>'date','Title'=>'title','Modified'=>'modified','Author'=>'author','Random'=>'rand'),
			),
			array(
				"type" 			=> "dropdown",
				"heading" 		=> esc_html__("Order", 'themeum-core'),
				"param_name" 	=> "order",
				"value" 		=> array('Select'=>'','DESC'=>'DESC','ASC'=>'ASC'),
			),
			array(
				"type" 			=> "textfield",
				"heading" 		=> esc_html__("Custom Class", 'themeum-core'),
				"param_name" 	=> "class",
				"value" 		=> "",
			),
		)
	));
}