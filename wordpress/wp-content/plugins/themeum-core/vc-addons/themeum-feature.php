<?php
add_shortcode( 'themeum_feature', function($atts, $content = null) {

	extract(shortcode_atts(array(
		'category' 						=> '',
		'number' 						=> '6',
        'order_by'  					=> 'date',               
        'order'   						=> 'DESC',      
        'class' 						=> '',
		), $atts));

 	global $post;

 	$output     = '';
 	$posts = 0;

    // The Query
	if( ( $category == '' ) || ( $category == 'themeumall' ) ){
		$posts = get_posts( array( 
		'post_type' => 'movie',
		'posts_per_page' => esc_attr($number),
		'meta_key' => 'themeum_featured_movie',
		'meta_value' => '1',
		'order' => esc_attr($order),
		'orderby' => esc_attr($order_by),
		) );
	}else{
		$posts = get_posts( array( 
			'post_type' => 'movie',
			'tax_query' => array(
				array(
					'taxonomy' => 'movie_cat',
					'field'    => 'slug',
					'terms'    => esc_attr($category),
				),
			),
			'posts_per_page' => esc_attr($number),
			'meta_key' => 'themeum_featured_movie',
			'meta_value' => '1',			
			'order' => esc_attr($order),
			'orderby' => esc_attr($order_by),
			) );
	}
	//$output .= print_r($posts,true);

    if(count($posts)>0){
    	$output .= '<div id="moview-movie" style="visibility: hidden;" class="moview-movie-featured '.esc_attr($class).'">';
    	$output .= '<div class="row-fluid">';
    	$output .= '<div class="movie-featured">';
	    foreach ($posts as $key=>$poste): setup_postdata($poste);

	        $movie_type   	 = esc_attr(get_post_meta($poste->ID,'themeum_movie_type',true));
    		$movie_actor   	 = rwmb_meta('themeum_movie_actor','type=checkbox_list',$poste->ID);
    		$movie_trailer_info   	= get_post_meta($poste->ID,'themeum_movie_trailer_info',true);
    		$release_year    = esc_attr(get_post_meta($poste->ID,'themeum_movie_release_year',true));
			$output .= '<div class="item">';
			$output .= '<div class="movie-poster">';
            if ( has_post_thumbnail($poste->ID) ) {
            	$img 	= wp_get_attachment_image_src( get_post_thumbnail_id( $poste->ID ), 'moview-profile' );
                $output .= '<a href="'. get_permalink($poste->ID) .'">';
                $output .= '<img class="img-responsive" src="'.esc_url( $img[0] ).'" alt="'. get_the_title($poste->ID) .'" >';
                $output .= '</a>';
            }
			$output .= '</div>'; // movie-poster

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
								$output .= '<a class="play-icon" href="'.get_permalink($poste->ID).'">
								<i class="themeum-moviewenter"></i>
								</a>';
							}
						}
					}
				}
			}

			$output .= '<div class="movie-details">';
			$output .= '<div class="moview-rating-wrapper">';
			if (function_exists('themeum_wp_rating')) {
			$output .= '<div class="moview-rating"> <span class="star active"></span> </div>'; // rating
				$output .= '<span class="moview-rating-summary"><span>'.themeum_wp_rating($poste->ID,'single').'</span>/'.__('10','themeum-core').'</span>';
			}
			
			$output .= '</div>'; // rating-wrapper
			$year ='';
			if ($release_year) { 
				$year =  '('.esc_attr($release_year).')'; 
			}
			$output .= '<div class="movie-name">
			<h2 class="movie-title"><a href="'.get_permalink($poste->ID).'">'. get_the_title($poste->ID).$year.'</a></h2>';
			if ($movie_type) { 
				$output .= '<span class="tag">'.esc_attr($movie_type).'</span>';
			}
			$output .= '</div>'; //movie-name

			$output .= '<div class="cast">
			<span>'.esc_html__("Cast :", "themeum-core").'</span> ';

			if( is_array(($movie_actor)) ) {
				if(!empty($movie_actor)) {
                    $posts_id = array();
                      foreach ( $movie_actor as $value ) {
                        $posts = get_posts(array('post_type' => 'celebrity', 'name' => $value));
                        $posts_id[] = $posts[0]->ID;
                      }
                      $movie_actor = get_posts( array( 'post_type' => 'celebrity', 'post__in' => $posts_id, 'posts_per_page'   => 20) );

                    foreach ($movie_actor as $key=>$poster) {
                    setup_postdata( $poster );
                        $output .= '<a href="'.get_permalink($poster->ID).'">'.get_the_title($poster->ID).'</a>';
                    }
                    wp_reset_postdata(); 
                }
            }


			$output .= '</div>'; //cast
			$output .= '</div>'; //movie-details
			$output .= '</div>'; //item

	    endforeach;
	    wp_reset_postdata();   
	    $output .= '</div>'; //sp-mv-movie
	    $output .= '</div>'; //row-fluid
	    $output .= '</div>'; //lates-featured-post 
	     
	}

	return $output;

});


//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" => esc_html__("Feature Post", 'themeum-core'),
		"base" => "themeum_feature",
		'icon' => 'icon-thm-feature',
		"class" => "",
		"description" => esc_html__("Widget Title Heading", 'themeum-core'),
		"category" => esc_html__('Moview', 'themeum-core'),
		"params" => array(					

			array(
				"type" => "dropdown",
				"heading" => esc_html__("Category Filter", 'themeum-core'),
				"param_name" => "category",
				"value" => themeum_cat_list('movie_cat'),
				),	

			array(
				"type" => "textfield",
				"heading" => esc_html__("Number of items", 'themeum-core'),
				"param_name" => "number",
				"value" => "6",
				),				

			array(
				"type" => "dropdown",
				"heading" => esc_html__("Order", 'themeum-core'),
				"param_name" => "order",
				"value" => array('None'=>'','DESC'=>'DESC','ASC'=>'ASC'),
				),	

			array(
				"type" => "dropdown",
				"heading" => esc_html__("OderBy", 'themeum-core'),
				"param_name" => "order_by",
				"value" => array('None'=>'','Date'=>'date','Title'=>'title','Modified'=>'modified','Author'=>'author','Random'=>'rand'),
				),				

			array(
				"type" => "textfield",
				"heading" => esc_html__("Custom Class", 'themeum-core'),
				"param_name" => "class",
				"value" => "",
				),	

			)

		));
}