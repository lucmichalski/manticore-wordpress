<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">    
	<?php wp_head(); ?>
   
</head>
<body  <?php body_class(); ?>>
  <?php if( get_theme_mod( 'enable_loader',0) ){?> 
    <div id="preloader">
      <div id="status">&nbsp;</div>
    </div>
  <?php }?>  
    <header id="header"  class="header <?php if(  get_theme_mod( 'fixed_header',1)  ){echo 'fixed';}?>">
        <div class="header-wrap  <?php if(  get_theme_mod( 'box_header_center',0) ){echo 'container';}else{ echo 'plr30';}?>">
            <div class="theta-logo">
				<?php if ( has_custom_logo() ){?>
                <div class="theta-logo-img">	
                    <?php the_custom_logo();?>
                </div>
               <?php }?> 

                <div class="theta-logo-text">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><span class="blog-name"><?php bloginfo('name'); ?></span></a><br>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><span class="blog-description"><?php bloginfo('description'); ?></span></a>
                </div>
            </div>

            <nav id="theta-menu" class="theta-menu">
                <div class="menu-icon"><i class="icon-menu"></i><i class="icon-close"></i></div>
                <!-- Mobile button -->
                
                <ul class="menu">
                <?php if(  get_theme_mod( 'enable_search',1)  ){?>
                <li><a class="theta-search" href="javascript:;"><i class="icon-search"></i></a></li>
                <?php }?>
                <span>
                <?php
				
					if ( has_nav_menu( 'header-menu' ) ) {
                    	 wp_nav_menu( array( 'theme_location' => 'header-menu', 'items_wrap' => '%3$s','container' => false  ) );
                  	}
				?>
                </span>                 
                </ul>
                
                <ul class="menu-mobile">
                </ul> 
                
                <?php if(  get_theme_mod( 'enable_search',1)  ){?>
                <div id="theta-top-search">
                	<span class="theta-close-search-field"></span>
                <?php 
                        get_search_form();
                ?>
                
                </div>
                <?php }?>
                                
                    
            </nav>
            
 
        </div><!--div class="header-wrap"-->
  
	</header>