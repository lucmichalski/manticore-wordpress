<?php
define('WP_USE_THEMES', false);
$parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
$wp_load = $parse_uri[0].'wp-load.php';
require_once($wp_load);

$output = '';
$data_view = '';
$id_list = $args = array();

if(isset($_POST['raw_data'])){
	$args = array(
				's'	=> $_POST['raw_data'],
				'posts_per_page' => 10
		);
	$data_view = $_POST['raw_data'];
}

$search_data = new WP_Query($args);
if($search_data->have_posts()):
	while ($search_data->have_posts()): $search_data->the_post();
		$id_list[] = get_the_ID();
	endwhile;
	wp_reset_postdata();
endif;


$args = array();
if(isset($_POST['category'])){
	if($_POST['category']!=''){

		if($_POST['category']=='movie'){
			$args2 = array( 'post_type' => 'movie', 'post__in' => $id_list );
			if(!empty($id_list)){ 
				$args = array_merge($args,$args2);
			}
		}

		if($_POST['category']=='celebrity') {
			$args2 = array( 'post_type' => 'celebrity', 'post__in' => $id_list );
			if(!empty($id_list)){ 
				$args = array_merge($args,$args2);
			}
		}

	}
}



$search_data = new WP_Query($args);

if($search_data->have_posts()):
	$output .= '<ul>';
	while ($search_data->have_posts()): $search_data->the_post();
		$the_title = str_ireplace( $data_view ,"<span>".$data_view."</span>", get_the_title() );
		$output .= '<li><a href="'.get_permalink().'"><i class="themeum-moviewsearch"></i> '.$the_title.'</a></li>';
	endwhile;
	$output .= '</ul>';
	wp_reset_postdata();
endif;

//echo $_POST['category'];
echo $output;