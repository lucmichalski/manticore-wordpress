<?php
/**
 * An example file demonstrating how to add all controls.
 *
 * @package     Kirki
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       3.0.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do not proceed if Kirki does not exist.
if ( ! class_exists( 'Kirki' ) ) {
	return;
}

//images url
$imagepath =  get_template_directory_uri() . '/images/'; 


/**
 * First of all, add the config.
 *
 * @link https://aristath.github.io/kirki/docs/getting-started/config.html
 */
Kirki::add_config(
	'theta_settings', array(
		'capability'  => 'edit_theme_options',
		'option_type' => 'theme_mod',
	)
);

/**
 * A proxy function. Automatically passes-on the config-id.
 *
 * @param array $args The field arguments.
 */
function config_kirki_add_field( $args ) {
	Kirki::add_field( 'theta_settings', $args );
}


 
//==========themes color ==========
/*
config_kirki_add_field(
	array(
		'type'        => 'radio-buttonset',
		'settings'    => 'theme_color_select',
		'label'       => __( 'Theme Color Select', 'theta' ),
		'section'     => 'colors',
		'default'     => 'red',
		'priority'    => 10,
		'choices'     => array(
			'red'  => esc_attr__( 'Red', 'theta' ),
			'Custom'  => esc_attr__( 'Custom', 'theta' ),		
		),
	)
);
*/


config_kirki_add_field(
	array(
		'type'        => 'color',
		'settings'    => 'header_background_color',
		'label'       => __( 'Header Background Color', 'theta' ),
		'section'     => 'colors',
		'default'     => '#000000',
		'priority'    => 10,	

	)
);

config_kirki_add_field(
	array(
		'type'        => 'slider',
		'settings'    => 'header_background_opacity',
		'label'       => __( 'Header Background Opacity', 'theta' ),
    	'section'	 => 'colors',
		'default'     => 0.5,
		'choices'     => array(
			'min'  => '0',
			'max'  => '1',
			'step' => '0.1',
		),
	)
);

if ( !function_exists('theta_theme_color')) { 
	function theta_theme_color(){
		config_kirki_add_field(
			array(
				'type'        => 'color',
				'settings'    => 'theme_color',
				'label'       => __( 'Theme Color Select', 'theta' ),
				'section'     => 'colors',
				'default'     => THEME_COLOR,
				'priority'    => 10,
				 'output'      => array(
					array(
						'element'  => '.theme-color,a:hover,.nav-wrap ul.menu > li.menu-item-has-children:hover:after,#search-toggle a.search-icon:hover, #search-toggle a.search-icon:focus,.mobile-nav-ico,.service_content_image a i.fa,.ct_team_bookmarks a,.tool-content-2 ul li i.fa,#footer a:hover,.service_content_image a:hover i.fa,.breadcrumb a:hover,.comment-reply-link, .form-submit input,.comment-reply-link,#gotoTop, .bttn,.ct_price_box,.ct_pricing_best .ct_pricing_plan i.fa,header .header-wrap .theta-menu ul.menu li a:hover,header .header-wrap .theta-menu ul.menu li.active a,header#header.changeh li.active a',
						'property' => 'color',
			
					),
					array(
						'element'  => '.theme-border-color,.nav-wrap ul li:hover > ul,section.ct_section_2 .feature_content_image a i,.ct_team_bookmarks a,.content-area h1#p-title,.comment-reply-link, .form-submit input,body .main-body #searchform,.page-nav-default span.current,#gotoTop,.bttn,.ct_price_box,header .header-wrap .theta-menu ul.menu li:hover',
						'property' => 'border-color',
					),
					array(
						'element'  => '.theme-background-color,.fhr,section.ct_section_2 .feature_content_image a i,.ct_team_bookmarks a:hover,.ct_tool_row hr,body .main-body #searchform #searchsubmit,.page-nav-default span.current,#gotoTop.hover,.bttn:hover,.bttn:active,.ct_panel_grid:hover .ct_price_box,section.ct_section_7 .ct_testimonials_text, section.ct_testimonials .rangle_r',
						'property' => 'background-color',
					),
				 ), 	
		
			)
		);
	}
	theta_theme_color();

}
//==========themes color end ==========  
  
//==========Theme Option ==========
/**
 * Add a panel.
 *
 * @link https://aristath.github.io/kirki/docs/getting-started/panels.html
 */
Kirki::add_panel(
	'theta_panel', array(
		'priority'    => 10,
		'title'       => esc_attr__( 'Theme Setting Panel', 'theta' ),
		'description' => esc_attr__( 'Contains sections for all kirki Theme Setting.', 'theta' ),
	)
);

/**
 * Add sections.
 */
Kirki::add_section( 'theme_option', array(
    'title'          => esc_attr__( 'General Theme Setting', 'theta' ),
    'panel'          => 'theta_panel',
    'priority'       => 160,
) );

Kirki::add_section( 'header_option', array(
    'title'          => esc_attr__( 'Header Setting', 'theta' ),
    'panel'          => 'theta_panel',
    'priority'       => 160,
) );

Kirki::add_section( 'footer_option', array(
    'title'          => esc_attr__( 'Footer Setting', 'theta' ),
    'panel'          => 'theta_panel',
    'priority'       => 160,
) );

Kirki::add_section( 'sidebar_option', array(
    'title'          => esc_attr__( 'Siderbar Setting', 'theta' ),
    'panel'          => 'theta_panel',
    'priority'       => 160,
) );

Kirki::add_section( '404_page_option', array(
    'title'          => esc_attr__( '404 page content Setting', 'theta' ),
    'panel'          => 'theta_panel',
    'priority'       => 160,
) );




//404_page_option
config_kirki_add_field(
	array(
		'type'        => 'editor',
		'settings'    => '404_page_content',
		'label'       => esc_attr__( '404 page Content', 'theta' ),
		'section'     => '404_page_option',
		'default'     =>'',
	)
); 




//header_option
config_kirki_add_field(
	array(
		'type'			 => 'switch', //toggle
		'settings'		 => 'box_header_center',
		'label'			 => __( 'Box Header Center', 'theta' ),
		'section'		 => 'header_option',
		'default'		 => 0,
		'priority'		 => 10,
	)
);

config_kirki_add_field(
	array(
		'type'			 => 'switch', //toggle
		'settings'		 => 'fixed_header',
		'label'			 => __( 'Fixed Header', 'theta' ),
		'section'		 => 'header_option',
		'default'		 => 1,
		'priority'		 => 10,
	)
);
config_kirki_add_field(
	array(
		'type'			 => 'switch', //toggle
		'settings'		 => 'enable_search',
		'label'			 => __( 'Enable search button in the head', 'theta' ),
		'section'		 => 'header_option',
		'default'		 => 1,
		'priority'		 => 10,
	)
);

  
//sidebar_option
config_kirki_add_field(
	array(
		'type'        => 'radio-image',
		'settings'    => 'siderbar_layout',
		'label'       => esc_html__( 'Siderbar Layout', 'theta' ),
		'section'     => 'sidebar_option',
		'default'     => 'right-sidebar',
		'priority'    => 10,
		'choices'     => array(
			'left-sidebar' => $imagepath . 'left-sidebar.png',
			'no-sidebar'  => $imagepath . 'no-sidebar.png',	
			'right-sidebar'   => $imagepath . 'right-sidebar.png',
	
		),
	)
); 
if (class_exists('Woocommerce')) {   
	config_kirki_add_field(
		array(
			'type'        => 'switch',
			'settings'    => 'enable_woocommerce_siderbar',
			'label'       => esc_attr__( 'Show woocommerce siderbar', 'theta' ),
			'section'     => 'sidebar_option',
			'default'		 => 1,
			'priority'		 => 10,
	
		)
	); 
}    




config_kirki_add_field(
	array(
		'type'			 => 'switch', //toggle
		'settings'		 => 'enable_breadcrumb_check',
		'label'			 => __( 'Enable the Breadcrumb Nav', 'theta' ),
		'section'		 => 'theme_option',
		'default'		 => 1,
		'priority'		 => 10,
	)
);
  

config_kirki_add_field(
	array(
		'type'			 => 'switch', //toggle
		'settings'		 => 'enable_return_top',
		'label'			 => __( 'Enable Return Top', 'theta' ),
		'section'		 => 'theme_option',
		'default'		 => 1,
		'priority'		 => 10,
	)
); 

config_kirki_add_field(
	array(
		'type'			 => 'switch', //toggle
		'settings'		 => 'enable_loader',
		'label'			 => __( 'Enable Loader', 'theta' ),
		'section'		 => 'theme_option',
		'default'		 => 0,
		'priority'		 => 10,
	)
); 
  

config_kirki_add_field(
	array(
		'type'        => 'switch',
		'settings'    => 'enable_article_info',
		'label'       => esc_attr__( 'Show article author information', 'theta' ),
		'description' => esc_attr__( 'Show article author info on page and single page', 'theta' ),
		'section'     => 'theme_option',
		'default'		 => 1,
		'priority'		 => 10,

	)
);
  
if ( !function_exists('theta_body_font')) { 
	function theta_body_font(){  
		config_kirki_add_field( array(
			'type'        => 'typography',
			'settings'    => 'body_typography',
			'label'       => esc_attr__( 'Select Body Font Family', 'theta' ),
			'section'     => 'theme_option',
			'default'     => array(
				'font-family'    => 'Roboto',
				'variant'        => 'regular',
				'font-size'      => '14px',
			),
			'priority'    => 10,
			'output'      => array(
				array(
					'element' => 'html, body',
				),
			),
		  )
		);  
	}
 	theta_body_font();
}
 
//footer_option 
config_kirki_add_field(
	array(
		'type'        => 'editor',
		'settings'    => 'footer_code',
		'label'       => esc_attr__( 'Footer Copyright', 'theta' ),
		'section'     => 'footer_option',
		'default'     => esc_attr__( '&copy; 2017 <a href="#">YourWeffbName.com</a>', 'theta' ),

	)
); 


  

//==========Theme Option end==========


/**
 * Configuration sample for the texas Customizer.
 */
function theta_configuration_sample() {

	$config[ 'color_back' ]		 = '#ffffff';
	$config[ 'color_accent' ]	 = '#008ec2';
	$config[ 'width' ]			 = '22%';

	return $config;
}

add_filter( 'kirki/config', 'theta_configuration_sample' );
