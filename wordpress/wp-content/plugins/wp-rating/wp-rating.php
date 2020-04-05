<?php
/*
Plugin Name: WP Rating
Version: 1.4
Plugin URI: http://themeum.com
Description: A plug-in to add additional fields in the comment form.
Author: Themeum
Author URI: http://www.themeum.com
*/

// ---------------------------------------------
// Comments Desplay Input
// Add fields after default fields above the comment box, always visible
// ---------------------------------------------



// language
add_action( 'init', 'wp_rating_language_load' );
function wp_rating_language_load(){
    $plugin_dir = basename(dirname(__FILE__))."/languages/";
    load_plugin_textdomain( 'wp-rating', false, $plugin_dir );
}

add_action( 'comment_form_logged_in_after', 'additional_fields' );
//add_action( 'comment_form_after_fields', 'additional_fields' );
function additional_fields () {
	$post_id = get_the_ID();
	$user_id = get_current_user_id();
	$rating_signal = wp_rating_comments_exits( $post_id , $user_id );
	if( get_post_type($post_id)=='movie' ){
		if( $rating_signal != 'true' ){
			echo '<div class="col-sm-12 comment-form-rating">';
		 		echo '<span class="commentratingbox">';
					for( $i=1; $i <= 10; $i++ ){
						echo '<span class="commentrating"><input type="radio" name="rating" value="'. esc_attr( $i ) .'"/></span>';	
					}
				echo '</span>';
			echo '</div>';
		}else{
			echo '<div class="col-sm-12 comment-form-rating-error"><label>'. __('Already rated! To change ratting edit the comments.','wp-rating') . '</label></div>';
		}
	}
}





//Add an edit option in comment edit screen  
add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}
function extend_comment_meta_box ( $comment ) {


	$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

	if ( is_user_logged_in() ) {
		if( $rating != "" ){
		    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
		    
		    echo '<p>';
		        echo '<label for="rating">'._e( 'Rating: ','wp-rating' ).'</label>';
					echo '<span class="commentratingbox">';
					for( $i=1; $i <= 10; $i++ ) {
						echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. esc_attr( $i ) .'"';
						if ( $rating == $i ) echo ' checked="checked"';
						echo ' />'. $i .' </span>'; 
						}
					echo '</span>';
		    echo '</p>';
		}
	}
}



// ---------------------------------------------
// Save Comments
// Save the comment meta data along with comment
// ---------------------------------------------
add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {

	if ( is_user_logged_in() ) {

		$user_id = get_current_user_id();
		$comment = get_comment( $comment_id );
		$post_id = $comment->comment_post_ID;
		
		$rating_signal = wp_rating_comments_exits( $post_id , $user_id );

		if( $rating_signal != 'true' ){
			if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ){
				$rating = wp_filter_nohtml_kses($_POST['rating']);
				add_comment_meta( $comment_id, 'rating', $rating );
				
				// Update Post Meta (Rating Average Meta Update)
				global $wpdb;
				$data = $wpdb->get_results( $wpdb->prepare( "SELECT `meta_key`,`meta_value` FROM `".$wpdb->prefix."commentmeta` WHERE `comment_id` IN (SELECT `comment_ID` FROM `".$wpdb->prefix."comments` WHERE `comment_post_ID`='".$post_id."') AND `meta_key`='rating'", $post_id ) );
				$total_count = 0;
				$percent = 0;
				if(is_array($data)){
					if(!empty($data)){
						foreach ($data as $value) {
							if($value->meta_value != '' ){
								$total_count = (int)$total_count + (int)$value->meta_value;
							}
						}
					}
				}
				if( ( count($data) !=0 )&&( $total_count != 0 ) ){
					$percent = floor( $total_count/count($data) );
				}
				update_post_meta( $post_id,'rating', $percent );
				update_post_meta( $post_id,'rating_count', count($data) );
				$score = count($data)*$total_count;
				update_post_meta( $post_id,'rating_score', $score );
				
				// Add User Meta
				//add_user_meta( $user_id , '_user_ratting_data', $rating);
			}
		}

	}
}



// ---------------------------------------------
// Delete Comments
// Delete comment meta data from comment edit screen 
// ---------------------------------------------
add_action( 'deleted_comment', function( $comment_id ) {
    //delete_comment_meta( $comment_id, 'rating' );

	global $wpdb;
	$comment = get_comment( $comment_id );
	$post_ids = $comment->comment_post_ID;
	$data = $wpdb->get_results( $wpdb->prepare( "SELECT `meta_key`,`meta_value` FROM `".$wpdb->prefix."commentmeta` WHERE `comment_id` IN (SELECT `comment_ID` FROM `".$wpdb->prefix."comments` WHERE `comment_post_ID`='".$post_ids."') AND `meta_key`='rating'", $post_ids ) );
	$total_count = 0;
	$percent = 0;
	if(is_array($data)){
		if(!empty($data)){
			foreach ($data as $value) {
				if($value->meta_value != '' ){
					$total_count = (int)$total_count + (int)$value->meta_value;
				}
			}
		}
	}
	if( ( count($data) != 0 )&&( $total_count != 0 ) ){
		$percent = floor( $total_count/count($data) );
	}
	update_post_meta( $post_ids,'rating', $percent );
	update_post_meta( $post_ids,'rating_count', count($data) );
	$score = count($data)*$total_count;
	update_post_meta( $post_ids,'rating_score', $score );

	// Add User Meta
	//delete_user_meta( $user_id, '_user_ratting_data', '' );
} );


// ---------------------------------------------
// Update Comments
// Update comment meta data from comment edit screen 
// ---------------------------------------------
add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;

	if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ){
		$rating = wp_filter_nohtml_kses($_POST['rating']);
		update_comment_meta( $comment_id, 'rating', $rating );
	}else{
		delete_comment_meta( $comment_id, 'rating');
	}

	// Update Post Meta (Rating Average Update)
	global $wpdb;
	$comment = get_comment( $comment_id );
	$post_ids = $comment->comment_post_ID;
	$data = $wpdb->get_results( $wpdb->prepare( "SELECT `meta_key`,`meta_value` FROM `".$wpdb->prefix."commentmeta` WHERE `comment_id` IN (SELECT `comment_ID` FROM `".$wpdb->prefix."comments` WHERE `comment_post_ID`='".$post_ids."') AND `meta_key`='rating'", $post_ids ) );
	$total_count = 0;
	$percent = 0;
	if(is_array($data)){
		if(!empty($data)){
			foreach ($data as $value) {
				if($value->meta_value != '' ){
					$total_count = (int)$total_count + (int)$value->meta_value;
				}
			}
		}
	}
	if( ( count($data) != 0 )&&( $total_count != 0 ) ){
		$percent = floor( $total_count/count($data) );
	}
	update_post_meta( $post_ids,'rating', $percent );
	update_post_meta( $post_ids,'rating_count', count($data) );
	$score = count($data)*$total_count;
	update_post_meta( $post_ids,'rating_score', $score );
}


// ---------------------------------------------
// Display Comments 
// Add the comment meta (saved earlier) to the comment text 
// You can also output the comment meta values directly in comments template  
// ---------------------------------------------
add_filter( 'comment_text', 'modify_comment');
function modify_comment( $text ){

	$html = '<ul class="list-unstyled list-inline entry-rate-list">';

	$plugin_url_path = WP_PLUGIN_URL;

	$commentrating = get_comment_meta( get_comment_ID(), 'rating', true );
	if( $commentrating == '' ){ $commentrating = 0; }
	
	if( $commentrating != 0 ){
		for ($i=1; $i <=10 ; $i++) { 
			if( $commentrating >= $i ){
				$html .= '<li><i class="themeum-moviewstar"></i></li>';
			}else{
				$html .= '<li><i class="themeum-moviewstar-blank"></i></li>';
			}
		}
		$html .= '</ul>';
		$html .= '<div class="already-rated">'. esc_attr( $commentrating ) .' / 10</div>'; 
	}
	if( get_post_type( get_the_ID() )=='movie' ){
		$text = $text . $html;
	}
	return $text;	 
}

// Rating Exits or Not Checker
function wp_rating_comments_exits( $post_id , $user_id ){
	$comments = get_comments( 
						array( 
							'post_id' => $post_id, 
							'author__in' => array($user_id),
							'meta_query' => array(
						                    	array(
							                        'key' => 'rating',
							                        'value' => '',
							                        'compare' => '!='
						                    )
						                ),
							'status' => 'approve'
							) 
						);
	$rating_signal = 'false';
	if( is_array( $comments ) ){
		if ( !empty($comments) ) {
			foreach ($comments as $value) {
				$rating_signal = 'true';
			}
		}
	}

	return $rating_signal;
}

// Add CSS for Frontend
add_action( 'wp_enqueue_scripts', 'themeum_rating_style' );
if(!function_exists('themeum_rating_style')):
    function themeum_rating_style(){
        wp_enqueue_script('wp-rating',plugins_url('js/main.js',__FILE__), array('jquery'));
    }
endif;

// This Function returns the rating data directly to theme.
// Just call  themeum_wp_rating( POST_ID , TYPE )
// TYPE = 'single' or 'with_html' or 'data_only'
function themeum_wp_rating( $post_id , $arg ){
	if( $arg == 'single' ){
		$var = get_post_meta( $post_id, 'rating', true );
		if( $var=='' ){ $var = 0; }
		return $var;
	}
	elseif ( $arg == 'with_html' ) {
		$html_output = '';
		$var = (int)get_post_meta( $post_id, 'rating', true );
		if( $var=='' ){ $var = 0; }

		$html_output .= '<div class="movie-rating">';
		for ($i=0; $i <=9 ; $i++) { 
			if( $i < $var ){
				$html_output .= '<span class="themeum-moviewstar"></span>';
			}else{
				$html_output .= '<span class="themeum-moviewstar-blank"></span>';
			}
		}
		$html_output .= '</div>';

		return $html_output;
	}
	elseif ( $arg == 'data_only' ) {
		$html_output = '';
		$html_output .= '<div class="movie-rating">';
		for ($i=0; $i <=9 ; $i++) { 
			if( $i < $post_id ){
				$html_output .= '<span class="themeum-moviewstar"></span>';
			}else{
				$html_output .= '<span class="themeum-moviewstar-blank"></span>';
			}
		}
		$html_output .= '</div>';
		return $html_output;
	}
}