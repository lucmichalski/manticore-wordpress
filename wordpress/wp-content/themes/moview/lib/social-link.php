
<div class="offcanvas-social-share">
    <ul>
        <?php if ( moview_options('wp-facebook') ) { ?>
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-facebook')); ?>"><i class="fa fa-facebook"></i></a></li>
        <?php } ?>   
        <?php if ( moview_options('wp-twitter') ) { ?>
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-twitter')); ?>"><i class="fa fa-twitter"></i></a></li>
        <?php } ?>    
        <?php if ( moview_options('wp-google-plus') ) { ?>
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-google-plus')); ?>"><i class="fa fa-google-plus"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-pinterest') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-pinterest')); ?>"><i class="fa fa-pinterest"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-youtube') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-youtube')); ?>"><i class="fa fa-youtube"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-linkedin') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-linkedin')); ?>"><i class="fa fa-linkedin"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-dribbble') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-dribbble')); ?>"><i class="fa fa-dribbble"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-behance') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-behance')); ?>"><i class="fa fa-behance"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-flickr') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-flickr')); ?>"><i class="fa fa-flickr"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-vk') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-vk')); ?>"><i class="fa fa-vk"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-skype') ) { ?>  
        <li><a target="_blank" href="skype:#<?php echo esc_url(moview_options('wp-skype')); ?>?chat"><i class="fa fa-skype"></i></a></li>
        <?php } ?>
        <?php if ( moview_options('wp-instagram') ) { ?>  
        <li><a target="_blank" href="<?php echo esc_url(moview_options('wp-instagram')); ?>"><i class="fa fa-instagram"></i></a></li>
        <?php } ?>
    </ul>
</div>