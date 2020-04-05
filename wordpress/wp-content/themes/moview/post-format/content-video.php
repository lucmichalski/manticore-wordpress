<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php if (!is_single() ) { ?>
        <div class="entry-content-list-blog">
            <div class="featured-wrap">
                <?php if(function_exists('rwmb_meta')){ ?>
                    <?php  if ( get_post_meta( get_the_ID(), 'themeum_video',true ) ) { ?>
                        <div class="entry-video embed-responsive embed-responsive-16by9">
                            <?php $video_source = esc_attr(get_post_meta( get_the_ID(), 'themeum_video_source',true )); ?>
                            <?php $video = get_post_meta( get_the_ID(), 'themeum_video',true ); ?>
                            <?php if($video_source == 1): ?>
                                <?php echo get_post_meta( get_the_ID(), 'themeum_video',true ); ?>
                            <?php elseif ($video_source == 2): ?>
                                <?php echo '<iframe width="100%" height="350" src="http://www.youtube.com/embed/'.esc_attr($video).'?rel=0&showinfo=0&modestbranding=1&hd=1&autohide=1&color=white"  allowfullscreen></iframe>'; ?>
                            <?php elseif ($video_source == 3): ?>
                                <?php echo '<div class="embed-responsive embed-responsive-16by9"><iframe src="http://player.vimeo.com/video/'.esc_attr($video).'?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>'; ?>
                            <?php endif; ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php get_template_part( 'post-format/entry-content' );?>
        </div>
    <?php } ?>   

    <?php if ( is_single() ) { ?>
        <div class="entry-content-list video-postformat">
            <?php if(function_exists('rwmb_meta')){ ?>
                <?php  if ( get_post_meta( get_the_ID(), 'themeum_video',true ) ) { ?>
                    <div class="entry-video embed-responsive embed-responsive-16by9">
                        <?php $video_source = esc_attr(get_post_meta( get_the_ID(), 'themeum_video_source',true )); ?>
                        <?php $video = get_post_meta( get_the_ID(), 'themeum_video',true ); ?>
                        <?php if($video_source == 1): ?>
                            <?php echo get_post_meta( get_the_ID(), 'themeum_video',true ); ?>
                        <?php elseif ($video_source == 2): ?>
                            <?php echo '<iframe width="100%" height="350" src="http://www.youtube.com/embed/'.esc_attr($video).'?rel=0&showinfo=0&modestbranding=1&hd=1&autohide=1&color=white"  allowfullscreen></iframe>'; ?>
                        <?php elseif ($video_source == 3): ?>
                            <?php echo '<iframe src="http://player.vimeo.com/video/'.esc_attr($video).'?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff" width="100%" height="350" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'; ?>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            <?php } ?>
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