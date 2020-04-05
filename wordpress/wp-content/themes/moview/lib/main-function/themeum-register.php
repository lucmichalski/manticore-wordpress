<?php 
/*-------------------------------------------*
 *      Themeum Widget Registration
 *------------------------------------------*/


if(!function_exists('moview_widdget_init')):

    function moview_widdget_init()
    {
        global $themeum_options;
        if(!isset($themeum_options['bottom-column'])){
            $themeum_options['bottom-column'] = 4;
        }
        $bottomcolumn = $themeum_options['bottom-column'];


        register_sidebar(array( 'name'          => esc_html__( 'Sidebar', 'moview' ),
                                'id'            => 'sidebar',
                                'description'   => esc_html__( 'Widgets in this area will be shown on Sidebar.', 'moview' ),
                                'before_title'  => '<h3 class="widget_title">',
                                'after_title'   => '</h3>',
                                'before_widget' => '<div id="%1$s" class="widget %2$s" >',
                                'after_widget'  => '</div>'
                    )
        );

        register_sidebar(array( 
                            'name'          => esc_html__( 'Bottom', 'moview' ),
                            'id'            => 'bottom',
                            'description'   => esc_html__( 'Widgets in this area will be shown before Footer.' , 'moview'),
                            'before_title'  => '<h3 class="widget_title">',
                            'after_title'   => '</h3>',
                            'before_widget' => '<div class="col-sm-6 col-md-'.esc_attr($bottomcolumn).' bottom-widget"><div id="%1$s" class="widget %2$s" >',
                            'after_widget'  => '</div></div>'
                            )
        );
        
        if(function_exists('buddypress')){
            register_sidebar(array( 'name'          => esc_html__( 'Buddypress Sidebar', 'moview' ),
                            'id'            => 'buddpress_sidebar',
                            'description'   => esc_html__( 'Widgets in this area will be shown on Sidebar.', 'moview' ),
                            'before_title'  => '<h3 class="widget_title">',
                            'after_title'   => '</h3>',
                            'before_widget' => '<div id="%1$s" class="widget %2$s" >',
                            'after_widget'  => '</div>'
                )
        );

        }
        
        
        if(function_exists('is_bbpress')){
            register_sidebar(array( 'name'          => esc_html__( 'BB Press Sidebar', 'moview' ),
                                    'id'            => 'bbpress_sidebar',
                                    'description'   => esc_html__( 'Widgets in this area will be shown on BB Press Sidebar.', 'moview' ),
                                    'before_title'  => '<h3 class="widget_title">',
                                    'after_title'   => '</h3>',
                                    'before_widget' => '<div id="%1$s" class="widget %2$s" >',
                                    'after_widget'  => '</div>'
                        )
            );
        }


        global $woocommerce;
        if($woocommerce) {
            register_sidebar(array(
                'name'          => __( 'Shop', 'moview' ),
                'id'            => 'shop',
                'description'   => __( 'Widgets in this area will be shown on Shop Sidebar.', 'moview' ),
                'before_title'  => '<div class="widget_title"><h3 class="widget_title">',
                'after_title'   => '</h3></div>',
                'before_widget' => '<div id="%1$s" class="widget %2$s" >',
                'after_widget'  => '</div>'
                )
            );
        }   

    }
    
    add_action('widgets_init','moview_widdget_init');

endif;


/*-------------------------------------------*
 *      Themeum Style
 *------------------------------------------*/

if(!function_exists('moview_style')):

    function moview_style(){

        wp_enqueue_media();

        wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css',false,'all');
        wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.css',false,'all');
        wp_enqueue_style( 'animate', get_template_directory_uri() . '/css/animate.css',false,'all');
        wp_enqueue_style( 'nanoscroller', get_template_directory_uri() . '/css/nanoscroller.css',false,'all');
        wp_enqueue_style( 'flexslider', get_template_directory_uri() . '/css/flexslider.css',false,'all');
        wp_enqueue_style( 'themeum-moview-font', get_template_directory_uri() . '/css/themeum-moview-font.css',false,'all');
        wp_enqueue_style( 'wpeducon-main', get_template_directory_uri() . '/css/main.css',false,'all');
        wp_enqueue_style( 'prettyPhoto', get_template_directory_uri() . '/css/prettyPhoto.css',false,'all');
        wp_enqueue_style( 'wpeducon-responsive', get_template_directory_uri() . '/css/responsive.css',false,'all');

        wp_enqueue_style('thm-style',get_stylesheet_uri());
        wp_enqueue_script('bootstrap',MOVIEW_JS.'bootstrap.min.js',array(),false,true);
        wp_enqueue_script('jquery.countdown',MOVIEW_JS.'jquery.countdown.min.js',array(),false,true);
        wp_enqueue_script('jquery.prettySocial',MOVIEW_JS.'jquery.prettySocial.min.js',array(),false,true);
        wp_enqueue_script('jquery.prettyPhoto',MOVIEW_JS.'jquery.prettyPhoto.js',array(),false,true);
        wp_enqueue_script('jquery.ajax.login',MOVIEW_JS.'ajax-login-script.js',array(),false,true);
        
        if( moview_options('menu-style') ){
            if( moview_options('menu-style') == 'offcanvus' || moview_options('menu-style') == 'classic' ){
                wp_enqueue_script('off.canvas',MOVIEW_JS.'off-canvas.js',array(),false,true);
            }
        }
        global $themeum_options;
        if( isset($themeum_options['custom-preset-en']) && $themeum_options['custom-preset-en']==0 ) {
            wp_enqueue_style( 'themeum-preset', get_template_directory_uri(). '/css/presets/preset' . $themeum_options['preset'] . '.css', array(),false,'all' );       
        }else {
            wp_enqueue_style('quick-preset',get_template_directory_uri().'/quick-preset.php',array(),false,'all');
        }
        wp_enqueue_style('quick-preset',get_template_directory_uri().'/quick-preset.php',array(),false,'all');

        wp_enqueue_style('quick-style',get_template_directory_uri().'/quick-style.php',array(),false,'all');
        wp_enqueue_script('main',MOVIEW_JS.'main.js',array(),false,true);
    }
    add_action('wp_enqueue_scripts','moview_style');
endif;

add_action('enqueue_block_editor_assets', 'moview_action_enqueue_block_editor_assets' );
function moview_action_enqueue_block_editor_assets() {
    wp_enqueue_style( 'moview-gutenberg-editor-font-awesome-styles', get_template_directory_uri() . '/css/font-awesome.css', null, 'all' );
    wp_enqueue_style( 'thm-style', get_stylesheet_uri() );
    wp_enqueue_style( 'moview-gutenberg-editor-customizer-styles', get_template_directory_uri() . '/css/gutenberg-editor-custom.css', null, 'all' );
    wp_enqueue_style( 'moview-gutenberg-editor-styles', get_template_directory_uri() . '/css/gutenberg-custom.css', null, 'all' );
}


if(!function_exists('moview_admin_style')):

    function moview_admin_style(){
        wp_enqueue_media();
        wp_register_script('thmpostmeta', get_template_directory_uri() .'/js/admin/post-meta.js');
        wp_enqueue_script('themeum-widget-js', get_template_directory_uri().'/js/widget-js.js', array('jquery'));
        wp_enqueue_script('thmpostmeta');
    }
    add_action('admin_enqueue_scripts','moview_admin_style');

endif;


/*-------------------------------------------------------
*           Include the TGM Plugin Activation class
*-------------------------------------------------------*/

require_once( get_template_directory()  . '/lib/class-tgm-plugin-activation.php');

add_action( 'tgmpa_register', 'moview_plugins_include');

if(!function_exists('moview_plugins_include')):

    function moview_plugins_include()
    {
        $plugins = array(

                array(
                    'name'                  => 'Themeum Core',
                    'slug'                  => 'themeum-core',
                    'source'                => 'http://demo.themeum.com/wordpress/plugins/moview/themeum-core.zip',
                    'required'              => true,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => '',
                ), 
                array(
                    'name'                  => esc_html__( 'Qubely Blocks – Full-fledged Gutenberg Toolkit', 'corlate' ),
                    'slug'                  => 'qubely',
                    'required'              => true,
                    'version'               => '',
                    'force_activation'      => true,
                    'force_deactivation'    => false
                ),
                array(
                    'name'                  => 'WPBakery Visual Composer',
                    'slug'                  => 'js_composer',
                    'source'                => 'http://demo.themeum.com/wordpress/plugins/js_composer.zip',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => '',
                ),                
                array(
                    'name'                  => 'Themeum Tweet',
                    'slug'                  => 'themeum-tweet',
                    'source'                => 'http://demo.themeum.com/wordpress/plugins/moview/themeum-tweet.zip',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => '',
                ),                 
                array(
                    'name'                  => 'WP Rating',
                    'slug'                  => 'wp-rating',
                    'source'                => 'http://demo.themeum.com/wordpress/plugins/moview/wp-rating.zip',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => '',
                ), 
                array(
                    'name'                  => 'MailChimp for WordPress',
                    'slug'                  => 'mailchimp-for-wp',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => 'https://downloads.wordpress.org/plugin/mailchimp-for-wp.3.1.6.zip',
                ),   
                array(
                    'name'                  => 'Woocoomerce',
                    'slug'                  => 'woocommerce',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => 'https://downloads.wordpress.org/plugin/woocommerce.3.0.4.zip', 
                ),  
                array(
                    'name'                  => 'Buddypress',
                    'slug'                  => 'buddypress',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => 'https://downloads.wordpress.org/plugin/buddypress.2.5.2.zip',
                ),  
                array(
                    'name'                  => 'bbpress',
                    'slug'                  => 'bbpress',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => 'https://downloads.wordpress.org/plugin/bbpress.2.5.8.zip',
                ),                                                                             
                array(
                    'name'                  => 'Widget Importer Exporter',
                    'slug'                  => 'widget-importer-exporter',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => 'https://downloads.wordpress.org/plugin/widget-importer-exporter.1.4.5.zip',
                ),
                array(
                    'name'                  => 'Contact Form 7',
                    'slug'                  => 'contact-form-7',
                    'required'              => false,
                    'version'               => '',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => 'https://downloads.wordpress.org/plugin/contact-form-7.4.4.1.zip',
                ),
            );
    $config = array(
            'domain'            => 'moview',           
            'default_path'      => '',                           
            'parent_menu_slug'  => 'themes.php',                 
            'parent_url_slug'   => 'themes.php',                
            'menu'              => 'install-required-plugins',   
            'has_notices'       => true,                         
            'is_automatic'      => false,                      
            'message'           => '',                     
            'strings'           => array(
                        'page_title'                                => esc_html__( 'Install Required Plugins', 'moview' ),
                        'menu_title'                                => esc_html__( 'Install Plugins', 'moview' ),
                        'installing'                                => esc_html__( 'Installing Plugin: %s', 'moview' ), 
                        'oops'                                      => esc_html__( 'Something went wrong with the plugin API.', 'moview'),
                        'return'                                    => esc_html__( 'Return to Required Plugins Installer', 'moview'),
                        'plugin_activated'                          => esc_html__( 'Plugin activated successfully.','moview'),
                        'complete'                                  => esc_html__( 'All plugins installed and activated successfully. %s', 'moview' ) 
                )
    );

    tgmpa( $plugins, $config );

    }

endif;