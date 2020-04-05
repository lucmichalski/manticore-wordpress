<?php 

add_action('after_switch_theme', 'moview_login_registration_setup_options');
function moview_login_registration_setup_options(){
    if( get_option('register_page_id') == "" ){
          $register_page = array(
            'post_title'    => 'Register',
            'post_content'  => '[custom_registration]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
          );
          $post_id = wp_insert_post( $register_page );
          add_option( 'register_page_id', $post_id ); 
      }
}



