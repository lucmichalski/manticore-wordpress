<?php
/**
 * Theta functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package Theta
 * @since Theta 1.0
 */

define( 'THETA_VERSION', '1.3.0' );

if ( !defined('THEME_COLOR')) {
    define( 'THEME_COLOR', '#F55145' );
}

if ( !class_exists( 'Mobile_Detect' ) ) {	
	get_template_part('/inc/Mobile_Detect');
}

$detect = new Mobile_Detect;	

if ( $detect->isMobile() ) {	
	define( 'ISMOBILE', true );
}


function theta_setup(){
	global $content_width;

	$lang = get_template_directory(). '/languages';
	load_theme_textdomain('theta', $lang);

	get_template_part('/inc/breadcrumb-trail');
	
	if ( !function_exists('theta_themes_pro')) {  
		get_template_part('/inc/customizer-pro/class-customize');
	}
	
	get_template_part('/inc/tgm-plugin');
	
	if ( class_exists( 'Kirki' ) ) {
		get_template_part('/inc/theta-config');	
	}
	get_template_part('/inc/customizer');
	
	if ( !function_exists('theta_about_page')) { 	
		get_template_part('/inc/ct-about-page/about-page');	
	}
	

	if ( function_exists( 'add_image_size' ) ) add_theme_support( 'post-thumbnails' );
	if ( function_exists( 'add_image_size' ) ) { 
		add_image_size( 'theta-thumb', 360, 186 );
	}

	$args = array();
	$header_args = array( 
	    'default-image'          => '',
		'default-repeat' => 'no-repeat',
        'default-text-color'     => '00BAE1',
		'url'                    => '',
        'width'                  => 1920,
        'height'                 => 89,
        'flex-height'            => true
     );
	$background_args = array(
		'default-color' => 'f7f8f8',
	);	 
	
		 
	add_theme_support( 'custom-background', $background_args );
	add_theme_support( 'custom-header', $header_args );
	add_theme_support( 'automatic-feed-links' );//
	add_theme_support( 'woocommerce' );

	add_theme_support( 'custom-logo', array(
			   'height'      => 50,
			   'width'       => 50,
			   'flex-width' => true,
			) );
	add_theme_support('nav_menus');
	add_theme_support( "title-tag" );//
	//register menus
	register_nav_menus(
					   array(
						'header-menu' => __( 'Header Menu', 'theta' ) ,
					   	'footer-menu' => __( 'Footer Menu', 'theta' )	
					   )
					   );					   
					   
	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );
	
	
	
	add_editor_style("css/editor-style.css");
	if ( !isset( $content_width ) ) $content_width = 1170;	
}
add_action( 'after_setup_theme', 'theta_setup' );


function add_admin_scripts(){
	wp_enqueue_style('theta-admin',  get_template_directory_uri() .'/css/admin.css', false, '1.0.0');
}
//add_action('admin_head', 'add_admin_scripts');
add_action( 'customize_controls_enqueue_scripts', 'add_admin_scripts', 9999 );
	
function theta_custom_scripts()
{
	$theme_info = wp_get_theme();
	
	wp_enqueue_style('theta-base',  get_template_directory_uri() .'/css/base.css', false, $theme_info->get( 'Version' ), false);
	wp_enqueue_style('font-awesome',  get_template_directory_uri() .'/css/font-awesome.min.css', false,"4.6.3", false);	

	
	if (class_exists('Woocommerce')) {  
		wp_enqueue_style('theta-Woocommerce',  get_template_directory_uri() .'/woocommerce/theta-woocommerce.css', false,$theme_info->get( 'Version' ), false);
	}	
	
	wp_enqueue_style('bootstrap',  get_template_directory_uri() .'/css/bootstrap.min.css', false,"3.3.7", false);
		
	$arry3 = apply_filters( 'css_filter_array', array('bootstrap') );	
	wp_enqueue_style('theta-style', get_stylesheet_uri(),$arry3, $theme_info->get( 'Version' ), false );	
			
	wp_enqueue_style(
		'theta-custom-style',
		get_template_directory_uri() . '/css/custom_script.css'
	);

	$custom_css = '';
	$color = THEME_COLOR;	
	//header css
 	if ( get_theme_mod( 'theme_color',THEME_COLOR) ){
		$color = get_theme_mod( 'theme_color',THEME_COLOR) ;
	}	
				
	$color_hover = theta_change_color($color,0.8);//#f7746a
	$color_rbg = theta_hex2rgb( $color );

	$header_color_rbg = theta_hex2rgb( '#ffffff' );

	$custom_css .='.s-color{color:'.$color.';}
		.s-bg-color{color:'.$color.';}	
		.s-hover-color{color:'.$color_hover.';}
		.single-content a{color:'.$color.'; text-decoration:none;}
		.single-content a:hover{color:'.$color_hover.';}
		#footer a{color:#DBDBDB;}
		#footer .copyright a{color:'.$color_hover.';}
		a:hover,
		a:active,
		a:focus{text-decoration: none;color:'.$color_hover.';}
		.cbutton--effect-jagoda.share-twitter::before, 
		.cbutton--effect-jagoda.share-twitter::after{box-shadow:0 0 0 1px rgba('.$color_rbg[0].','.$color_rbg[1].','.$color_rbg[2].',0.5)}';

	if ( has_custom_logo() ){
		$custom_css .= "@media screen and (max-width:1025px){
			.theta-logo-text{ display:none;}
			.theta-logo{ margin-left:20px;}	
			
		}";
	 }else{
		$custom_css .= "@media screen and (max-width:1025px){
			.blog-description{ display:none;}
			.theta-logo-text{ margin-top:5px;}	
		}";
		
	}
	
	if ( get_header_textcolor() !='' && get_header_textcolor()!= '2C2C2C' ){
	
		$custom_css .='.theta-logo-text .blog-name,.theta-logo-text .blog-description,.theta-menu li>a,.theta-menu ul.menu li.menu-item-has-children ul li.menu-item-has-children a:after, .theta-menu ul.menu li.menu-item-has-children a:after,.theta-search a.search-icon{color:#'.esc_attr(get_header_textcolor()).';}';	

	}
	
	if( get_theme_mod( 'fixed_header',1) ){
	  $custom_css .= 'header#header.changeh a{color:#'.esc_attr(get_header_textcolor()).';}
		header#header.changeh ul li ul a{color:#515151;}
		header#header.changeh a:hover{color:'.$color_hover.';text-decoration:none;}	
		header.changeh #theta-top-search span.theta-close-search-field{ color:'.$color.';}
		.warp{ padding-top:74px;}';
	}	
	
	$header_background_color = esc_attr(get_theme_mod( 'header_background_color','#000000'));
		
	$header_background_opacity = get_theme_mod( 'header_background_opacity',0.5);
	
	$header_color_rbg = theta_hex2rgb( $header_background_color );
	$custom_css .= 'header.changeh {  background-color: rgba('.$header_color_rbg[0].','.$header_color_rbg[1].','.$header_color_rbg[2].','.$header_background_opacity.');}';
		
	// Has the text been hidden?
	if ( ! display_header_text() ){	
		$custom_css .=  '.theta-logo-text{ display:none;}';
	}
	
		
	if ( get_background_color() !=''  ){
		$custom_css .='html body{background-image:none;}';	
	}	
	$header_image = get_header_image();	
	//echo '-----888-------';		
	if ( ! empty( $header_image ) ){

		$custom_css .='header {
			background-image: url('.esc_url($header_image).');	background-repeat: no-repeat;background-position: 50% 50%;	-webkit-background-size: cover;	-moz-background-size:  cover;
			-o-background-size:      cover;	background-size:         cover;
		}
		.main-nav,.nav-wrap ul.menu li.menu-item-has-children { background-color:transparent;}
		@media screen and (min-width: 59.6875em) {
			body:before {
				background-image: url('.esc_url($header_image).');	background-repeat: no-repeat;background-position: 100% 50%;	-webkit-background-size: cover;	-moz-background-size: cover;
				-o-background-size:  cover;	background-size:   cover;		border-right: 0;
			}
		}';	

	}		
	//siderbar			
	if( get_theme_mod( 'siderbar_layout','right-sidebar') == 'left-sidebar'){$custom_css .= '.single-content{float: right;    padding-left: 40px;
    border-left-width: 1px;
    border-left-style: solid;
    border-left-color: #E1E1E1; border-right:none;}.theta-article-content .sidebar{float: left;}.theta-article-content{padding-top: 20px;}';}
	if( get_theme_mod( 'siderbar_layout','right-sidebar') == 'no-sidebar'){$custom_css .= '.single-content{ width:100%;border-right:none;}';}
	
	
    if( !get_theme_mod( 'enable_article_info',1) ){							 
		$custom_css .= '.blog-article-content{ margin-top:25px;}';
    }
	
	if ( defined( 'ISMOBILE' ) && ISMOBILE ) {
		$custom_css .='
			header.changeh #theta-top-search{ margin-top:-2px;}	
			header.fixed .warp {
				padding-top: 0px;
			}
		
			.header-wrap{ width: auto;}
			.theta-menu .menu-icon{
				color:'.$color.';
				border:'.$color.' 1px solid;
				background:transparent;
				cursor:pointer;
				outline:none;
				font-size:18px; 
				padding: 5px 5px 5px 4px;
				margin: 15px 10px 0 0;
				border-radius:3px;
			}	
			.theta-menu .menu-icon .icon-menu{font-size:24px; }
			.theta-menu .menu-icon:hover{background-color: rgba('.$color_rbg[0].','.$color_rbg[1].','.$color_rbg[2].',0.1);}
				
			.theta-menu .menu-icon i{-webkit-transition:all ease 0.4s;-moz-transition:all ease 0.4s;-o-transition:all ease 0.4s;transition:all ease 0.4s}
			
			.menu-icon{display:block;}
		
			.theta-menu .menu-icon .icon-close{display:none; padding: 5px 0;font-size:14px;}
			.theta-menu .menu-icon .icon-menu{display:block;}
		
			header .header-wrap .theta-menu ul.menu span{display:none}
				
			.theta-logo-img img{ width:40px; height:auto}
			
			header .header-wrap .theta-menu ul.menu li {
			height: 64px;
			}
			.header{
			height: 64px;
			}
			header .header-wrap .theta-menu ul.menu li {
			padding-top: 18px;
			margin-right:5px;
			}	
			#header a{color:'.$color.';}
			.ct_video a.btn{   margin: 10px 15px 0 15px;}
			.ct_testimonials_list {   padding-bottom: 80px;}
			.ct_testimonials_text h1 {   padding-top: 100px;}	
			
			.single-content {
				float: none;
				padding-right: 0px;
				border:none;
			}			
			#gotoTop {   bottom: 50px;   margin-right: 20px;}
			
		
			.theta-menu .menu-icon {
				color: '.$color.';
				border: '.$color.' 1px solid;
			}
		';
	}	
	
			
    wp_add_inline_style( 'theta-custom-style', $custom_css );		

	wp_enqueue_script('bootstrap', get_template_directory_uri().'/js/bootstrap.min.js', array( 'jquery' ), '3.3.7', true );				
	wp_enqueue_script('theta-main', get_template_directory_uri().'/js/main.js', array( 'jquery' ),$theme_info->get( 'Version' ), true );
	
	wp_add_inline_script( 'theta-main', theta_script_method() );				
}

add_action( 'wp_enqueue_scripts', 'theta_custom_scripts' );



function theta_script_method() {	
	$custom_js = 'jQuery(document).ready(function($){';

	$custom_js .= 'var height = jQuery(window).height();
	var width = jQuery(window).width();';

	
	if(get_theme_mod( 'enable_loader',1) ){	
	   $custom_js .= " 			$(window).on('load', function() { // makes sure the whole site is loaded 
			  $('#status').fadeOut(); // will first fade out the loading animation 
			  $('#preloader').delay(350).fadeOut('slow'); // will fade out the white DIV that covers the website. 
			  $('body').delay(350).css({'overflow':'visible'});
			});			";
	}


	$custom_js .= '});';
	

	return $custom_js;
}


function theta_better_comments($comment, $args, $depth)
{

?>	
 
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
        <article id="comment-<?php comment_ID(); ?>" class="comment-body">
            <div class="comment_avatar">
                <?php echo get_avatar($comment, $size = '45'); ?>
            </div>

            <div class="comment_postinfo">
                <?php printf(__('<cite class="fn">%s</cite> <span class="says"> on </span>','theta'), get_comment_author_link()) ?>
                <span class="comment_date"><?php printf(__('%1$s at %2$s','theta'), get_comment_date(),  get_comment_time()) ?></span><?php edit_comment_link(__('(Edit)','theta'),'  ','') ?>
            </div> <!-- .comment_postinfo -->

            <div class="comment_area">				
                <div class="comment-content clearfix">
                	<?php if ($comment->comment_approved == '0') : ?>
                     	<em><?php esc_html_e('Your comment is awaiting moderation.','theta') ?></em>
                     	<br />
                    <?php endif; ?>
                    
                    <?php comment_text() ?>
                    
                    
                    <div class="reply-container">
                        <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
                    </div>
                      
                </div> <!-- end comment-content-->
            </div> <!-- end comment_area-->
        </article> <!-- .comment-body -->
        
<?php
}


/*
add widgets to wp-admin
*/
function theta_widgets_init() {

	register_sidebar( array(
		'name' => __('Sidebar','theta'),
		'id' => 'sidebar',
		'before_widget' => '<div class="sidebar-section"><ul class="blog-category">
',
		'after_widget' => '</ul></div> <!-- end .sidebar-section -->',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	) );
	
	register_sidebar( array(
		'name' => __('woocommerce Sidebar','theta'),
		'id' => 'sidebar-woocommerce',
		'before_widget' => '<div class="sidebar-section"><ul class="blog-category">
',
		'after_widget' => '</ul></div> <!-- end .sidebar-section -->',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	) );	


}
add_action( 'widgets_init', 'theta_widgets_init' );



/* this function gets thumbnail from Post Thumbnail or Custom field or First post image */
if ( ! function_exists( 'theta_get_thumbnail' ) ) {
	function theta_get_thumbnail($post_id)
	{
		if(has_post_thumbnail())
		{
			
			$ct_post_thumbnail_fullpath=wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), "Full");
			$thumb_array['fullpath'] = $ct_post_thumbnail_fullpath[0];
		
		}else{
			$post_content = get_post($post_id)->post_content;
			$thumb_array['fullpath'] = theta_catch_that_image($post_content);
		}	
		
		if($thumb_array['fullpath']=="" )
		{
			
			$thumb_array['fullpath'] = get_template_directory_uri()."/images/default.jpg";
		
		}		

		return $thumb_array;
		
	}
}

function theta_catch_that_image($post_content)
{
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches);
  if($output!='')  $first_img = $matches[1][0];

  return $first_img;
}



/**
 * Convert Hex Code to RGB   #FFFFFF -> 255 255 255
 * @param  string $hex Color Hex Code
 * @return array       RGB values
 
 theta_hex2rgb( '#FFFFFF')
 */
function theta_hex2rgb( $hex )
{
	if ( strpos( $hex,'rgb' ) !== FALSE )
	{
		$rgb_part = strstr( $hex, '(' );
		$rgb_part = trim($rgb_part, '(' );
		$rgb_part = rtrim($rgb_part, ')' );
		$rgb_part = explode( ',', $rgb_part );
		
		$rgb = array($rgb_part[0], $rgb_part[1], $rgb_part[2], $rgb_part[3]);
		
	}
	elseif( $hex == 'transparent' )
	{
		$rgb = array( '255', '255', '255', '0' );
	}
	else
	{
		$hex = str_replace( '#', '', $hex );	
		if( strlen( $hex ) == 3 )
		{
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		}
		else
		{
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		$rgb = array( $r, $g, $b );
	}

	return $rgb; // returns an array with the rgb values
}

/* user:
$colour = '#00A800';
$brightness = 0.9; // 90% brighter
$newColour = cts_change_color($colour,$brightness);
*/
function theta_change_color($hex, $percent) {
	// Work out if hash given
	$hash = '';
	if (stristr($hex,'#')) {
		$hex = str_replace('#','',$hex);
		$hash = '#';
	}
	/// HEX TO RGB
	$rgb = array(hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)));
	//// CALCULATE 
	for ($i=0; $i<3; $i++) {
		// See if brighter or darker
		if ($percent > 0) {
			// Lighter
			$rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
		} else {
			// Darker
			$positivePercent = $percent - ($percent*2);
			$rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
		}
		// In case rounding up causes us to go to 256
		if ($rgb[$i] > 255) {
			$rgb[$i] = 255;
		}
	}
	//// RBG to Hex
	$hex = '';
	for($i=0; $i < 3; $i++) {
		// Convert the decimal digit to hex
		$hexDigit = dechex($rgb[$i]);
		// Add a leading zero if necessary
		if(strlen($hexDigit) == 1) {
		$hexDigit = "0" . $hexDigit;
		}
		// Append to the hex string
		$hex .= $hexDigit;
	}
	return $hash.$hex;
}


function theta_get_typography( $option_name,$option_default= array() )
{
  $return = '';
	
  $value = get_theme_mod( $option_name, $option_default );

  if ( isset( $value['font-family'] ) ) {
	$return .= 'font-family: \''.$value['font-family'].'\', sans-serif;';
  }
  if ( isset( $value['variant'] ) ) {
	$return .= 'font-style:'.$value['variant'].';';  
  }

  if ( isset( $value['font-size'] ) ) {
	$return .= 'font-size:'.$value['font-size'].';';  
	//echo '<p>' . sprintf( esc_attr_e( 'Font Size: %s', 'theta-fullpage' ), $value['font-size'] ) . '</p>';
  }
  if ( isset( $value['line-height'] ) ) {
	$return .= 'line-height:'.$value['line-height'].';';  
	//echo '<p>' . sprintf( esc_attr_e( 'Line Height: %s', 'theta-fullpage' ), $value['line-height'] ) . '</p>';
  }
  if ( isset( $value['letter-spacing'] ) ) {
	$return .= 'letter-spacing:'.$value['letter-spacing'].';'; 
	//echo '<p>' . sprintf( esc_attr_e( 'Letter Spacing: %s', 'theta-fullpage' ), $value['letter-spacing'] ) . '</p>';
  }
  if ( isset( $value['color'] ) ) {
	$return .= 'color:'.$value['color'].';'; 
	//echo '<p>' . sprintf( esc_attr_e( 'Color: %s', 'theta-fullpage' ), $value['color'] ) . '</p>';
  }	
  if ( isset( $value['text-transform'] ) ) {
	$return .= 'text-transform:'.$value['text-transform'].';';  
  }  
  
  if ( isset( $value['text-transform'] ) ) {
	$return .= 'text-align:'.$value['text-align'].';';  
  }    
  
  return $return ; 
}

/*	
*	get background 
*	---------------------------------------------------------------------
*/
function theta_get_background($args,$opacity=1)
{
	$background = "";


	$rgb = theta_hex2rgb($args);
	$background .= "background-color:rgba(".$rgb[0].",".$rgb[1].",".$rgb[2].",".esc_attr($opacity).");";

	return $background;
}

