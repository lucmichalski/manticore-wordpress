<?php
add_shortcode( 'themeum_trailer_video', function($atts, $content = null) {

	extract(shortcode_atts(array(
		'category' 						=> '',
		'number' 						=> '5',
		'class' 						=> '',
        'order_by'  					=> 'date',                
        'show_date'  					=> 'yes',            
        'order'   						=> 'DESC',      
		), $atts));

 	global $post;
	
 	$output     = '';
 	$counter = 1;
 	$posts= 0;

    // The Query
	if( ( $category == '' ) || ( $category == 'themeumall' ) ){
		$posts = get_posts( array( 
		'post_type' => 'movie',
		'posts_per_page' => esc_attr($number),	
	    'meta_query'    => array(
            array(
            'key'       => 'themeum_movie_trailer_info',
            'value'     => 's:18:"themeum_video_link";s:0:"";',
            'compare'   =>'NOT LIKE'
	    )),
		'order' => $order,
		'orderby' => $order_by,
		) );
	}else{
		$posts = get_posts( array( 
		'post_type' => 'movie',
		'tax_query' => array(
			array(
				'taxonomy' => 'movie_cat',
				'field'    => 'slug',
				'terms'    => $category,
			),
		),
	    'meta_query'    => array(
            array(
            'key'       => 'themeum_movie_trailer_info',
            'value'     => 's:18:"themeum_video_link";s:0:"";',
            'compare'   =>'NOT LIKE'
	    )),		
		'posts_per_page' => esc_attr($number),			
		'order' => $order,
		'orderby' => $order_by,
		) );
	}

    if(count($posts)>0){
    	$output .= '<div class="trailers-videos">';
		$output .= '<div class="row">';
    	foreach ($posts as $key=>$post): 
    		setup_postdata($post);
    		$movie_trailer_info   	= get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
    		$movie_type   	 = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
    		$release_year    = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_release_year',true));
    		if( $counter == 1 ){
				$output .= '<div class="trailer-item leading col-sm-12">';
				$output .= '<div class="trailer">';

				if (!empty($movie_trailer_info)) {

					foreach( $movie_trailer_info as $key=>$value ){
						if ($key==0) {
							if(isset($value["themeum_video_trailer_image"])){
							$image = $value["themeum_video_trailer_image"];
							$trailer_image = wp_get_attachment_image_src($image[0], 'moview-large');
							$output .= '<div class="trailer-image-wrap">';
								if ($trailer_image[0]!='') {
									$output .= '<img class="img-responsive" src="'.esc_url( $trailer_image[0] ).'" alt="'.esc_html__('trailers', 'themeum-core').'">';
								} else { 
									$output .= '<div class="trailer-no-video"></div>';
								}
								
								$output .= '<a class="play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr($value["themeum_video_source"]).'">';
									$output .= '<i class="play-icon themeum-moviewplay"></i>';
								$output .= '</a>';
								$output .= '<div class="content-wrap">';
									$output .= '<div class="video-container">';
										$output .= '<span class="video-close">x</span>';
									$output .= '</div>';
								$output .= '</div>'; //content-wrap
							$output .= '</div>'; //trailer-image-wrap	
							}
						}
					}
				}
				$output .= '<div class="trailer-info trailers-info">';
					$output .= '<div class="trailer-info-block">';
			            if ( has_post_thumbnail() ) {
			            	$img 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'moview-small' );
			                $output .= '<img class="img-responsive thumb-img" src="'.esc_url($img[0]).'" alt="'. get_the_title() .'">';
			            }
			            $year ='';
	                    if ($release_year) { 
                            $year =  '('.esc_attr($release_year).')'; 
                        }
						$output .= '<h3 class="movie-title"><a href="'.get_permalink().'">'. get_the_title().$year.'</a></h3>';
						if ($movie_type) { 
							$output .= '<p class="genry">'.esc_attr($movie_type).'</p>';
						}
					$output .= '</div>';//trailer-info-block

                    if (function_exists('themeum_wp_rating')) {
						$output .= '<div class="count-rating pull-right">';
						$output .= '<span>'.themeum_wp_rating(get_the_ID(),'single').'</span>';
						$output .= '</div>';
                    }

				$output .= '</div>'; //trailer-info
				$output .= '</div>'; //trailer	
				$output .= '</div>'; //trailer-item
    		} else {
				$output .= '<div class="trailer-item subleading col-sm-3">';
				$output .= '<div class="trailer">';

					foreach( $movie_trailer_info as $key=>$value ){
						if ($key==0) {
							if(isset($value["themeum_video_trailer_image"])){
							$image = $value["themeum_video_trailer_image"];
							$trailer_image = wp_get_attachment_image_src($image[0], 'moview-trailer');
							$output .= '<div class="trailer-image-wrap">';
								if ($trailer_image[0]!='') {
								$output .= '<img class="img-responsive" src="'.esc_url($trailer_image[0]).'" alt="'.esc_html__('trailers', 'themeum-core').'">';
								} else { 
									$output .= '<div class="trailer-smail-no-video"></div>';
								}
								$output .= '<a class="play-video" href="'.$value["themeum_video_link"].'" data-type="'.esc_attr($value["themeum_video_source"]).'">';
									$output .= '<i class="play-icon themeum-moviewplay"></i>';
								$output .= '</a>';
								$output .= '<div class="content-wrap">';
									$output .= '<div class="video-container">';
										$output .= '<span class="video-close">x</span>';
									$output .= '</div>';
								$output .= '</div>'; //content-wrap
							$output .= '</div>'; //trailer-image-wrap
							}
						}
					}
				$output .= '<div class="trailer-info trailers-info">';
					$output .= '<div class="trailer-info-block">';
						$year ='';
						if ($release_year) { 
                            $year =  '('.esc_attr($release_year).')'; 
                        }
						$output .= '<h3 class="movie-title"><a href="'.get_permalink().'">'. get_the_title().$year.'</a></h3>';
						if ($movie_type) { 
							$output .= '<p class="genry">'.esc_attr($movie_type).'</p>';
						}
					$output .= '</div>';//trailer-info-block
				$output .= '</div>'; //trailer-info
				$output .= '</div>'; //trailer	
				$output .= '</div>'; //trailer-item
    		}
    	$counter++;	
	    endforeach;
	    wp_reset_postdata();   
	    $output .= '</div>';//row
		$output .= '</div>';//trailers-videos					
	}  

	return $output;

});


//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" => esc_html__("Trailers Video", 'themeum-core'),
		"base" => "themeum_trailer_video",
		'icon' => 'icon-thm-video_post',
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
				"value" => "5",
				),						

			array(
				"type" => "dropdown",
				"heading" => esc_html__("OderBy", 'themeum-core'),
				"param_name" => "order_by",
				"value" => array('Date'=>'date','Title'=>'title','Modified'=>'modified','Author'=>'author','Random'=>'rand'),
				),	

			array(
				"type" => "dropdown",
				"heading" => esc_html__("Show Date", 'themeum-core'),
				"param_name" => "show_date",
				"value" => array('YES'=>'yes','NO'=>'no'),
				),

			array(
				"type" => "dropdown",
				"heading" => esc_html__("Order", 'themeum-core'),
				"param_name" => "order",
				"value" => array('DESC'=>'DESC','ASC'=>'ASC'),
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