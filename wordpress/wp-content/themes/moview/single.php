<?php get_header(); 
?>
<section id="main">
    <div id="content" class="site-content" role="main">
        <?php if ( have_posts() ) :  ?> 

            <?php while ( have_posts() ) : the_post(); ?>

                <?php get_template_part( 'post-format/content', get_post_format() ); ?>

                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                 <?php if ( moview_options('post-nav-en') ) { ?>
                                    <div class="clearfix post-navigation">
                                <?php previous_post_link('<span class="previous-post pull-left btn btn-primary">%link</span>','<i class="fa fa-angle-double-left"></i> '.esc_html__("Previous",'moview')); ?>
                                <?php next_post_link('<span class="next-post pull-right btn btn-primary">%link</span>',esc_html__("Next",'moview').' <i class="fa fa-angle-double-right"></i>'); ?>                                    
                                     </div> <!-- .post-navigation -->
                                <?php } ?>
                                
                                <?php
                                    if ( comments_open() || get_comments_number() ) {
                                        comments_template( '/comments-single.php' );
                                    }
                                ?>
                            </div>
                        </div>
                    </div> 

                <?php
                    if ( is_singular( 'post' ) ){
                        $count_post = esc_attr( get_post_meta( $post->ID, '_post_views_count', true) );
                        if( $count_post == ''){
                            $count_post = 1;
                            add_post_meta( $post->ID, '_post_views_count', $count_post);
                        }else{
                            $count_post = (int)$count_post + 1;
                            update_post_meta( $post->ID, '_post_views_count', $count_post);
                        }
                    }
                ?>
            <?php endwhile; ?>

            
        <?php else: ?>
            <?php get_template_part( 'post-format/content', 'none' ); ?>
        <?php endif; ?>
        <div class="clearfix"></div>
    </div> <!-- #content -->
</section> <!-- #main -->

<?php get_footer();