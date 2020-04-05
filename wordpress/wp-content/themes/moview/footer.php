<!-- start footer -->
<?php global $themeum_options; ?>
    <footer id="footer" class="footer-wrap">  
        <?php if(is_active_sidebar('bottom') && isset($themeum_options['bottom-section'])){ ?>
            <div class="bottom">
                <div class="container">
                    <div class="bottom-wrap clearfix">
                        <?php dynamic_sidebar('bottom'); ?>
                    </div>
                </div>
            </div><!--/#footer-->
        <?php } ?>  
       
        <?php if ( has_nav_menu( 'footernav' ) || moview_options('copyright-text') ) { ?>
            <div class="footer">
                <div class="container">
                    <div class="row">
                        <?php if ( has_nav_menu( 'footernav' ) ) { ?>
                            <div class="col-sm-6">
                                <?php    
                                $default = array( 'theme_location'  => 'footernav',
                                  'container'       => '', 
                                  'menu_class'      => 'menu-footer-menu',
                                  'menu_id'         => 'menu-footer-menu',
                                  'fallback_cb'     => 'wp_page_menu',
                                  'depth'           => 1
                                );
                                wp_nav_menu($default);
                                ?>
                            </div>
                        <?php } ?>    
                        <?php if( moview_options('copyright-text') ){ ?>   
                            <?php if (!has_nav_menu( 'footernav' ) ) { ?>
                            <div class="col-sm-12 text-center">
                            <?php } else{ ?>
                                <div class="col-sm-6 text-right">
                            <?php }?>
                                <?php echo balanceTags( moview_options('copyright-text') ); ?>
                            </div> <!-- end row -->
                        <?php } ?> 
                    </div> <!-- end row -->
                </div>  <!-- end container -->
            </div> <!-- end footer -->
        <?php } ?> 
    </footer>
</div> <!-- #page -->

<div id="moview-player">
    <div class="content-wrap">
        <div class="video-container">
            <span class="video-close">x</span>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>