<?php
add_shortcode( 'themeum_top_celebrities', function($atts, $content = null) {

	extract(shortcode_atts(array(
		'category' 						=> '',
		'number' 						=> '4',
		'class' 						=> '',
        'order'   						=> 'DESC',      
		), $atts));

	 	global $post;
	 	$output     = '';
	 	$posts= 0;
	  		

  		if (isset($category) && $category!='') {
			$args = array(
				'post_type'			=> 'celebrity',
		    	'tax_query' => array(
									'taxonomy' => 'celebrity_cat',
									'field'    => 'slug',
									'terms'    => $category,
								),
		    	'meta_key'			=> '_post_views_count',
		    	'orderby'    		=> 'meta_value',
		        'order' 			=> $order,
		        'posts_per_page' 	=> esc_attr($number),
		    );
		    $posts = get_posts($args);
 		}else{
			$args = array(
				'post_type'			=> 'celebrity',
				'meta_key'			=> '_post_views_count',
		    	'orderby'    		=> 'meta_value',
		        'order' 			=> $order,
		        'posts_per_page' 	=> esc_attr($number),
		    );
		    $posts = get_posts($args);
	 	}

	    if(count($posts)>0){
			$output .= '<div class="movie-celebrities">';
			foreach ($posts as $post): setup_postdata($post);
				if( has_post_thumbnail() ) {
					$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'moview-thumb' );
				}else {
					$img = get_template_directory().'/images/blank_celebraties.png';
				}
				$type = get_post_meta( get_the_ID(),'themeum_movie_type',true );

				$output .= '<div class="movie-celebrity clearfix">';
					$output .= '<a href="'. get_permalink() .'">';
						$output .= '<div class="pull-left movie-celebrity-thumb" style="background-image: url('. esc_url( $img[0] ) .'); "></div>';
					$output .= '</a>';
					$output .= '<div class="movie-celebrity-info">';
						$output .= '<div class="movie-celebrity-name">';
							$output .= '<a href="'. get_permalink() .'">'. get_the_title() .'</a>';
							$output .= '<small class="movie-celebrity-designation">'. esc_attr( $type ) .'</small>';
						$output .= '</div>';
					$output .= '</div> ';
				$output .= '</div>';

			endforeach;
			wp_reset_postdata();
			$output .= '</div>';
		}  

	return $output;

});


//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" => esc_html__("Top Celebrities", 'themeum-core'),
		"base" => "themeum_top_celebrities",
		'icon' => 'icon-thm-top-celebrities',
		"class" => "",
		"description" => esc_html__("Widget Top Celebrities", 'themeum-core'),
		"category" => esc_html__('Moview', 'themeum-core'),
		"params" => array(				

			

			array(
				"type" => "dropdown",
				"heading" => esc_html__("Category Filter", 'themeum-core'),
				"param_name" => "category",
				"value" => themeum_cat_list( 'celebrity_cat' ),
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