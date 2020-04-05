<?php
/**
* Template Name: Page With Right Sidebar
*/
get_header(); ?>
<section id="main">
<?php get_template_part('lib/sub-header')?>
<div class="container">
    <div class="row">
        <div id="content" class="site-content col-sm-9" role="main">

            <?php while ( have_posts() ): the_post(); ?>

            <div id="post-<?php the_ID(); ?>">

                <?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
                <div class="row">
                    <div class="entry-thumbnail col-md-12">
                        <?php the_post_thumbnail(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="entry-content row">
                <?php $content = get_the_content(); ?>
                <?php
                if(!has_shortcode($content, 'vc_row')){
                    ?>
                    <div class="col-md-12">
                        <?php the_content(); ?>
                    </div>
                    <?php
                }else{
                    the_content();
                }
                wp_link_pages(); ?>
            </div>

            </div>
            <?php
            if ( comments_open() || get_comments_number() ) :
                comments_template( '/comments-single.php' );
            endif;
            ?>
        <?php endwhile; ?>
    </div> <!--/#content-->

    <?php get_sidebar(); ?> <!--Page with Right Sidebar-->

</div>
</div>
</section> <!--/#main-->
<?php get_footer();