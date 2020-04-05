<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php if (!is_single() ) { ?>
        <div class="entry-content-list-blog">
            <div class="featured-wrap">
                <div class="entry-content-gallery">
                    <?php if(function_exists('rwmb_meta')){ ?>
                        <?php  if ( get_post_meta( get_the_ID(),'themeum_gallery_images',false) ) { ?>
                            <?php $slides = get_post_meta( get_the_ID(),'themeum_gallery_images',false); ?>
                                <?php if(count($slides) > 0) { ?>
                                    <div id="blog-gallery-slider<?php echo get_the_ID(); ?>" class="carousel slide blog-gallery-slider">
                                        <!-- Wrapper for slides -->
                                        <div class="carousel-inner">
                                            <?php $slide_no = 1; ?>
                                            <?php foreach( $slides as $slide ) { ?>
                                            <div class="item <?php if($slide_no == 1) echo 'active'; ?>">
                                                <?php $images = wp_get_attachment_image_src( $slide, 'moview-large' ); ?>
                                                <img class="img-responsive" src="<?php echo esc_url($images[0]); ?>" alt="<?php  esc_html_e( 'image', 'moview' ); ?>">
                                            </div>
                                            <?php $slide_no++; ?>
                                            <?php } ?>
                                        </div>
                                        <!-- Controls -->
                                        <a class="left carousel-left" href="#blog-gallery-slider<?php echo get_the_ID(); ?>" data-slide="prev">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                        <a class="right carousel-right" href="#blog-gallery-slider<?php echo get_the_ID(); ?>" data-slide="next">
                                            <i class="fa fa-angle-right"></i>
                                        </a>
                                    </div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div><!--/.entry-content-gallery-->
            </div>
            <?php get_template_part( 'post-format/entry-content' );?>
        </div> 
    <?php } ?> 
    <?php if ( is_single() ) { ?>
        <div class="entry-content-list video-postformat">
            <div class="entry-content-gallery">
                <?php if(function_exists('rwmb_meta')){ ?>
                    <?php  if ( get_post_meta( get_the_ID(),'themeum_gallery_images',false) ) { ?>
                        <?php $slides = get_post_meta( get_the_ID(),'themeum_gallery_images',false); ?>
                            <?php if(count($slides) > 0) { ?>
                                <div id="blog-gallery-slider<?php echo get_the_ID(); ?>" class="carousel slide blog-gallery-slider">
                                    <!-- Wrapper for slides -->
                                    <div class="carousel-inner">
                                        <?php $slide_no = 1; ?>
                                        <?php foreach( $slides as $slide ) { ?>
                                        <div class="item <?php if($slide_no == 1) echo 'active'; ?>">
                                            <?php $images = wp_get_attachment_image_src( $slide, 'moview-large' ); ?>
                                            <img class="img-responsive" src="<?php echo esc_url($images[0]); ?>" alt="<?php  esc_html_e( 'image', 'moview' ); ?>">
                                        </div>
                                        <?php $slide_no++; ?>
                                        <?php } ?>
                                    </div>
                                    <!-- Controls -->
                                    <a class="left carousel-left" href="#blog-gallery-slider<?php echo get_the_ID(); ?>" data-slide="prev">
                                        <i class="fa fa-angle-left"></i>
                                    </a>
                                    <a class="right carousel-right" href="#blog-gallery-slider<?php echo get_the_ID(); ?>" data-slide="next">
                                        <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div><!--/.entry-content-gallery-->
            <div class="container">
                <?php get_template_part( 'post-format/entry-content' ); ?>
            </div>
        </div>
    <?php } ?> 
   
    

    <div class="container">
        <div class="row">
            <div class="col-sm-12"> 
                <?php get_template_part( 'post-format/entry-content-single' ); ?> 
            </div>
        </div>
    </div>
</article> <!--/#post -->



