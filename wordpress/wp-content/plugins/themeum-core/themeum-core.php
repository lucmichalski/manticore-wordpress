<?php
/*
* Plugin Name: Themeum Core
* Plugin URI: http://www.themeum.com/item/core
* Author: Themeum
* Author URI: http://www.themeum.com
* License - GNU/GPL V2 or Later
* Description: Themeum Core is a required plugin for this theme.
* Version: 2.3
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// language
add_action( 'init', 'themeum_core_language_load' );
function themeum_core_language_load(){
    $plugin_dir = basename(dirname(__FILE__))."/languages/";
    load_plugin_textdomain( 'themeum-core', false, $plugin_dir );
}

if( !function_exists("themeum_cat_list") ){
    // List of Group
    function themeum_cat_list( $category ){
        global $wpdb;
        $sql = "SELECT * FROM `".$wpdb->prefix."term_taxonomy` INNER JOIN `".$wpdb->prefix."terms` ON `".$wpdb->prefix."term_taxonomy`.`term_taxonomy_id`=`".$wpdb->prefix."terms`.`term_id` AND `".$wpdb->prefix."term_taxonomy`.`taxonomy`='".$category."'";
        $results = $wpdb->get_results( $sql );

        $cat_list = array();
        $cat_list['All'] = 'themeumall';  
        if(is_array($results)){
            foreach ($results as $value) {
                $cat_list[$value->name] = $value->slug;
            }
        }
        return $cat_list;
    }
}

function themeum_get_movie_release_year(){
    global $wpdb;
    $sql = "SELECT DISTINCT `meta_value` FROM `".$wpdb->prefix."postmeta` WHERE `meta_key`='themeum_movie_release_year'";
    $results = $wpdb->get_results( $sql );
    return $results;
}

// Post Type List
include_once( 'post-type/celebrities.php' );
include_once( 'post-type/movies.php' );

// Metabox Include
include_once( 'post-type/meta_box.php' );
include_once( 'post-type/meta-box/meta-box.php' );
include_once( 'post-type/meta-box-group/meta-box-group.php' );


// Redux integration
global $themeum_options; 
if ( !class_exists( 'ReduxFramework' ) ) {
    include_once( 'lib/redux/framework.php' );
    include_once( 'lib/admin-config.php' );
    include_once( 'import-functions.php' );
}

// Registration
include_once( 'lib/registration.php' );

// shortcode lists
include_once( 'vc-addons/fontawesome-helper.php' );
include_once( 'vc-addons/fontmoview-helper.php' );
include_once( 'vc-addons/shortcode-helper.php' );
include_once( 'vc-addons/themeum-title.php' );
include_once( 'vc-addons/themeum-feature.php' );
include_once( 'vc-addons/themeum-spotlight.php' );
include_once( 'vc-addons/themeum-featured-movies.php' );
include_once( 'vc-addons/themeum-movies.php' );
include_once( 'vc-addons/themeum-latest-celebrities.php' );
include_once( 'vc-addons/themeum-trailers-video.php' );
include_once( 'vc-addons/themeum-top-celebrities.php' );
include_once( 'vc-addons/themeum-news-feed.php' );

// New Shortcode
include_once( 'vc-addons/themeum-movie-listing.php' );
include_once( 'vc-addons/themeum-celebrity-listing.php' );

include_once( 'imdb/imdb.class.php' );


/**
 * Movie Single Template
 *
 * @return string
 */
function themeum_core_movie_template($single_template) {
	global $post;
	if ($post->post_type == 'movie') {
		$single_template = dirname( __FILE__ ) . '/templates/movie-single.php';
	}
	return $single_template;
}
add_filter( "single_template", "themeum_core_movie_template" ) ;

/**
 * Celebrity Single Template
 *
 * @return string
 */
function themeum_core_celebrity_template($single_template) {
    global $post;
    if ($post->post_type == 'celebrity') {
        $single_template = dirname( __FILE__ ) . '/templates/celebrity-single.php';
    }
    return $single_template;
}
add_filter( "single_template", "themeum_core_celebrity_template" ) ;


// movie_cat taxonomy template
add_filter('template_include', 'themeum_taxonomy_movie_template');
function themeum_taxonomy_movie_template( $template ){
    if( is_tax('movie_cat') ){
        $template = dirname( __FILE__ ).'/templates/taxonomy-movie_cat.php';
    }
    return $template;
}

// celebrity_cat taxonomy template
add_filter('template_include', 'themeum_taxonomy_celebrity_template');
function themeum_taxonomy_celebrity_template( $template ){
    if( is_tax('celebrity_cat') ){
        $template = dirname( __FILE__ ).'/templates/taxonomy-celebrity_cat.php';
    }
    return $template;
}


// Add CSS for Frontend
add_action( 'wp_enqueue_scripts', 'themeum_core_style' );
if(!function_exists('themeum_core_style')):
    function themeum_core_style(){
        // CSS
        wp_enqueue_style('themeum-core',plugins_url('assets/css/themeum-core.css',__FILE__));
        wp_enqueue_style('owl-theme',plugins_url('assets/css/owl.theme.css',__FILE__));
        wp_enqueue_style('owl-carousel',plugins_url('assets/css/owl.carousel.css',__FILE__));
        wp_enqueue_style('meta-box-group-css',plugins_url('post-type/meta-box-group/group.css',__FILE__));
        // JS
        wp_enqueue_script('themeum-prettysocial',plugins_url('assets/js/jquery.prettySocial.min.js',__FILE__), array('jquery'));
        wp_enqueue_script('owl-carousel-min',plugins_url('assets/js/owl.carousel.min.js',__FILE__), array('jquery'));
        wp_enqueue_script('meta-box-group-js',plugins_url('post-type/meta-box-group/group.js',__FILE__), array('jquery'));
        wp_enqueue_script('themeum-core-js',plugins_url('assets/js/main.js',__FILE__), array('jquery'));
    }
endif;



// Woocommerce Product Tab
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) { 
    // Adds the new tab
    $tabs['test_tab'] = array(
        'title'     => __( 'Trailer', 'themeum-core' ),
        'priority'  => 50,
        'callback'  => 'woo_video_product_tab_content'
    );
    return $tabs;
}
function woo_video_product_tab_content() {
    // The new tab content
    $movie_trailer_info     = rwmb_meta('themeum_shop_info');

    if(is_array( $movie_trailer_info )){
        if(!empty( $movie_trailer_info )){
            if(( $movie_trailer_info["themeum_video_trailer_image"] != '' ) && ( $movie_trailer_info["themeum_video_link"] != '' )){

                $image = $movie_trailer_info["themeum_video_trailer_image"];
                $trailer_image   = wp_get_attachment_image_src($image, 'moview-medium');    ?>
                <div class="trailer-item leading">
                    <div class="trailer">
                        <div class="trailer-image-wrap">
                            <?php if ($trailer_image[0]!='') { ?>
                                <img class="img-responsive" src="<?php echo $trailer_image[0];?>" alt="<?php esc_html_e('trailers', 'themeum-core');?>">
                                <?php } else{ ?>
                                    <div class="trailer-no-video"></div>
                            <?php }?>
                            <a class="play-video" href="<?php echo $movie_trailer_info["themeum_video_link"];?>" data-type="<?php echo $movie_trailer_info["themeum_video_source"]; ?>">
                                <i class="play-icon themeum-moviewplay"></i>
                            </a>
                            <div class="content-wrap">
                                <div class="video-container">
                                    <span class="video-close">x</span>
                                </div>
                            </div>
                        </div> <!-- trailer-image-wrap -->
                        <div class="trailer-info trailers-info">
                            <div class="trailer-info-block">
                                <?php if ( has_post_thumbnail() && ! post_password_required() ) { ?>
                                    <?php the_post_thumbnail('moview-small', array('class' => 'img-responsive thumb-img')); ?>
                                <?php } //.entry-thumbnail ?>                                                           
                                <h4 class="movie-title"><?php echo esc_attr($movie_trailer_info["themeum_video_info_title"]);?></h4>
                            </div>
                        </div>
                    </div>
                </div> <!--//trailer-item-->
                <?php
            }
        }
    }
}


function beackend_theme_update_notice()
{
    wp_enqueue_style('woonotice',plugins_url('assets/css/woonotice.css',__FILE__));
}
add_action( 'admin_print_styles', 'beackend_theme_update_notice' );


