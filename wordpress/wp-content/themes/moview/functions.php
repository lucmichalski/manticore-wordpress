<?php
define('MOVIEW_NAME', wp_get_theme()->get( 'Name' ));
define('MOVIEW_CSS', get_template_directory_uri().'/css/');
define('MOVIEW_JS', get_template_directory_uri().'/js/');

/*-----------------------------------------------------
 * 				Moview Options
*----------------------------------------------------*/
if(!function_exists('moview_options')):
	function moview_options($arg) {
		global $themeum_options;
		if (isset($themeum_options[$arg])) {
			return $themeum_options[$arg];
		} else {
			return false;
		}
	}
endif;

if(!function_exists('moview_options_url')):
	function moview_options_url($arg,$arg2) {
		global $themeum_options;
		if (isset($themeum_options[$arg][$arg2])) {
			return $themeum_options[$arg][$arg2];
		} else {
			return false;
		}
	}
endif;

//widget
require_once( get_template_directory()  . '/lib/widgets/themeum_about_widget.php');
require_once( get_template_directory()  . '/lib/widgets/image_widget.php');
require_once( get_template_directory()  . '/lib/widgets/double-ads.php');

/*-------------------------------------------*
 *				Register Navigation
 *------------------------------------------*/
register_nav_menus( array(
	'primary' => esc_html__( 'Primary Menu', 'moview' ),
	'mainmenu' => esc_html__( 'Main Menu', 'moview' ),
	'footernav' => esc_html__( 'Footer Menu', 'moview' )
) );

/*-------------------------------------------*
 *				title tag
 *------------------------------------------*/


add_action( 'after_setup_theme', 'moview_slug_setup' );
if(!function_exists( 'moview_slug_setup' )):
    function moview_slug_setup() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-formats', array( 'link', 'quote' ) );
    }
endif;

/*-------------------------------------------*
 *				navwalker
 *------------------------------------------*/
//Main Navigation
require_once( get_template_directory()  . '/lib/menu/admin-megamenu-walker.php');
require_once( get_template_directory()  . '/lib/menu/meagmenu-walker.php');
require_once( get_template_directory()  . '/lib/menu/mobile-navwalker.php');
//Admin mega menu
add_filter( 'wp_edit_nav_menu_walker', function( $class, $menu_id ){
	return 'Themeum_Megamenu_Walker';
}, 10, 2 );


/*-------------------------------------------*
 *				Startup Register
 *------------------------------------------*/
require_once( get_template_directory()  . '/lib/main-function/themeum-register.php');


/*-------------------------------------------------------
 *			Themeum Core
 *-------------------------------------------------------*/
require_once( get_template_directory()  . '/lib/main-function/themeum-core.php');


/*-------------------------------------------------------
 *          Themeum Login Registration
 *-------------------------------------------------------*/
require_once( get_template_directory()  . '/lib/main-function/ajax-login.php');
require_once( get_template_directory()  . '/lib/main-function/login-registration.php');


/*-----------------------------------------------------
 * 				Custom Excerpt Length
 *----------------------------------------------------*/

if(!function_exists('moview_excerpt_max_charlength')):
	function moview_excerpt_max_charlength($charlength) {
		$excerpt = get_the_excerpt();
		$charlength++;

		if ( mb_strlen( $excerpt ) > $charlength ) {
			$subex = mb_substr( $excerpt, 0, $charlength - 5 );
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
			if ( $excut < 0 ) {
				return mb_substr( $subex, 0, $excut );
			} else {
				return $subex;
			}

		} else {
			return $excerpt;
		}
	}
endif;

/*-------------------------------------------*
 *				woocommerce support
 *------------------------------------------*/

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

/*-----------------------------------------------------
 * 				Review List
 *----------------------------------------------------*/
add_action('after_switch_theme', 'moview_review_setup_options');
function moview_review_setup_options(){
  if( get_option('review_page_id') == "" ){
        $register_page = array(
          'post_title'    => 'Reviews',
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'page',
          'page_template' => 'page-reviews.php'
        );
        $post_id = wp_insert_post( $register_page );
        add_option( 'review_page_id', $post_id ); 
    }
}

define ( 'BP_AVATAR_THUMB_WIDTH', 100 );
define ( 'BP_AVATAR_THUMB_HEIGHT', 100 );
define ( 'BP_AVATAR_FULL_WIDTH', 150 );
define ( 'BP_AVATAR_FULL_HEIGHT', 150 );
define ( 'BP_AVATAR_ORIGINAL_MAX_WIDTH', 640 );

/*-----------------------------------------------------
 * 				Custom body class
 *----------------------------------------------------*/
add_filter( 'body_class', 'moview_body_class' );
function moview_body_class( $classes ) {
     $menu_style = 'none';
     if ( moview_options('boxfull-en') ) {
      $layout = esc_attr(moview_options('boxfull-en'));
     }else{
        $layout = 'fullwidth';
     }
     $classes[] = $layout.'-bg'; 
	return $classes;
}

# Shop Page Column Set
function loop_columns() {
return 3; 
}
add_filter('loop_shop_columns', 'loop_columns');



