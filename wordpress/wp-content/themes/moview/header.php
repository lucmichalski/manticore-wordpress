<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php  if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) {
      if( moview_options('favicon') ){  ?>
        <link rel="shortcut icon" href="<?php echo esc_url( moview_options_url('favicon','url') ); ?>" type="image/x-icon"/>
      <?php }else{ ?> 
        <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri().'/images/plus.png'); ?>" type="image/x-icon"/>
      <?php }
    }
    ?>
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
  <?php wp_head(); ?>
</head>

 <?php
     $menu_style = 'none';
     if ( moview_options('boxfull-en') ) {
      $layout = esc_attr( moview_options('boxfull-en') );
     }else{
        $layout = 'fullwidth';
     }

     if (moview_options('menu-style') == 'classic' || (isset($_REQUEST['menu-style']) && $_REQUEST['menu-style'] == 'classic')) {
       $menu_style = '';
     }
 ?>

<body <?php body_class(); ?>>  

  <div id="page" class="hfeed site <?php echo esc_attr($layout); ?>">

<?php
  $sticky_id = (moview_options('menu-style') != 'classic' || (isset($_REQUEST['menu-style']) && $_REQUEST['menu-style'] != 'classic')) ? 'masthead' : '' ;
?>

<header id="<?php echo esc_attr( $sticky_id ); ?>" class="site-header header">
  <div class="container">
      <div class="row">
          <?php
            $logo_cls = ((int) moview_options('show_search_box') == false && (int) moview_options('show_login_menu') == false) ? 'col-sm-12' : 'col-sm-3' ;
            $logo_cls .= ((int) moview_options('show_search_box') == false && (int) moview_options('show_login_menu') == false && (int) moview_options('logo_center') == true) ? ' mv-logo-center' : '' ;
          ?>
          <div class="<?php echo esc_attr( $logo_cls ); ?> col-xs-12">

          <?php 

          if (isset( $_REQUEST['menu-style'])) {
            $menu = esc_attr($_REQUEST['menu-style']);
          } else {
            $menu = '';
          }

          if( moview_options('menu-style') && ($menu != 'regular') ){ ?>
              <?php if( moview_options('menu-style') == 'offcanvus' ){ $menu_style = 'found'; ?>
                  <div class="main-menu-wrap">
                    <button id="open-button" class="menu-button"><?php esc_html_e('Open','moview');?></button>
                  </div>
                  <div id="offcanvas-menu-wrapper">
                    <div class="menu-close-container">
                      <button class="close-button" id="close-button"><?php esc_html_e('Close','moview');?></button>
                    </div>
                    <div id="offcanvas-menu-inner">
                              <div id="offcanvas-menu">
                                    <div class="navbar-collapse">
                                        <?php 
                                        if ( has_nav_menu( 'primary' ) ) {
                                            wp_nav_menu( array(
                                                'theme_location'      => 'primary',
                                                'container'           => false,
                                                'menu_class'          => 'nav',
                                                'fallback_cb'         => 'wp_page_menu',
                                                'depth'               => 4,
                                                'walker'              => new wp_bootstrap_mobile_navwalker()
                                                )
                                            ); 
                                        }
                                        ?>
                                    </div>
                              </div><!--/.#mobile-menu-->
                    </div><!--/#offcanvas-menu-wrapper-->
                    <?php get_template_part('lib/social-link'); ?>
                  </div><!--/#offcanvas-menu-wrapper-->
                  
              <?php } ?>
          <?php } ?>
          

          <?php if( moview_options('menu-style') == 'classic' || (isset($_REQUEST['menu-style']) && $_REQUEST['menu-style'] == 'classic') ): ?>
            <div class="visible-xs">
              <div class="main-menu-wrap">
                <button id="open-button" class="menu-button"><?php esc_html_e('Open','moview');?></button>
              </div>
              <div id="offcanvas-menu-wrapper">
                <div class="menu-close-container">
                  <button class="close-button" id="close-button"><?php esc_html_e('Close','moview');?></button>
                </div>
                <div id="offcanvas-menu-inner">
                          <div id="offcanvas-menu">
                                <div class="navbar-collapse">
                                    <?php 
                                    if ( has_nav_menu( 'primary' ) ) {
                                        wp_nav_menu( array(
                                            'theme_location'      => 'primary',
                                            'container'           => false,
                                            'menu_class'          => 'nav',
                                            'fallback_cb'         => 'wp_page_menu',
                                            'depth'               => 4,
                                            'walker'              => new wp_bootstrap_mobile_navwalker()
                                            )
                                        ); 
                                    }
                                    ?>
                                </div>
                          </div><!--/.#mobile-menu-->
                </div><!--/#offcanvas-menu-wrapper-->
                <?php get_template_part('lib/social-link'); ?>
              </div><!--/#offcanvas-menu-wrapper-->
            </div>
          <?php endif; ?>

          
            <?php 
                $menutyle = moview_options('menu-style');
                if( !$menutyle || ( $menu_style == 'none' ) ){ ?>
              
                  <div class="main-menu-wrap">
                     <i class="fa fa-bars hidden-xs"></i>
                     <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                     </button>
                      <div id="main-menu" class="hidden-xs">
                          <?php 
                          if ( has_nav_menu( 'primary' ) ) {
                              wp_nav_menu(  array(
                                  'theme_location' => 'primary',
                                  'container'      => '', 
                                  'menu_class'     => 'nav',
                                  'fallback_cb'    => 'wp_page_menu',
                                  'depth'          => 4,
                                  'walker'         => new Megamenu_Walker()
                                  )
                              ); 
                          }
                          ?>
                      </div><!--/#main-menu-->                 
                      <div id="mobile-menu" class="visible-xs">
                          <div class="collapse navbar-collapse">
                              <?php 
                              if ( has_nav_menu( 'primary' ) ) {
                                  wp_nav_menu( array(
                                      'theme_location'      => 'primary',
                                      'container'           => false,
                                      'menu_class'          => 'nav navbar-nav',
                                      'fallback_cb'         => 'wp_page_menu',
                                      'depth'               => 4,
                                      'walker'              => new wp_bootstrap_mobile_navwalker()
                                      )
                                  ); 
                              }
                              ?>
                          </div>
                      </div><!--/.#mobile-menu-->
                    </div><!--/.main-menu-wrap-->
                  
              <?php } ?>

              <div class="navbar-header">
                  <div class="logo-wrapper">
                     <h1><a class="moview-navbar-brand" href="<?php echo esc_url(site_url()); ?>">
                          <?php
                              if ( moview_options('logo') )
                             {
                                  if( moview_options('logo-text-en') ) { ?>
                                      <h1> <?php echo esc_html( moview_options('logo-text') ); ?> </h1>
                                  <?php }
                                  else
                                  {
                                      $logo = moview_options('logo');
                                      if( !empty($logo) ) {
                                      ?>
                                          <img class="enter-logo img-responsive" src="<?php echo esc_url( moview_options_url('logo','url') ); ?>" alt="<?php  esc_html_e( 'Logo', 'moview' ); ?>" title="<?php  esc_html_e( 'Logo', 'moview' ); ?>">
                                      <?php
                                      }else{
                                          echo esc_html(get_bloginfo('name'));
                                      }
                                  }
                             }
                              else
                             {
                                  echo esc_html(get_bloginfo('name'));
                             }
                          ?>
                       </a></h1>
                  </div>     
              </div> 
          </div> <!--/logo .col-sm-2-->    
    
          <?php if( moview_options('show_login_menu') == true ): ?>
            <div class="col-xs-4 visible-xs">
                <div class="top-align top-user-login">
                    <?php if ( is_user_logged_in() ) { ?>
                        
                        <?php
                        $current_user = wp_get_current_user();
                        echo get_avatar( $current_user->user_email, 32 );

                        if( $current_user->display_name != '' ){
                            if( get_option('review_page_id') != '' ){ 
                                echo '<a class="review-page hidden-xs" href="'.get_permalink( get_option('review_page_id') ).'">';  
                            }
                            _e('Hi','moview');
                            echo ','. esc_attr($current_user->display_name);
                            if( get_option('review_page_id') != '' ){ echo '</a>';  }
                        }else{
                            if( get_option('review_page_id') != '' ){ 
                                echo '<a class="review-page hidden-xs" href="'.get_permalink( get_option('review_page_id') ).'">';
                            }
                            _e('Hi','moview');
                            echo ','. esc_attr( $current_user->user_login );
                            if( get_option('review_page_id') != '' ){ echo '</a>';  }
                        }
                        ?>

                        <a href="<?php echo wp_logout_url( esc_url( home_url('/') ) ); ?>"><?php _e('Logout','moview'); ?></a>
                    <?php } else { ?>
                          <i class="themeum-moviewuser"></i><span class="hidden-xs hidden-sm"><?php _e('Welcome Guest!','moview'); ?></span> <a href="#sign-in" data-toggle="modal" data-target="#sign-in"><?php _e('Login','moview'); ?></a>
                    <?php } ?>
                </div>
            </div> <!--/login .col-sm-3--> 
          <?php endif; ?>

          
          <?php
          if( moview_options('show_search_box') == true ):
            $offset_search = ((int) moview_options('show_login_menu') == false) ? 'col-md-offset-3' : '' ;
          ?>
            <div class="clearfix visible-xs"></div>
            <div class="col-sm-6 home-search <?php echo esc_attr( $offset_search ); ?>">
                <div class="home-search-btn">
                     <?php get_template_part('lib/moview-search'); ?>
                </div>
            </div>
          <?php endif; ?>
          
          <?php
          if( moview_options('show_login_menu') == true ):
            $offset_login = ((int) moview_options('show_search_box') == false) ? 'col-md-offset-6' : '' ;
          ?>
            <div class="col-sm-3 hidden-xs <?php echo esc_attr( $offset_login ); ?>">
                <div class="top-align top-user-login">
                    <?php if ( is_user_logged_in() ) { ?>

                    <?php
                    $current_user = wp_get_current_user();
                    echo get_avatar( $current_user->user_email, 32 );

                    if( $current_user->display_name != '' ){
                      if( get_option('review_page_id') != '' ){ echo '<a class="review-page" href="'.get_permalink( get_option('review_page_id') ).'">';  }
                        _e('Hi','moview');
                        echo ','. esc_attr( $current_user->display_name );
                      if( get_option('review_page_id') != '' ){ echo '</a>';  }
                    }else{
                      if( get_option('review_page_id') != '' ){ echo '<a class="review-page" href="'.get_permalink( get_option('review_page_id') ).'">';  }
                          _e('Hi','moview');
                          echo ','. esc_attr( $current_user->user_login );
                      if( get_option('review_page_id') != '' ){ echo '</a>';  }
                    }
                    ?>

                    <a href="<?php echo wp_logout_url( esc_url( home_url('/') ) ); ?>"><?php _e('Logout','moview'); ?></a>
                    <?php } else { ?>
                        <i class="themeum-moviewuser"></i><span class="hidden-xs hidden-sm"><?php _e('Welcome Guest!','moview'); ?></span> <a href="#sign-in" data-toggle="modal" data-target="#sign-in"><?php _e('Login','moview'); ?></a>
                    <?php } ?>
                </div>
            </div> <!--/login .col-sm-3-->
          <?php endif; ?>
      </div><!--/.row--> 
  </div><!--/.container--> 
</header><!--/.header-->

<?php if( moview_options('menu-style') == 'classic' || (isset($_REQUEST['menu-style']) && $_REQUEST['menu-style'] == 'classic') ): ?>
<div id="masthead" class="mv-classic-menu-container hidden-xs">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class=" mv-classic-menu">
            <?php 
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu(  array(
                    'theme_location' => 'primary',
                    'container'      => '', 
                    'menu_class'     => 'nav',
                    'fallback_cb'    => 'wp_page_menu',
                    'depth'          => 4,
                    'walker'         => new Megamenu_Walker()
                    )
                ); 
            }
            ?>
        </div><!--/#main-menu-->  
      </div>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- sign in form -->
<div id="sign-form">
     <div id="sign-in" class="modal fade">
        <div class="modal-dialog modal-md">
             <div class="modal-content">
                 <div class="modal-header">
                     <i class="fa fa-close close" data-dismiss="modal"></i>
                     <h1 class="title"><?php _e('Login','moview'); ?></h1>
                 </div>
                 <div class="modal-body">
                     <form id="login" action="login" method="post">
                        <div class="login-error alert alert-info" role="alert"></div>
                        <input type="text"  id="username" name="username" class="form-control" placeholder="<?php _e('User Name','moview'); ?>">
                        <input type="password" id="password" name="password" class="form-control" placeholder="<?php _e('Password','moview'); ?>">
                        <input type="submit" class="btn btn-success submit_button"  value="Login" name="submit">
                        <a class="lost-pass" href="<?php echo esc_url(wp_lostpassword_url()); ?>"><strong><?php _e('Forgot password?','moview'); ?></strong></a>
                        <p class="modal-footer"><?php _e('Not a member?','moview'); ?> <a href="<?php echo esc_url(get_permalink(get_option('register_page_id'))); ?>"><strong><?php _e('Join today','moview'); ?></strong></a></p>
                        <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
                     </form>
                 </div>
             </div>
         </div> 
     </div>
</div> <!-- end sign-in form -->

<div id="logout-url" class="hidden"><?php echo wp_logout_url( esc_url( home_url('/') )); ?></div>
    