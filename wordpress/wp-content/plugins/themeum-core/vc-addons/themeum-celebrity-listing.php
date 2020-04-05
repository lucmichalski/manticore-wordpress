<?php
add_shortcode( 'themeum_celebrity_listing_2', function($atts, $content = null) {

	extract(shortcode_atts(array(
		'category' 		=> 'themeumall',
		'number' 		=> '8',
		'column'		=>	'3',
		'class' 		=> '',
		'order_by'		=> 'date',
		'order'			=> 'DESC',
		), $atts));

 	global $post;
 	$posts = 0;	
	$temp_post = $post;
	$output = $args = $url_encode = $get_keyword = $get_keyword_raw = $get_years =  '';

	
	# Basic Query
    $args = array(
		'post_type'			=> 'celebrity',
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
					'taxonomy' => 'celebrity_cat',
					'field'    => 'slug',
					'terms'    => $category,
				),
			),
		);
		if( $category != 'themeumall' ){
			$args = array_merge( $args,$args2 );
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

			$output .= '<div class="item col-sm-6 col-md-'.esc_attr($column).'">';
                $output .= '<div class="movie-poster">';
                    $output .= get_the_post_thumbnail($mpost->ID,'moview-profile', array('class' => 'img-responsive'));
                    $output .= '<a href="'.get_the_permalink($mpost->ID).'" class="play-icon"><i class="themeum-moviewenter"></i></a>';
                $output .= '</div>';//movie-poster
               $output .= '<div class="movie-details">';
                    $output .= '<div class="movie-name">';
                        $output .= '<h4 class="movie-title"><a href="'.get_the_permalink($mpost->ID).'">'.get_the_title($mpost->ID).'</a></h4>';
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

		# Total Post
	    $args['posts_per_page'] = -1;
	    $total_post = get_posts( $args );
		$var = $number;
		if( $var == "" || $var == 0 ){
			$total_post = 1;
		}else{
			$total_post = ceil( count($total_post)/(int)$var );
		}

		# QUERY Page
		if (is_front_page()) {
	        $paged = (get_query_var('page')) ? get_query_var('page') : 1;
	    }else{
	        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	    }

		$output .= '<div class="themeum-pagination">';
			$big 			= 999999999; # need an unlikely integer
			$output 		.= paginate_links( array(
				'type'      => 'list',
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


# Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" 				=> esc_html__("Celebrity Listing", 'themeum-core'),
		"base" 				=> "themeum_celebrity_listing_2",
		'icon' 				=> 'icon-thm-video_post',
		"class" 			=> "",
		"description" 		=> esc_html__("Widget Celebrity Listing", 'themeum-core'),
		"category" 			=> esc_html__('Moview', 'themeum-core'),
		"params" 			=> array(				

			array(
				"type" 			=> "dropdown",
				"heading" 		=> esc_html__("Category Filter", 'themeum-core'),
				"param_name" 	=> "category",
				"value" 		=> themeum_cat_list('celebrity_cat'),
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