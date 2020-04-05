<?php get_header(); ?>

<section id="main">
    <?php get_template_part('lib/sub-header')?>
    <div class="container">
        <div class="row">
            <div id="content" class="site-content col-sm-9" role="main">
                <?php
                    if ( have_posts() ) :
                        while ( have_posts() ) : the_post();
                            get_template_part( 'post-format/content', get_post_format() );
                        endwhile;
                    else:
                        get_template_part( 'post-format/content', 'none' );
                    endif;
                ?>
               <?php                                 
                $page_numb = max( 1, get_query_var('paged') );
                $max_page = $wp_query->max_num_pages;
                echo moview_pagination( $page_numb, $max_page ); 
                ?>
            </div> <!-- #content -->
            <?php get_sidebar(); ?>
        </div> <!-- .row -->
    </div>
</section> <!-- .container -->

<?php get_footer();