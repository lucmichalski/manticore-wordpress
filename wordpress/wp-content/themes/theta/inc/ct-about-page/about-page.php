<?php
/**
 * Lite Manager
 *
 * @package theta
 * @since 1.0.12
 */


/**
 * About page class
 */
require_once get_template_directory() . '/inc/ct-about-page/class-ct-about-page.php';

/*
* About page instance
*/
$config = array(
	// Menu name under Appearance.
	'menu_name'           => __( 'About theta', 'theta' ),
	// Page title.
	'page_name'           => __( 'About theta', 'theta' ),
	// Main welcome title
	/* translators: s - theme name */
	'welcome_title'       => sprintf( __( 'Welcome to %s! - Version ', 'theta' ), 'theta' ),
	// Main welcome content
	'welcome_content'     => esc_html__( 'Theta is a fully responsive one page wordpress theme which has a modern design with clean lines and style. Excellent prebuilt sections can easily and elegantly display all your important content on a single page. Theta will make your online business a professional and eye-catching look. You can see the demo at http://demos.coothemes.com/?theme-demo=theta', 'theta' ),
	/**
	 * Tabs array.
	 *
	 * The key needs to be ONLY consisted from letters and underscores. If we want to define outside the class a function to render the tab,
	 * the will be the name of the function which will be used to render the tab content.
	 */
	'tabs'                => array(
		'getting_started'     => __( 'Getting Started', 'theta' ),
		/*'recommended_actions' => __( 'Recommended Actions', 'theta' ),*/
		'recommended_plugins' => __( 'Recommended Plugins', 'theta' ),
		/*'support'             => __( 'Support', 'theta' ),*/
		/*'changelog'           => __( 'Changelog', 'theta' ),*/
		'free_pro'            => __( 'Free vs PRO', 'theta' ),
	),
	// Support content tab.
	'support_content'     => array(


	),
	// Getting started tab
	'getting_started'     => array(
		'first'  => array(
			'title'               => esc_html__( 'Recommended actions', 'theta' ),
			'text'                => esc_html__( 'We have compiled a list of steps for you to take so we can ensure that the experience you have using one of our products is very easy to follow.', 'theta' ),
			'button_label'        => esc_html__( 'Recommended actions', 'theta' ),
			'button_link'         => esc_url( admin_url( 'themes.php?page=theta-welcome&tab=recommended_plugins' ) ),
			'is_button'           => false,
			'recommended_actions' => true,
			'is_new_tab'          => false,
		),
		'second' => array(
			'title'               => esc_html__( 'Read full documentation', 'theta' ),
			'text'                => esc_html__( 'Need more details? Please check our full documentation for detailed information on how to use theta.', 'theta' ),
			'button_label'        => esc_html__( 'Documentation', 'theta' ),
			'button_link'         => 'https://www.coothemes.com/theta-pro-manual/',
			'is_button'           => false,
			'recommended_actions' => false,
			'is_new_tab'          => true,
		),
		'third'  => array(
			'title'               => esc_html__( 'Go to the Customizer', 'theta' ),
			'text'                => esc_html__( 'Using the WordPress Customizer you can easily customize every aspect of the theme.', 'theta' ),
			'button_label'        => esc_html__( 'Go to the Customizer', 'theta' ),
			'button_link'         => esc_url( admin_url( 'customize.php' ) ),
			'is_button'           => true,
			'recommended_actions' => false,
			'is_new_tab'          => true,
		),
		
		'fourth'  => array(
			'title'        => esc_html__( 'Contact Support', 'theta' ),
			'text'                => esc_html__( 'We want to make sure you have the best experience using theta, and that is why we have gathered all the necessary information here for you. We hope you will enjoy using theta as much as we enjoy creating great products.', 'theta' ),
			'button_label'        => esc_html__( 'Contact Support', 'theta' ),
			'button_link'  => esc_url( 'https://www.coothemes.com/forum/theta-theme' ),
			'is_button'           => true,
			'recommended_actions' => false,
			'is_new_tab'          => true,
		),
		
		
	),
	// Free vs PRO array.
	'free_pro'            => array(
		'free_theme_name'     => 'theta',
		'pro_theme_name'      => 'theta Pro',
		'pro_theme_link'      => 'https://www.coothemes.com/theme-theta/',
		/* translators: s - theme name */
		'get_pro_theme_label' => sprintf( __( 'Get %s now!', 'theta' ), 'theta Pro' ),
		'features'            => array(
			array(
				'title'       => __( 'Mobile friendly', 'theta' ),
				'description' => __( 'Responsive layout. Works on every device.', 'theta' ),
				'is_in_lite'  => 'true',
				'is_in_pro'   => 'true',
			),
			array(
				'title'       => __( 'WooCommerce Compatible', 'theta' ),
				'description' => __( 'Ready for e-commerce. You can build an online store here.', 'theta' ),
				'is_in_lite'  => 'true',
				'is_in_pro'   => 'true',
			),
			array(
				'title'       => __( 'Frontpage Sections', 'theta' ),
				'description' => __( 'slider, Features, About, Team, Testimonials, Blog, Contact', 'theta' ),
				'is_in_lite'  => 'false',
				'is_in_pro'   => 'true',
			),
			array(
				'title'       => __( 'Background image', 'theta' ),
				'description' => __( 'You can use any background image you want.', 'theta' ),
				'is_in_lite'  => 'true',
				'is_in_pro'   => 'true',
			),

			array(
				'title'       => __( 'Header Slider', 'theta' ),
				'description' => __( 'You will be able to add more content to your site header with an awesome slider.', 'theta' ),
				'is_in_lite'  => 'false',
				'is_in_pro'   => 'true',
			),
			array(
				'title'       => __( 'Fully Customizable Colors', 'theta' ),
				'description' => __( 'Change colors for the header overlay, header text and navbar.', 'theta' ),
				'is_in_lite'  => 'false',
				'is_in_pro'   => 'true',
			),

			array(
				'title'       => __( 'Pricing Plans Section', 'theta' ),
				'description' => __( 'A fully customizable pricing plans section.', 'theta' ),
				'is_in_lite'  => 'false',
				'is_in_pro'   => 'true',
			),
			array(
				'title'       => __( 'Quality Support', 'theta' ),
				'description' => __( '24/7 HelpDesk Professional Support', 'theta' ),
				'is_in_lite'  => 'false',
				'is_in_pro'   => 'true',
			),
		),
	),
	
	// Plugins array.
	'recommended_plugins' => array(
		'already_activated_message' => esc_html__( 'Already activated', 'theta' ),
		'version_label'             => esc_html__( 'Version: ', 'theta' ),
		'install_label'             => esc_html__( 'Install and Activate', 'theta' ),
		'activate_label'            => esc_html__( 'Activate', 'theta' ),
		'deactivate_label'          => esc_html__( 'Deactivate', 'theta' ),
		'content'                   => array(
			array(
				'slug' => 'kirki',
			),
			
		),
	),
/*	
	// Required actions array.
	'recommended_actions' => array(
		'install_label'    => esc_html__( 'Install and Activate', 'theta' ),
		'activate_label'   => esc_html__( 'Activate', 'theta' ),
		'deactivate_label' => esc_html__( 'Deactivate', 'theta' ),
		'content'          => array(
			'themeisle-companion' => array(
				'title'       => 'kirki',
				'description' => __( 'It is highly recommended that you install the kirki plugin to have access to the Frontpage sections.', 'theta' ),
				'check'       => '3.0.20',
				'plugin_slug' => 'kirki',
				'id'          => 'kirki',
			),


		),
	),*/
);
Coothemes_About_Page::init( apply_filters( 'theta_about_page_array', $config ) );

/*
 * Notifications in customize
 */
 /*
require get_template_directory() . '/ti-customizer-notify/class-themeisle-customizer-notify.php';

$config_customizer = array(
	'recommended_plugins'       => array(
		'themeisle-companion' => array(
			'recommended' => true,

			'description' => sprintf( esc_html__( 'If you want to take full advantage of the options this theme has to offer, please install and activate %s.', 'theta' ), sprintf( '<strong>%s</strong>', 'ThemeIsle Companion' ) ),
		),
	),
	'recommended_actions'       => array(),
	'recommended_actions_title' => esc_html__( 'Recommended Actions', 'theta' ),
	'recommended_plugins_title' => esc_html__( 'Recommended Plugins', 'theta' ),
	'install_button_label'      => esc_html__( 'Install and Activate', 'theta' ),
	'activate_button_label'     => esc_html__( 'Activate', 'theta' ),
	'deactivate_button_label'   => esc_html__( 'Deactivate', 'theta' ),
);
Themeisle_Customizer_Notify::init( apply_filters( 'theta_customizer_notify_array', $config_customizer ) );
*/