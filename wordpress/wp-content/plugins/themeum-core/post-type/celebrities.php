<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Admin functions for the Event post type
 *
 * @author 		Themeum
 * @category 	Admin
 * @package 	Varsity
 * @version     1.0
 *-------------------------------------------------------------*/

/**
 * Register post type Celebrity
 *
 * @return void
 */

function themeum_celebrity_post_type_event()
{
	$labels = array( 
		'name'                	=> _x( 'Celebrity', 'Celebrities', 'themeum-core' ),
		'singular_name'       	=> _x( 'Celebrity', 'Celebrity', 'themeum-core' ),
		'menu_name'           	=> __( 'Celebrities', 'themeum-core' ),
		'parent_item_colon'   	=> __( 'Parent Celebrity:', 'themeum-core' ),
		'all_items'           	=> __( 'All Celebrity', 'themeum-core' ),
		'view_item'           	=> __( 'View Celebrity', 'themeum-core' ),
		'add_new_item'        	=> __( 'Add New Celebrity', 'themeum-core' ),
		'add_new'             	=> __( 'New Celebrity', 'themeum-core' ),
		'edit_item'           	=> __( 'Edit Celebrity', 'themeum-core' ),
		'update_item'         	=> __( 'Update Celebrity', 'themeum-core' ),
		'search_items'        	=> __( 'Search Celebrity', 'themeum-core' ),
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
		'menu_position'      	=> null,
		'supports'           	=> array( 'title','editor','thumbnail'),
		//'taxonomies' 			=> array('celebrity_cat')
		);

	register_post_type('celebrity',$args);

}

add_action('init','themeum_celebrity_post_type_event');


/**
 * View Message When Updated Celebrity
 *
 * @param array $messages Existing post update messages.
 * @return array
 */

function themeum_celebritie_update_message_event( $messages )
{
	global $post, $post_ID;

	$message['event'] = array(
		0 => '',
		1 => sprintf( __('Celebrity updated. <a href="%s">View Celebrity</a>', 'themeum-core' ), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.', 'themeum-core' ),
		3 => __('Custom field deleted.', 'themeum-core' ),
		4 => __('Celebrity updated.', 'themeum-core' ),
		5 => isset($_GET['revision']) ? sprintf( __('Celebrity restored to revision from %s', 'themeum-core' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Celebrity published. <a href="%s">View Celebrity</a>', 'themeum-core' ), esc_url( get_permalink($post_ID) ) ),
		7 => __('Celebrity saved.', 'themeum-core' ),
		8 => sprintf( __('Celebrity submitted. <a target="_blank" href="%s">Preview Celebrity</a>', 'themeum-core' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Celebrity scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Celebrity</a>', 'themeum-core' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Celebrity draft updated. <a target="_blank" href="%s">Preview Celebrity</a>', 'themeum-core' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

return $message;
}

add_filter( 'post_updated_messages', 'themeum_celebritie_update_message_event' );


/**
 * Register Celebrity Category Taxonomies
 *
 * @return void
 */

function themeum_celebrity_register_event_cat_taxonomy()
{
	$labels = array(
		'name'              	=> _x( 'Celebrity Categories', 'taxonomy general name', 'themeum-core' ),
		'singular_name'     	=> _x( 'Celebrity Category', 'taxonomy singular name', 'themeum-core' ),
		'search_items'      	=> __( 'Search Category', 'themeum-core' ),
		'all_items'         	=> __( 'All Category', 'themeum-core' ),
		'parent_item'       	=> __( 'Parent Category', 'themeum-core' ),
		'parent_item_colon' 	=> __( 'Parent Category:', 'themeum-core' ),
		'edit_item'         	=> __( 'Edit Category', 'themeum-core' ),
		'update_item'       	=> __( 'Update Category', 'themeum-core' ),
		'add_new_item'      	=> __( 'Add New Category', 'themeum-core' ),
		'new_item_name'     	=> __( 'New Category Name', 'themeum-core' ),
		'menu_name'         	=> __( 'Celebrity Category', 'themeum-core' )
		);

	$args = array(	
		'hierarchical'      	=> true,
		'labels'            	=> $labels,
		'show_ui'           	=> true,
		'show_admin_column' 	=> true,
		'query_var'         	=> true
		);

	register_taxonomy('celebrity_cat',array( 'celebrity' ),$args);
}

add_action('init','themeum_celebrity_register_event_cat_taxonomy');





/**
 * Register Celebrity Tag Taxonomies
 *
 * @return void
 */

function themeum_celebrity_register_tag_taxonomy()
{
	$labels = array(
		'name'              	=> _x( 'Celebrity Tags', 'taxonomy general name', 'themeum-core' ),
		'singular_name'     	=> _x( 'Celebrity Tag', 'taxonomy singular name', 'themeum-core' ),
		'search_items'      	=> __( 'Search Tag', 'themeum-core' ),
		'all_items'         	=> __( 'All Tag', 'themeum-core' ),
		'parent_item'       	=> __( 'Parent Tag', 'themeum-core' ),
		'parent_item_colon' 	=> __( 'Parent Tag:', 'themeum-core' ),
		'edit_item'         	=> __( 'Edit Tag', 'themeum-core' ),
		'update_item'       	=> __( 'Update Tag', 'themeum-core' ),
		'add_new_item'      	=> __( 'Add New Tag', 'themeum-core' ),
		'new_item_name'     	=> __( 'New Tag Name', 'themeum-core' ),
		'menu_name'         	=> __( 'Celebrity Tag', 'themeum-core' )
		);

	$args = array(	
		'hierarchical'      	=> false,
		'labels'            	=> $labels,
		'show_ui'           	=> true,
		'show_admin_column' 	=> true,
		'query_var'         	=> true
		);

	register_taxonomy('celebrity_tag',array( 'celebrity' ),$args);
}

add_action('init','themeum_celebrity_register_tag_taxonomy');
