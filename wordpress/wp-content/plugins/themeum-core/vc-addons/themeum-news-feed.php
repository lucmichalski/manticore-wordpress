<?php
add_shortcode( 'themeum_news_feed', function($atts, $content = null) {

	$title_color  	= '';
	$title_font  	= '';

	extract(shortcode_atts(array(
		'category' 							=> '',
		'number' 							=> '4',
		'title_color' 						=> '',
		'title_line' 						=> '',
		'class' 							=> '',
		'title_font' 						=> '',
        'order_by'  						=> 'date',                
        'show_date'  						=> 'yes',            
        'order'   							=> 'DESC',      
		), $atts));

 	global $post;
 	$output     = '';
	 $counter = 1;
	 
	 $t_color = 'style="color:'. esc_attr( $title_color ) .'"';
	 $t_font = 'style="font-size:'. esc_attr( $title_font ) .'px"';
 	
 	if (isset($category) && $category!='') {
 		$idObj 	= get_category_by_slug( $category );
 		
 		if (isset($idObj) && $idObj!='') {
			$idObj 	= get_category_by_slug( $category );
			$cat_id = $idObj->term_id;

			$args = array( 
		    	'category' => $cat_id,
		        'orderby' => $order_by,
		        'order' => $order,
		        'posts_per_page' => esc_attr($number),
		    );
		    $posts = get_posts($args);
 		}else{
 			$args = 0;
 		}
 		}else{
			$args = array( 
		        'orderby' => $order_by,
		        'order' => $order,
		        'posts_per_page' => esc_attr($number),
		    );
		    $posts = get_posts($args);
	 	}

	 	
	    if(count($posts)>0){
	    	$output .= '<div class="news-feed row">';
	    	foreach ($posts as $post): setup_postdata($post);
			if( $counter == 1 ){
			    $output .= '<div class="col-sm-6 leading-item">';
			        $output .= '<div class="news-feed-item">';
			            $output .= '<div class="news-feed-thumb">';
			                if ( has_post_thumbnail() ) {
			                	$img 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'moview-medium' );
				                $output .= '<a href="'. get_permalink() .'">';
				                $output .= '<img class="img-responsive" src="'.esc_url( $img[0] ).'" alt="'. get_the_title() .'" >';
				                $output .= '</a>';
				            }
			            $output .= '</div>'; // news-feed-thumb
			            $output .= '<div class="news-feed-info"><span class="meta-category">'.get_the_term_list( $post->ID, 'category', '', '' ).'</span>';
			                $output .= '<h3 class="news-feed-title" '.$t_font.'><a href="'. get_permalink() .'" '.$t_color.'>'. get_the_title() .'</a></h3>';
			                $output .= '<div class="news-feed-meta"><i class="themeum-moviewclock"></i><span class="meta-date">'. date_i18n( get_option( 'date_format' ), strtotime( get_the_date() ) ) .'</span>';
			                $output .= '</div>'; //news-feed-meta
			            $output .= '</div>'; //news-info
			        $output .= '</div>'; //news-feed-item
			    $output .= '</div>'; // leading-item
			}else{
			    $output .= '<div class="col-sm-6">';
			        $output .= '<div class="news-feed-item">';
			            $output .= '<div class="news-feed-thumb">';
			                if ( has_post_thumbnail() ) {
			                	$img 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'moview-thumb' );
				                $output .= '<a href="'. get_permalink() .'">';
				                $output .= '<img class="img-responsive" src="'.esc_url( $img[0] ).'" alt="'. get_the_title() .'" >';
				                $output .= '</a>';
				            }			                
			            $output .= '</div>'; // news-feed-thumb
			            $output .= '<div class="news-feed-info"><span class="meta-category">'.get_the_term_list( $post->ID, 'category', '', '' ).'</span>';
			                $output .= '<h3 class="news-feed-title" '.$t_font.'><a href="'. get_permalink() .'" '.$t_color.'>'. get_the_title() .'</a></h3>';
			                $output .= '<div class="news-feed-meta"><i class="themeum-moviewclock"></i><span class="meta-date">'. date_i18n( get_option( 'date_format' ), strtotime( get_the_date() ) ) .'</span>';
			                $output .= '</div>'; // news-feed-meta
			            $output .= '</div>'; // news-feed-info
			        $output .= '</div>'; // news-feed-item
			    $output .= '</div>'; // col-sm-6
			}

		$counter++;
		endforeach;
		wp_reset_postdata();
		$output .= '</div>';
		}  

	return $output;

});


//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" => esc_html__("News Feed", 'themeum-core'),
		"base" => "themeum_news_feed",
		'icon' => 'icon-thm-news-feeds',
		"class" => "",
		"description" => esc_html__("Widget News Feed", 'themeum-core'),
		"category" => esc_html__('Moview', 'themeum-core'),
		"params" => array(				
			

			array(
				"type" => "dropdown",
				"heading" => esc_html__("Category Filter", 'themeum-core'),
				"param_name" => "category",
				"value" => themeum_cat_list('category'),
				),		

			array(
				"type" => "textfield",
				"heading" => esc_html__("Number of items", 'themeum-core'),
				"param_name" => "number",
				"value" => "4",
				),	
				
			array(
				"type" => "colorpicker",
				"heading" => esc_html__("Title Color", 'themeum-core'),
				"param_name" => "title_color",
				"value" => "#000",
				),
			array(
				"type" => "textfield",
				"heading" => esc_html__("Title Font Size", 'themeum-core'),
				"param_name" => "title_font",
				"value" => "18",
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