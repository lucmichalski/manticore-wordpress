<?php 
/*
 * Template Name: Coming Soon
 */
get_header('alternative'); ?>

<?php
    $comingsoon_date = '';
    if ( moview_options('comingsoon-date') ) {
        $comingsoon_date = esc_attr( moview_options('comingsoon-date') );
    }
?>

<div class="comingsoon" style="background-image: url(<?php echo esc_url( moview_options_url('comingsoon','url')); ?>);">
    <div class="container">
        <div class="comingsoon-wrap text-center">
            <div class="comingsoon-content">
                <script type="text/javascript">
                jQuery(function($) {
                    $('#comingsoon-countdown').countdown("<?php echo str_replace('-', '/', $comingsoon_date); ?>", function(event) {
                        $(this).html(event.strftime('<div class="countdown-section"><span class="countdown-amount first-item countdown-days">%-D </span><span class="countdown-period">%!D:<?php echo esc_html__("DAY", "moview"); ?>,<?php echo esc_html__("DAYS", "moview"); ?>;</span></div><div class="countdown-section"><span class="countdown-amount countdown-hours">%-H </span><span class="countdown-period">%!H:<?php echo esc_html__("HOUR", "moview"); ?>,<?php echo esc_html__("HOURS", "moview"); ?>;</span></div><div class="countdown-section"><span class="countdown-amount countdown-minutes">%-M </span><span class="countdown-period">%!M:<?php echo esc_html__("MINUTE", "moview"); ?>,<?php echo esc_html__("MINUTES", "moview"); ?>;</span></div><div class="countdown-section"><span class="countdown-amount countdown-seconds">%-S </span><span class="countdown-period">%!S:<?php echo esc_html__("SECOND", "moview"); ?>,<?php echo esc_html__("SECONDS", "moview"); ?>;</span></div>'));
                    });
                });
                </script>

                <?php if( moview_options_url('comingsoon-logo','url')): ?>
                    <div class="logo-top">
                        <img src="<?php echo esc_url( moview_options_url('comingsoon-logo','url')); ?>" alt="<?php  esc_html_e( '404 error', 'moview' ); ?>">
                    </div>
                <?php endif; ?>

                <?php if ( moview_options('comingsoon-subtitle') ){?>
                <p class="sub-page-header"><?php echo balanceTags( moview_options('comingsoon-subtitle' )); ?></p>
                <?php }?>
                
                <?php if ( moview_options('comingsoon-title') ){?>
                <h2 class="soon-page-header"><?php echo balanceTags( moview_options('comingsoon-title')); ?></h2>
                <?php }?> 

                <div id="comingsoon-countdown"></div>
                <div class="text-center comingsoon-footer">
                    <div class="social-share">
                        <ul>
                            <?php if ( moview_options('comingsoon-facebook') ) { ?>
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-facebook')); ?>"><i class="fa fa-facebook"></i></a></li>
                            <?php } ?>   
                            <?php if ( moview_options('comingsoon-twitter') ) { ?>
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-twitter')); ?>"><i class="fa fa-twitter"></i></a></li>
                            <?php } ?>    
                            <?php if ( moview_options('comingsoon-google-plus') ) { ?>
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-google-plus')); ?>"><i class="fa fa-google-plus"></i></a></li>
                            <?php } ?>
                            <?php if ( moview_options('comingsoon-pinterest') ) { ?>  
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-pinterest')); ?>"><i class="fa fa-pinterest"></i></a></li>
                            <?php } ?>
                            <?php if ( moview_options('comingsoon-youtube') ) { ?>  
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-youtube')); ?>"><i class="fa fa-youtube"></i></a></li>
                            <?php } ?>
                            <?php if ( moview_options('comingsoon-linkedin') ) { ?>  
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-linkedin')); ?>"><i class="fa fa-linkedin"></i></a></li>
                            <?php } ?>
                            <?php if ( moview_options('comingsoon-dribbble') ) { ?>  
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-dribbble')); ?>"><i class="fa fa-dribbble"></i></a></li>
                            <?php } ?>
                            <?php if ( moview_options('comingsoon-instagram') ) { ?>  
                            <li><a target="_blank" href="<?php echo esc_url(moview_options('comingsoon-instagram')); ?>"><i class="fa fa-instagram"></i></a></li>
                            <?php } ?>
                        </ul>
                    </div> <!--/.social-share-->
                    <?php if ( moview_options('comingsoon-copyright') ){?>
                        <div class="copyright-text">
                            <p><?php echo moview_options('comingsoon-copyright'); ?></p>
                        </div>
                    <?php }?>   
                </div><!--/.comingsoon-footer-->
            </div><!--/.comingsoon-content-->
        </div><!--/.comingsoon-wrap-->
    </div><!--/.container-->
</div><!--/.comingsoon-->


<?php get_footer('alternative');