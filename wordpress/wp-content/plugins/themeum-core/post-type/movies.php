<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Admin functions for the Event post type
 *
 * @author 		Themeum
 * @category 	Admin
 * @package 	Moview
 * @version     1.0
 *-------------------------------------------------------------*/

/**
 * Register post type Movies
 *
 * @return void
 */

function themeum_moview_post_type_course()
{
	$labels = array( 
		'name'                	=> _x( 'Movies', 'Movies', 'themeum-core' ),
		'singular_name'       	=> _x( 'Movie', 'Movie', 'themeum-core' ),
		'menu_name'           	=> __( 'Movies', 'themeum-core' ),
		'parent_item_colon'   	=> __( 'Parent Movie:', 'themeum-core' ),
		'all_items'           	=> __( 'All Movies', 'themeum-core' ),
		'view_item'           	=> __( 'View Movie', 'themeum-core' ),
		'add_new_item'        	=> __( 'Add New Movie', 'themeum-core' ),
		'add_new'             	=> __( 'New Movie', 'themeum-core' ),
		'edit_item'           	=> __( 'Edit Movie', 'themeum-core' ),
		'update_item'         	=> __( 'Update Movie', 'themeum-core' ),
		'search_items'        	=> __( 'Search Movie', 'themeum-core' ),
		'not_found'           	=> __( 'No article found', 'themeum-core' ),
		'not_found_in_trash'  	=> __( 'No article found in Trash', 'themeum-core' )
		);

	$args = array(  
		'labels'             	=> $labels,
		'public'             	=> true,
		'publicly_queryable' 	=> true,
		'show_in_menu'       	=> true,
		'show_in_admin_bar'   	=> true,
		'can_export'          	=> true,
		'has_archive'        	=> false,
		'hierarchical'       	=> false,
		'menu_position'      	=> true,
		'menu_icon'				=> true,
		'supports'           	=> array( 'title','editor','thumbnail','comments'),
		//'taxonomies' 			=> array('post_tag')
		);

	register_post_type('movie',$args);

}

add_action('init','themeum_moview_post_type_course');


/**
 * View Message When Updated Movies
 *
 * @param array $messages Existing post update messages.
 * @return array
 */

function themeum_movie_update_message_course( $messages )
{
	global $post, $post_ID;

	$message['course'] = array(
		0 => '',
		1 => sprintf( __('Movie updated. <a href="%s">View Movie</a>', 'themeum-core' ), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.', 'themeum-core' ),
		3 => __('Custom field deleted.', 'themeum-core' ),
		4 => __('Movie updated.', 'themeum-core' ),
		5 => isset($_GET['revision']) ? sprintf( __('Movie restored to revision from %s', 'themeum-core' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Movie published. <a href="%s">View Movie</a>', 'themeum-core' ), esc_url( get_permalink($post_ID) ) ),
		7 => __('Movie saved.', 'themeum-core' ),
		8 => sprintf( __('Movie submitted. <a target="_blank" href="%s">Preview Movie</a>', 'themeum-core' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Movie scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Movie</a>', 'themeum-core' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Movie draft updated. <a target="_blank" href="%s">Preview Movie</a>', 'themeum-core' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

return $message;
}

add_filter( 'post_updated_messages', 'themeum_movie_update_message_course' );


/**
 * Register Movie Category Taxonomies
 *
 * @return void
 */

function themeum_movie_register_course_cat_taxonomy()
{
	$labels = array(
		'name'              	=> _x( 'Movie Categories', 'taxonomy general name', 'themeum-core' ),
		'singular_name'     	=> _x( 'Movie Category', 'taxonomy singular name', 'themeum-core' ),
		'search_items'      	=> __( 'Search Movie Category', 'themeum-core' ),
		'all_items'         	=> __( 'All Movie Category', 'themeum-core' ),
		'parent_item'       	=> __( 'Movie Parent Category', 'themeum-core' ),
		'parent_item_colon' 	=> __( 'Movie Parent Category:', 'themeum-core' ),
		'edit_item'         	=> __( 'Edit Movie Category', 'themeum-core' ),
		'update_item'       	=> __( 'Update Movie Category', 'themeum-core' ),
		'add_new_item'      	=> __( 'Add New Movie Category', 'themeum-core' ),
		'new_item_name'     	=> __( 'New Movie Category Name', 'themeum-core' ),
		'menu_name'         	=> __( 'Movie Category', 'themeum-core' )
		);

	$args = array(
		'hierarchical'      	=> true,
		'labels'            	=> $labels,
		'show_in_nav_menus' 	=> true,
		'show_ui'           	=> true,
		'show_admin_column' 	=> true,
		'query_var'         	=> true
		);

	register_taxonomy('movie_cat',array( 'movie' ),$args);
}

add_action('init','themeum_movie_register_course_cat_taxonomy');




/**
 * Register Movie Tag Taxonomies
 *
 * @return void
 */

function themeum_movie_register_tag_taxonomy()
{
	$labels = array(
		'name'              	=> _x( 'Movie Tags', 'taxonomy general name', 'themeum-core' ),
		'singular_name'     	=> _x( 'Movie Tag', 'taxonomy singular name', 'themeum-core' ),
		'search_items'      	=> __( 'Search Movie Tag', 'themeum-core' ),
		'all_items'         	=> __( 'All Movie Tag', 'themeum-core' ),
		'parent_item'       	=> __( 'Movie Parent Tag', 'themeum-core' ),
		'parent_item_colon' 	=> __( 'Movie Parent Tag:', 'themeum-core' ),
		'edit_item'         	=> __( 'Edit Movie Tag', 'themeum-core' ),
		'update_item'       	=> __( 'Update Movie Tag', 'themeum-core' ),
		'add_new_item'      	=> __( 'Add New Movie Tag', 'themeum-core' ),
		'new_item_name'     	=> __( 'New Movie Tag Name', 'themeum-core' ),
		'menu_name'         	=> __( 'Movie Tag', 'themeum-core' )
		);

	$args = array(
		'hierarchical'      	=> false,
		'labels'            	=> $labels,
		'show_in_nav_menus' 	=> true,
		'show_ui'           	=> true,
		'show_admin_column' 	=> true,
		'query_var'         	=> true
		);

	register_taxonomy('movie_tag',array( 'movie' ),$args);
}

add_action('init','themeum_movie_register_tag_taxonomy');

