<?php

/**
  ReduxFramework Sample Config File
  For full documentation, please visit: https://docs.reduxframework.com
 * */

if (!class_exists('Redux_Framework_sample_config')) {

    class Redux_Framework_sample_config {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

            // This is needed. Bah WordPress bugs.  ;)
            if (  true == Redux_Helpers::isTheme(__FILE__) ) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);
            }

        }

        public function initSettings() {

            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            // If Redux is running as a plugin, this will remove the demo notice and links
            //add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
            
            // Function to test the compiler hook and demo CSS output.
            // Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
            //add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 3);
            
            // Change the arguments after they've been declared, but before the panel is created
            //add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
            
            // Change the default value of a field after it's been set, but before it's been useds
            //add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );
            
            // Dynamically add a section. Can be also used to modify sections/fields
            //add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**

          This is a test function that will let you see when the compiler hook occurs.
          It only runs if a field	set with compiler=>true is changed.

         * */
        function compiler_action($options, $css, $changed_values) {
            echo '<h1>The compiler hook has run!</h1>';
            echo "<pre>";
            print_r($changed_values); // Values that have changed since the last save
            echo "</pre>";
            //print_r($options); //Option values
            //print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )

            /*
              if( $wp_filesystem ) {
                $wp_filesystem->put_contents(
                    $filename,
                    $css,
                    FS_CHMOD_FILE // predefined mode settings for WP files
                );
              }
             */
        }

        /**

          Custom function for filtering the sections array. Good for child themes to override or add to the sections.
          Simply include this function in the child themes functions.php file.

          NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
          so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => esc_html__('Section via hook', 'themeum-core'),
                'desc' => esc_html__('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'themeum-core'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );

            return $sections;
        }

        /**

          Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

         * */
        function change_arguments($args) {
            //$args['dev_mode'] = true;

            return $args;
        }

        /**

          Filter hook for filtering the default value of any given field. Very useful in development mode.

         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = 'Testing filter hook!';

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
            }
        }

        public function setSections() {

            /**
              Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
             * */
            // Background Patterns Reader
            $sample_patterns_path   = ReduxFramework::$_dir . '../sample/patterns/';
            $sample_patterns_url    = ReduxFramework::$_url . '../sample/patterns/';
            $sample_patterns        = array();

            if (is_dir($sample_patterns_path)) :

                if ($sample_patterns_dir = opendir($sample_patterns_path)) :
                    $sample_patterns = array();

                    while (( $sample_patterns_file = readdir($sample_patterns_dir) ) !== false) {

                        if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
                            $name = explode('.', $sample_patterns_file);
                            $name = str_replace('.' . end($name), '', $sample_patterns_file);
                            $sample_patterns[]  = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
                        }
                    }
                endif;
            endif;

            ob_start();

            $ct             = wp_get_theme();
            $this->theme    = $ct;
            $item_name      = $this->theme->get('Name');
            $tags           = $this->theme->Tags;
            $screenshot     = $this->theme->get_screenshot();
            $class          = $screenshot ? 'has-screenshot' : '';

            $customize_title = sprintf(esc_html__('Customize &#8220;%s&#8221;', 'themeum-core'), $this->theme->display('Name'));
            
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">
            <?php if ($screenshot) : ?>
                <?php if (current_user_can('edit_theme_options')) : ?>
                        <a href="<?php echo esc_url(wp_customize_url()); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview','moview'); ?>" />
                        </a>
                <?php endif; ?>
                    <img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_html_e('Current theme preview','moview'); ?>" />
                <?php endif; ?>

                <h4><?php echo esc_html($this->theme->display('Name')); ?></h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(esc_html__('By %s', 'themeum-core'), $this->theme->display('Author')); ?></li>
                        <li><?php printf(esc_html__('Version %s', 'themeum-core'), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . esc_html__('Tags', 'themeum-core') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
            <?php
            if ($this->theme->parent()) {
                printf(' <p class="howto">' . esc_html__('This child theme requires its parent theme','moview') . '</p>', __('http://codex.wordpress.org/Child_Themes', 'moview'), $this->theme->parent()->display('Name'));
            }
            ?>

                </div>
            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();

            // ACTUAL DECLARATION OF SECTIONS


            /**********************************
            ********* Header Setting ***********
            ***********************************/
            $this->sections[] = array(
                'title'     => esc_html__('Header', 'Home Setting'),
                'icon'      => 'el-icon-bookmark',
                'icon_class' => 'el-icon-large',
                'fields'    => array(


                    

                    array(
                        'id'        => 'header-padding-top',
                        'type'      => 'text',
                        'title'     => esc_html__('Header Top Padding', 'themeum-core'),
                        'subtitle' => esc_html__('Enter custom header top padding', 'themeum-core'),
                        'default'   => '0',

                    ),  

                    array(
                        'id'        => 'header-padding-bottom',
                        'type'      => 'text',
                        'title'     => esc_html__('Header Bottom Padding', 'themeum-core'),
                        'subtitle' => esc_html__('Enter custom header bottom padding', 'themeum-core'),
                        'default'   => '0',
                    ),     

                    array(
                        'id'        => 'header-height',
                        'type'      => 'text',
                        'title'     => esc_html__('Header Height ex. 52', 'themeum-core'),
                        'subtitle' => esc_html__('Enter custom header Height', 'themeum-core'),
                        'default'   => '52',
                    ),  

                    array(
                        'id'        => 'header-fixed',
                        'type'      => 'switch',
                        'title'     => __('Sticky Header', 'themeum-core'),
                        'subtitle' => __('Enable or disable sicky Header', 'themeum-core'),
                        'default'   => false,
                    ),
                    array(
                        'id'        => 'show_search_box',
                        'type'      => 'switch',
                        'title'     => __('Show Search Box', 'themeum-core'),
                        'subtitle' => __('Enable or disable search box at Header', 'themeum-core'),
                        'default'   => true,
                    ),
                    array(
                        'id'        => 'show_login_menu',
                        'type'      => 'switch',
                        'title'     => __('Show Login Menu', 'themeum-core'),
                        'subtitle' => __('Enable or disable Login Menu at Header', 'themeum-core'),
                        'default'   => true,
                    ),
                    

                )
            );


            /* *********************************
            **** Category Page Setting  *****
            ********************************** */
            $this->sections[] = array(
                'title'     => esc_html__('Menu Settings', 'themeum-core'),
                'icon'      => 'sub-banner-icon',
                'icon_class' => 'el-icon-filter',
                'fields'    => array(
                    array(
                        'id'       => 'menu-style',
                        'type'     => 'select',
                        'title'    => esc_html__('Select Menu Style', 'themeum-core'), 
                        'subtitle' => esc_html__('Select Menu Style of the Theme', 'themeum-core'),
                        'options'  => array(
                            'offcanvus' => 'Menu Style Off Canvas',
                            'regular'   => 'Menu Style Regular',
                            'classic'   => 'Menu Style Classic'
                        ),
                        'default'  => 'offcanvus',
                    ),
                    array(
                        'id'        => 'regular_menu_bg',
                        'type'      => 'color',
                        'output'    => array('background-color' => '#main-menu'),
                        'title'     => esc_html__('Regular Menu Background Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Regular menu bar background color at header (default: #eeeeee).', 'themeum-core'),
                        'default'   => '#eeeeee',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'regular'),
                    ),
                    array(
                        'id'        => 'regular_menu_link_color',
                        'type'      => 'color',
                        'output'    => array('color' => '#main-menu .nav>li a'),
                        'title'     => esc_html__('Regular Menu Link Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Regular menu bar background color at header (default: #000).', 'themeum-core'),
                        'default'   => '#000',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'regular'),
                    ), 
                    array(
                        'id'        => 'regular_menu_link_color_hvr',
                        'type'      => 'color',
                        'output'    => array('color' => '#main-menu .nav>li a:hover'),
                        'title'     => esc_html__('Regular Menu Link Hover Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Regular menu bar background color at header (default: #000).', 'themeum-core'),
                        'default'   => '#000',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'regular'),
                    ),

                    array(
                        'id'        => 'regular_menu_link_bg_color',
                        'type'      => 'color',
                        'output'    => array('background-color' => '#main-menu .nav>li a'),
                        'title'     => esc_html__('Regular Menu Link Background', 'themeum-core'),
                        'subtitle'  => esc_html__('Regular menu bar background color at header (default: #eeeeee).', 'themeum-core'),
                        'default'   => '#eeeeee',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'regular'),
                    ), 
                    array(
                        'id'        => 'regular_menu_link_bg_color_hvr',
                        'type'      => 'color',
                        'output'    => array('background-color' => '#main-menu .nav>li a:hover'),
                        'title'     => esc_html__('Regular Menu Link Hover Background', 'themeum-core'),
                        'subtitle'  => esc_html__('Regular menu bar background color at header (default: #f26522).', 'themeum-core'),
                        'default'   => '#fff',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'regular'),
                    ),

                    array(
                        'id'        => 'classic_menu_bg',
                        'type'      => 'color',
                        'output'    => array('background-color' => '.mv-classic-menu-container'),
                        'title'     => esc_html__('Classic Menu Background Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Classic menu bar background color at header (default: #f5f5f5).', 'themeum-core'),
                        'default'   => '#f5f5f5',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'classic'),
                    ),
                    array(
                        'id'        => 'classic_menu_link_color',
                        'type'      => 'color',
                        'output'    => array('color' => '.mv-classic-menu .nav>li>a'),
                        'title'     => esc_html__('Classic Menu Link Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Classic menu bar background color at header (default: #000).', 'themeum-core'),
                        'default'   => '#000',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'classic'),
                    ), 
                    array(
                        'id'        => 'classic_menu_link_color_hvr',
                        'type'      => 'color',
                        'output'    => array('color' => '.mv-classic-menu .nav>li>a:hover'),
                        'title'     => esc_html__('Classic Menu Link Hover Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Classic menu bar background color at header (default: #f26522).', 'themeum-core'),
                        'default'   => '#f26522',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('menu-style', "=", 'classic'),
                    ), 
                )
            );
                    

            /**********************************
            ********* Logo & Favicon ***********
            ***********************************/

            $this->sections[] = array(
                'title'     => esc_html__('All Logo & favicon', 'themeum-core'),
                'icon'      => 'el-icon-leaf',
                'icon_class' => 'el-icon-large',
                'fields'    => array(

                    array( 
                        'id'        => 'favicon', 
                        'type'      => 'media',
                        'desc'      => 'upload favicon image',
                        'title'      => esc_html__('Favicon','themeum-core'),
                        'subtitle' => esc_html__('Upload favicon image', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/favicon.ico' ), 
                    ),                                        

                    array(
                        'id'=>'logo',
                        'url'=> false,
                        'type' => 'media', 
                        'title' => esc_html__('Logo', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
                        'subtitle' => esc_html__('Upload your custom site logo.', 'themeum-core'),
                    ),

                    array(
                        'id'        => 'logo-width',
                        'type'      => 'text',
                        'title'     => esc_html__('Logo Width', 'themeum-core'),
                        'subtitle'  => esc_html__('Set Logo Width', 'themeum-core'),
                        'default'   => '',
                    ),
                    array(
                        'id'        => 'logo-height',
                        'type'      => 'text',
                        'title'     => esc_html__('Logo Height', 'themeum-core'),
                        'subtitle'  => esc_html__('Set Logo Height', 'themeum-core'),
                        'default'   => '',
                    ),

                    array(
                        'id'        => 'logo-text-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Text Type Logo', 'themeum-core'),
                        'subtitle' => esc_html__('Enable or disable text type logo', 'themeum-core'),
                        'default'   => false,
                    ),

                    array(
                        'id'        => 'logo-text',
                        'type'      => 'text',
                        'title'     => esc_html__('Logo Text', 'themeum-core'),
                        'subtitle' => esc_html__('Use your Custom logo text Ex. Moview', 'themeum-core'),
                        'default'   => 'themeum-core',
                        'required'  => array('logo-text-en', "=", 1),
                    ),

                    array(
                        'id'        => 'logo_center',
                        'type'      => 'switch',
                        'title'     => __('Logo Align Center', 'themeum-core'),
                        'subtitle' => __('Logo at center will only work, if search box and user login menu is disabled.', 'themeum-core'),
                        'default'   => false,
                    ),

                    array( 
                        'id'        => 'errorpage', 
                        'type'      => 'media',
                        'desc'      => 'upload 404 Page Logo',
                        'title'      => esc_html__('404 Page Logo','themeum-core'),
                        'subtitle' => esc_html__('Upload 404 Page Logo', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/404.png' ), 
                    ),

                    array( 
                        'id'        => 'errorbg', 
                        'type'      => 'media',
                        'desc'      => 'Upload 404 Page Background Image',
                        'title'      => esc_html__('404 Page BG','themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/404-bg.png' ), 
                    ),
                    
                    array( 
                        'id'        => 'comingsoon-logo', 
                        'type'      => 'media',
                        'desc'      => 'Upload Coming Soon Page Logo',
                        'title'     => esc_html__('Coming Soon Page Logo','themeum-core'),
                        'subtitle' => esc_html__('Upload Coming Soon Page Logo', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/coming-soon-logo.png' ), 
                    ),

                    array( 
                        'id'        => 'comingsoon', 
                        'type'      => 'media',
                        'desc'      => 'Upload Coming Soon Page Background',
                        'title'      => esc_html__('Coming Soon Page Background','themeum-core'),
                        'subtitle' => esc_html__('Upload Coming Soon Page Background', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/trailers-and-video-bg.jpg' ), 
                    ),

                    array(
                        'id'=>'reg_logo',
                        'url'=> false,
                        'type' => 'media', 
                        'title' => esc_html__('Register Form Logo', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
                        'subtitle' => esc_html__('Upload your custom site logo.', 'themeum-core'),
                    ),

                    
                )
            );



            /* *********************************
            **** Category Page Setting  *****
            ********************************** */
            $this->sections[] = array(
                'title'     => esc_html__('Category Page', 'themeum-core'),
                'icon'      => 'sub-banner-icon',
                'icon_class' => 'el-icon-filter',
                'fields'    => array(

                    array(
                        'id'        => 'movie-cat-num',
                        'type'      => 'text',
                        'title'     => esc_html__('Movie Category Page Number:', 'themeum-core'),
                        'subtitle' => esc_html__('Number of Post Show on the Movie Category Listing Page', 'themeum-core'),
                        'default'   => '3',
                    ),

                    array(
                        'id'        => 'celebrity-cat-num',
                        'type'      => 'text',
                        'title'     => esc_html__('Celebrity Category Page Number:', 'themeum-core'),
                        'subtitle' => esc_html__('Number of Post Show on the Celebrity Category Listing Page', 'themeum-core'),
                        'default'   => '3',
                    ), 

                )
            );

            /**********************************
            **** Default Banner  *****
            ***********************************/
            $this->sections[] = array(
                'title'     => esc_html__('Banner Image', 'themeum-core'),
                'icon'      => 'sub-banner-icon',
                'icon_class' => 'el-icon-compass',
                'fields'    => array(
                    array( 
                        'id'        => 'blog-banner', 
                        'type'      => 'media',
                        'desc'      => 'Upload Blog Banner image',
                        'title'      => esc_html__('Blog Banner','themeum-core'),
                        'subtitle' => esc_html__('Upload Blog Banner image', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/blog-banner.jpg' ),
                    ),  
                    array( 
                        'id'        => 'buddypress-m-banner', 
                        'type'      => 'media',
                        'desc'      => 'Upload Buddypress Member Page Banner image',
                        'title'      => esc_html__('Buddypress Member Page Banner','themeum-core'),
                        'subtitle' => esc_html__('Upload Buddypress Member Page Banner image', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/budym-banner.jpg' ),
                    ),
                    array( 
                        'id'        => 'buddypress-g-banner', 
                        'type'      => 'media',
                        'desc'      => 'Upload Buddypress Group Page Banner image',
                        'title'      => esc_html__('Buddypress Group Page Banner','themeum-core'),
                        'subtitle' => esc_html__('Upload Buddypress Group Page Banner image', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/budym-banner.jpg' ),
                    ),                    
                    array( 
                        'id'        => 'buddypress-ac-banner', 
                        'type'      => 'media',
                        'desc'      => 'Upload Buddypress Activity Page Banner image',
                        'title'      => esc_html__('Buddypress Activity Page Banner','themeum-core'),
                        'subtitle' => esc_html__('Upload Buddypress Activity Page Banner image', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/blog-banner.jpg' ),
                    ),                    
                    array( 
                        'id'        => 'bbpress-forums-banner', 
                        'type'      => 'media',
                        'desc'      => 'Upload bbPress Forums Page Banner image',
                        'title'      => esc_html__('bbPress Forums Page Banner','themeum-core'),
                        'subtitle' => esc_html__('Upload bbpress Forums Page Banner image', 'themeum-core'),
                        'default' => array( 'url' => get_template_directory_uri() .'/images/blog-banner.jpg' ),
                    ),
                    array( 
                        'id'        => 'blog-subtitle-bg-color', 
                        'type'      => 'color',
                        'desc'      => 'Blog Subtitle BG Color',
                        'title'     => esc_html__('Background Color','themeum-core'),
                        'subtitle'  => esc_html__('Blog Subtitle BG Color', 'themeum-core'),
                        'default'   => '#191919',
                        'transparent'   =>false,

                    )
                )
            );


            /**********************************
            ********* Layout & Styling ***********
            ***********************************/

            $this->sections[] = array(
                'icon' => 'el-icon-brush',
                'icon_class' => 'el-icon-large',
                'title'     => esc_html__('Layout & Styling', 'themeum-core'),
                'fields'    => array(

                   array(
                        'id'       => 'boxfull-en',
                        'type'     => 'select',
                        'title'    => esc_html__('Select Layout', 'themeum-core'), 
                        'subtitle' => esc_html__('Select BoxWidth of FullWidth', 'themeum-core'),
                        // Must provide key => value pairs for select options
                        'options'  => array(
                            'boxwidth' => 'BoxWidth',
                            'fullwidth' => 'FullWidth'
                        ),
                        'default'  => 'fullwidth',
                    ), 
                   
                    array(
                        'id'        => 'box-background',
                        'type'      => 'background',
                        'output'    => array('body'),
                        'title'     => esc_html__('Body Background', 'themeum-core'),
                        'subtitle'  => esc_html__('You can set Background color or images or patterns for site body tag', 'themeum-core'),
                        'default'   => '#fff',
                        'transparent'   =>false,
                    ), 


                    array(
                        'id'        => 'preset',
                        'type'      => 'image_select',
                        'compiler'  => true,
                        'title'     => esc_html__('Preset Layout', 'themeum-core'),
                        'subtitle'  => esc_html__('select any preset', 'themeum-core'),
                        'options'   => array(
                            '1' => array('alt' => 'Preset 1',       'img' => ReduxFramework::$_url . 'assets/img/presets/preset1.png'),
                            '2' => array('alt' => 'Preset 2',       'img' => ReduxFramework::$_url . 'assets/img/presets/preset2.png'),
                            '3' => array('alt' => 'Preset 3',       'img' => ReduxFramework::$_url . 'assets/img/presets/preset3.png'),
                            '4' => array('alt' => 'Preset 4',       'img' => ReduxFramework::$_url . 'assets/img/presets/preset4.png'),
                            ),
                        'default'   => '1'
                    ),  
                    

                    array(
                        'id'        => 'custom-preset-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Select Custom Color', 'themeum-core'),
                        'subtitle' => esc_html__('You can use unlimited color', 'themeum-core'),
                        'default'   => true,
                        
                    ),

                     array(
                        'id'        => 'link-color',
                        'type'      => 'color',
                        'title'     => esc_html__('Link Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Pick a link color (default: #f26522).', 'themeum-core'),
                        'default'   => '#f26522',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('custom-preset-en', "=", 1),
                    ),

                     array(
                        'id'        => 'hover-color',
                        'type'      => 'color',
                        'title'     => esc_html__('Hover Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Pick a hover color (default: #d54d0d).', 'themeum-core'),
                        'default'   => '#d54d0d',
                        'validate'  => 'color',
                        'transparent'   =>false,
                        'required'  => array('custom-preset-en', "=", 1),
                    ), 

                    array(
                        'id'        => 'header-bg',
                        'type'      => 'color',
                        'title'     => esc_html__('Header Background Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Pick a background color for the header (default: #fff).', 'themeum-core'),
                        'default'   => '#fff',
                        'validate'  => 'color',
                        'transparent'   =>false,
                    ),

                    array(
                        'id'        => 'bottom-section',
                        'type'      => 'switch',
                        'title'     => esc_html__('Select Bottom Section Enable/Disable', 'themeum-core'),
                        'subtitle' => esc_html__('You can use unlimited color', 'themeum-core'),
                        'default'   => true,
                        
                    ),
                    array(
                        'id'       => 'bottom-column',
                        'type'     => 'select',
                        'title'    => esc_html__('Select Bottom Column', 'themeum-core'), 
                        // Must provide key => value pairs for select options
                        'options'  => array(
                            '3' => '4 Column',
                            '4' => '3 Column',
                            '6' => '2 Column',
                        ),
                        'default'  => '4',
                    ), 

                    array(
                        'id'        => 'bottom-background',
                        'type'      => 'background',
                        'output'    => array('.footer-wrap'),
                        'title'     => esc_html__('Bottom Background', 'themeum-core'),
                        'subtitle'  => esc_html__('You can set Background color or images or patterns for site Bottom Background', 'moview'),
                        'default'   => '#ed1c24',
                        'transparent'   =>false,
                    ),   
                    
                    array(
                        'id'        => 'tweet-color',
                        'type'      => 'color',
                        'title'     => esc_html__('Tweet Text Color', 'themeum-core'),
                        'subtitle'  => esc_html__('Pick a Tweet Text color for the Twitter (default: #fff).', 'themeum-core'),
                        'default'   => '#fff',
                        'validate'  => 'color',
                        'transparent'   =>false,
                    ),
                    array(
                        'id'        => 'tweet-link-color',
                        'type'      => 'color',
                        'title'     => esc_html__('Tweet Link Color', 'themeum-core'),
                        'default'   => '#fff',
                        'validate'  => 'color',
                        'transparent'   =>false,
                    ),
                    array(
                        'id'        => 'tweet-font-size',
                        'type'      => 'text',
                        'title'     => esc_html__('Tweet Font Size', 'themeum-core'),
                        'default'   => '22',
                    ),
                    array(
                        'id'        => 'tweet-font-line',
                        'type'      => 'text',
                        'title'     => esc_html__('Tweet Font Line Height', 'themeum-core'),
                        'default'   => '30',
                    ),


                )
            );

            /**********************************
            ********* Typography ***********
            ***********************************/

            $this->sections[] = array(
                'icon'      => 'el-icon-font',
                'icon_class' => 'el-icon-large',                
                'title'     => esc_html__('Typography', 'themeum-core'),
                'fields'    => array(

                    array(
                        'id'            => 'body-font',
                        'type'          => 'typography',
                        'title'         => esc_html__('Body Font', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        //'font-size'     => ture,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('body'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Body Font', 'themeum-core'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '300',
                            'font-family'   => 'Open Sans',
                            'google'        => true,
                            'font-size'     => '16px'),
                    ), 

                    array(
                        'id'            => 'menu-font',
                        'type'          => 'typography',
                        'title'         => esc_html__('Menu Font', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('#main-menu .nav>li>a, #main-menu ul.sub-menu li > a, #offcanvas-menu .nav>li a'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Menu Font', 'themeum-core'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '400',
                            'font-family'   => 'Open Sans',
                            'google'        => true,
                            'font-size'     => '14px'),
                    ),

                    array(
                        'id'            => 'headings-font_h1',
                        'type'          => 'typography',
                        'title'         => esc_html__('Headings Font h1', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('h1'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Headings Font', 'themeum-core'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '700',
                            'font-family'   => 'Open Sans',
                            'google'        => true,
                            'font-size'     => '36px'),
                    ),                      

                    array(
                        'id'            => 'headings-font_h2',
                        'type'          => 'typography',
                        'title'         => esc_html__('Headings Font h2', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('h2'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Headings Font', 'themeum-core'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '700',
                            'font-family'   => 'Open Sans',
                            'google'        => true,
                            'font-size'     => '36px'),
                    ),                      

                    array(
                        'id'            => 'headings-font_h3',
                        'type'          => 'typography',
                        'title'         => esc_html__('Headings Font h3', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('h3'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Headings Font', 'moview'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '700',
                            'font-family'   => 'Montserrat',
                            'google'        => true,
                            'font-size'     => '24px'),
                    ),                     

                    array(
                        'id'            => 'headings-font_h4',
                        'type'          => 'typography',
                        'title'         => esc_html__('Headings Font h4', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('h4'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Headings Font', 'themeum-core'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '700',
                            'font-family'   => 'Open Sans',
                            'google'        => true,
                            'font-size'     => '20px'),
                    ),                      

                    array(
                        'id'            => 'headings-font_h5',
                        'type'          => 'typography',
                        'title'         => esc_html__('Headings Font h5', 'themeum-core'),
                        'compiler'      => false,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => true, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => true, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        // 'text-align'    => false,
                        'line-height'   => false,
                        'word-spacing'  => false,  // Defaults to false
                        'letter-spacing'=> false,  // Defaults to false
                        'color'         => true,
                        'preview'       => true, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        =>array('h5'),
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Select your website Headings Font', 'themeum-core'),
                        'default'       => array(
                            'color'         => '#000',
                            'font-weight'    => '700',
                            'font-family'   => 'Open Sans',
                            'google'        => true,
                            'font-size'     => '18px'),
                    ),    

                )
            );




            /**********************************
            ********* Social Media Link ***********
            ***********************************/

            $this->sections[] = array(
                'icon'      => 'el-icon-asterisk',
                'icon_class' => 'el-icon-large', 
                'title'     => esc_html__('Social Media', 'themeum-core'),
                'fields'    => array(
                 

                    array(
                        'id'        => 'wp-facebook',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Facebook URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-twitter',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Twitter URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-google-plus',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Google Plus URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-pinterest',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Pinterest URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-youtube',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Youtube URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-linkedin',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Linkedin URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-dribbble',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Dribbble URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-behance',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Behance URL', 'themeum-core'),
                    ), 
                    array(
                        'id'        => 'wp-flickr',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Flickr URL', 'themeum-core'),
                    ), 
                    array(
                        'id'        => 'wp-vk',
                        'type'      => 'text',
                        'title'     => esc_html__('Add vk URL', 'themeum-core'),
                    ),  
                    array(
                        'id'        => 'wp-skype',
                        'type'      => 'text',
                        'title'     => esc_html__('Add skype URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'wp-instagram',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Instagram URL', 'themeum-core'),
                    ),

                )
            );




            /**********************************
            ********* Coming Soon  ***********
            ***********************************/

            $this->sections[] = array(
                'icon'      => 'el-icon-time',
                'icon_class' => 'el-icon-large',                  
                'title'     => esc_html__('Coming Soon', 'themeum-core'),
                'fields'    => array(

                    array(
                        'id'        => 'comingsoon-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Enable Coming Soon', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable or disable coming soon mode', 'themeum-core'),
                        'default'   => false,
                    ),

                    array(
                        'id'        => 'comingsoon-date',
                        'type'      => 'date',
                        'title'     => esc_html__('Coming Soon date', 'themeum-core'),
                        'subtitle' => esc_html__('Coming Soon Date', 'themeum-core'),
                        'default'   => esc_html__('08/30/2018', 'themeum-core')
                        
                    ),

                    array(
                        'id'        => 'comingsoon-title',
                        'type'      => 'text',
                        'title'     => esc_html__('Title', 'themeum-core'),
                        'subtitle' => esc_html__('Coming Soon Title', 'themeum-core'),
                        'default'   => esc_html__("Coming Soon", 'themeum-core')
                    ),

                    array(
                        'id'        => 'comingsoon-subtitle',
                        'type'      => 'text',
                        'title'     => esc_html__('Sub Title', 'themeum-core'),
                        'subtitle' => esc_html__('Coming Soon Sub Title', 'themeum-core'),
                        'default'   => esc_html__("We are working on something awesome!", 'themeum-core')
                    ),                    

                    array(
                        'id'        => 'comingsoon-copyright',
                        'type'      => 'text',
                        'title'     => esc_html__('Coming Soon Copyright', 'themeum-core'),
                        'subtitle' => esc_html__('Coming Soon Copyright Text', 'themeum-core'),
                        'default'   => esc_html__("Copyright 2016 © Moview. All Rights Reserved", 'themeum-core')
                    ),
                    array(
                        'id'        => 'comingsoon-facebook',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Facebook URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-twitter',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Twitter URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-google-plus',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Google Plus URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-pinterest',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Pinterest URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-youtube',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Youtube URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-linkedin',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Linkedin URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-dribbble',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Dribbble URL', 'themeum-core'),
                    ),
                    array(
                        'id'        => 'comingsoon-instagram',
                        'type'      => 'text',
                        'title'     => esc_html__('Add Instagram URL', 'themeum-core'),
                    ),

                )
            );


            /**********************************
            ********* Blog  ***********
            ***********************************/

            $this->sections[] = array(
                'icon'      => 'el-icon-edit',
                'icon_class' => 'el-icon-large',                  
                'title'     => esc_html__('Blog', 'themeum-core'),
                'fields'    => array(

                    array(
                        'id'        => 'blog-social',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Single Page Social Share', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable or disable blog social share for single page', 'themeum-core'),
                        'default'   => true,
                    ),                     

                    array(
                        'id'        => 'blog-view',
                        'type'      => 'switch',
                        'title'     => esc_html__('Post View Count', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable or disable Post View Count', 'themeum-core'),
                        'default'   => true,
                    ),                 

                    array(
                        'id'        => 'blog-author',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Author', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Blog Author ex. Admin', 'themeum-core'),
                        'default'   => true,
                    ),

                    array(
                        'id'        => 'blog-date',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Date', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Blog Date ', 'themeum-core'),
                        'default'   => true,
                    ),

                    array(
                        'id'        => 'blog-category',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Category', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable or disable blog category', 'themeum-core'),
                        'default'   => true,
                    ),                     

                    array(
                        'id'        => 'blog-comment',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Comment', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable or disable blog Comment', 'themeum-core'),
                        'default'   => true,
                    ), 


                    array(
                        'id'        => 'blog-tag',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Tag', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Blog Tag ', 'themeum-core'),
                        'default'   => true,
                    ),  

                    array(
                        'id'        => 'blog-single-comment-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Single Post Comment', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Single post comment ', 'themeum-core'),
                        'default'   => true,
                    ),

                    array(
                        'id'        => 'post-nav-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Post navigation', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Post navigation ', 'themeum-core'),
                        'default'   => true,
                    ),

                    array(
                        'id'        => 'blog-continue-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Blog Readmore', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Blog Readmore', 'themeum-core'),
                        'default'   => true,
                    ),

                    array(
                        'id'        => 'blog-continue',
                        'type'      => 'text',
                        'title'     => esc_html__('Continue Reading', 'themeum-core'),
                        'subtitle' => esc_html__('Continue Reading', 'themeum-core'),
                        'default'   => esc_html__('Continue Reading', 'themeum-core'),
                        'required'  => array('blog-continue-en', "=", 1),
                    ),  

                )
            );




            /* *********************************
            ************** Footer **************
            ********************************** */

            $this->sections[] = array(
                'icon'      => 'el-icon-bookmark',
                'icon_class' => 'el-icon-large', 
                'title'     => esc_html__('Footer', 'themeum-core'),
                'fields'    => array(
                 
                    array(
                        'id'        => 'copyright-en',
                        'type'      => 'switch',
                        'title'     => esc_html__('Copyright', 'themeum-core'),
                        'subtitle'  => esc_html__('Enable Copyright Text', 'themeum-core'),
                        'default'   => true,
                    ),                    

                    array(
                        'id'        => 'copyright-text',
                        'type'      => 'editor',
                        'title'     => esc_html__('Copyright Text', 'themeum-core'),
                        'subtitle'  => esc_html__('Add Copyright Text', 'themeum-core'),
                        'default'   => esc_html__('© 2016 Your Company. All Rights Reserved. Designed By THEMEUM', 'themeum-core'),
                        'required'  => array('copyright-en', "=", 1),
                    ),
                    array( 
                        'id'        => 'copyright_top_border_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.footer-wrap .footer .row'),
                        'title'     => esc_html__('Copyright Top Border Color','themeum-core'),
                        'default'   => '#4d4752',
                        'transparent'=>false,
                        'required'  => array('copyright-en', "=", 1),
                    ),
                    array( 
                        'id'        => 'copyright_text_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.footer .text-right'),
                        'title'     => esc_html__('Copyright Text','themeum-core'),
                        'default'   => '#999',
                        'transparent'=>false,
                        'required'  => array('copyright-en', "=", 1),
                    ),
                    array( 
                        'id'        => 'copyright_link_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.menu-footer-menu li a'),
                        'title'     => esc_html__('Link Color for Copyright','themeum-core'),
                        'default'   => '#999',
                        'transparent'=>false,
                        'required'  => array('copyright-en', "=", 1),
                    ),
                    array( 
                        'id'        => 'copyright_link_color_hvr', 
                        'type'      => 'color',
                        'output'    => array('color' => '.menu-footer-menu li a:hover'),
                        'title'     => esc_html__('Link Hover Color for Copyright','themeum-core'),
                        'default'   => '#999',
                        'transparent'=>false,
                        'required'  => array('copyright-en', "=", 1),
                    ),
                    array( 
                        'id'        => 'mv_footer_padding', 
                        'type'      => 'spacing',
                        'mode'      => 'padding',
                        'units'     => array('em', 'px'),
                        'output'    => array('.bottom'),
                        'title'     => esc_html__('Footer Padding','themeum-core'),
                        'subtitle'  => esc_html__('Footer Padding Top &amp; Bottom', 'themeum-core'),
                        'left'      => false,
                        'right'     => false,
                        'default'            => array(
                            'padding-top'     => '100px', 
                            'padding-bottom'  => '80px', 
                            'units'          => 'px', 
                        ),
                    ),

                    array( 
                        'id'        => 'footer_title_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.bottom-widget .widget .widget_title'),
                        'title'     => esc_html__('Footer Widget Title Color','themeum-core'),
                        'default'   => '#fff',
                        'transparent'=>false,
                    ),

                    array( 
                        'id'        => 'footer_title_icon_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.bottom-widget .widget .widget_title:before'),
                        'title'     => esc_html__('Footer Widget Title Icon Color','themeum-core'),
                        'default'   => '#fff',
                        'transparent'=>false,
                    ),

                    array( 
                        'id'        => 'footer_text_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.bottom'),
                        'title'     => esc_html__('Footer Widget Text Color','themeum-core'),
                        'default'   => '#fff',
                        'transparent'=>false,
                    ),

                    array( 
                        'id'        => 'footer_link_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.bottom-widget .widget ul li a'),
                        'title'     => esc_html__('Footer Widget Link Color','themeum-core'),
                        'default'   => '#a6a6a6',
                        'transparent'=>false,
                    ),
                    array( 
                        'id'        => 'footer_link_hover_color', 
                        'type'      => 'color',
                        'output'    => array('color' => '.bottom-widget .widget ul li a:hover'),
                        'title'     => esc_html__('Footer Widget Link Hover Color','themeum-core'),
                        'default'   => '#fff',
                        'transparent'=>false,
                    ),
                )
            );


            /**********************************
            ********* Custom CSS & JS ***********
            ***********************************/
            $this->sections[] = array(
                'title'     => esc_html__('Custom CSS', 'themeum-core'),
                'icon'      => 'el-icon-bookmark',
                'icon_class' => 'el-icon-large',
                'fields'    => array(

                    array(
                        'id'        => 'custom_css',
                        'type'      => 'ace_editor',
                        'mode'      => 'css',
                        'title'     => __('Custom CSS', 'themeum-core'),
                        'subtitle' => __('Add some custom CSS', 'themeum-core'),
                        'default'   => '',
                    ),
                )
            );




            /**********************************
            ********* Import / Export ***********
            ***********************************/

            $this->sections[] = array(
                'title'     => esc_html__('Import / Export', 'themeum-core'),
                'desc'      => esc_html__('Import and Export your Theme Options settings from file, text or URL.', 'themeum-core'),
                'icon'      => 'el-icon-refresh',
                'fields'    => array(
                    array(
                        'id'            => 'opt-import-export',
                        'type'          => 'import_export',
                        'title'         => esc_html__('Import Export','themeum-core'),
                        'subtitle'      => 'Save and restore your Redux options',
                        'full_width'    => false,
                    ),
                ),
            ); 

        }

        public function setHelpTabs() {

            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-1',
                'title'     => esc_html__('Theme Information 1', 'themeum-core'),
                'content'   => esc_html__('<p>This is the tab content, HTML is allowed.</p>', 'themeum-core')
            );

            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-2',
                'title'     => esc_html__('Theme Information 2', 'themeum-core'),
                'content'   => esc_html__('<p>This is the tab content, HTML is allowed.</p>', 'themeum-core')
            );

            // Set the help sidebar
            $this->args['help_sidebar'] = esc_html__('<p>This is the sidebar content, HTML is allowed.</p>', 'themeum-core');
        }

        /**

          All the possible arguments for Redux.
          For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

         * */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name'          => 'themeum_options',            // This is where your data is stored in the database and also becomes your global variable name.
                'display_name'      => $theme->get('Name'),     // Name that appears at the top of your panel
                'display_version'   => $theme->get('Version'),  // Version that appears at the top of your panel
                'menu_type'         => 'menu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
                'menu_title'        => esc_html__('Theme Options', 'themeum-core'),
                'page_title'        => esc_html__('Theme Options', 'themeum-core'),
                
                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' => '', // Must be defined to add google fonts to the typography module
                
                'async_typography'  => false,                    // Use a asynchronous font on the front end or font string
                'admin_bar'         => true,                    // Show the panel pages on the admin bar
                'global_variable'   => '',                      // Set a different name for your global variable other than the opt_name
                'dev_mode'          => false,                    // Show the time the page took to load, etc
                'customizer'        => true,                    // Enable basic customizer support
                //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
                //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

                // OPTIONAL -> Give you extra features
                'page_priority'     => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent'       => 'themes.php',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
                'menu_icon'         => '',                      // Specify a custom URL to an icon
                'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
                'page_icon'         => 'icon-themes',           // Icon displayed in the admin panel next to your menu_title
                'page_slug'         => '_options',              // Page slug used to denote the panel
                'save_defaults'     => true,                    // On load save the defaults to DB before user clicks save or not
                'default_show'      => false,                   // If true, shows the default value next to each field that is not the default value.
                'default_mark'      => '',                      // What to print by the field's title if the value shown is default. Suggested: *
                'show_import_export' => true,                   // Shows the Import/Export panel when not used as a field.
                
                // CAREFUL -> These options are for advanced use only
                'transient_time'    => 60 * MINUTE_IN_SECONDS,
                'output'            => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag'        => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
                
                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database'              => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'system_info'           => false, // REMOVE

                // HINTS
                'hints' => array(
                    'icon'          => 'icon-question-sign',
                    'icon_position' => 'right',
                    'icon_color'    => 'lightgray',
                    'icon_size'     => 'normal',
                    'tip_style'     => array(
                        'color'         => 'light',
                        'shadow'        => true,
                        'rounded'       => false,
                        'style'         => '',
                    ),
                    'tip_position'  => array(
                        'my' => 'top left',
                        'at' => 'bottom right',
                    ),
                    'tip_effect'    => array(
                        'show'          => array(
                            'effect'        => 'slide',
                            'duration'      => '500',
                            'event'         => 'mouseover',
                        ),
                        'hide'      => array(
                            'effect'    => 'slide',
                            'duration'  => '500',
                            'event'     => 'click mouseleave',
                        ),
                    ),
                )
            );
         }

    }
    
    global $reduxConfig;
    $reduxConfig = new Redux_Framework_sample_config();
}

/**
  Custom function for the callback referenced above
 */
if (!function_exists('redux_my_custom_field')):
    function redux_my_custom_field($field, $value) {
        print_r($field);
        echo '<br/>';
        print_r($value);
    }
endif;

/**
  Custom function for the callback validation referenced above
 * */
if (!function_exists('redux_validate_callback_function')):
    function redux_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';

        /*
          do your validation

          if(something) {
            $value = $value;
          } elseif(something else) {
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }
endif;
