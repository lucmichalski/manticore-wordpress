<?php 
/**
* Template Name: Blog Right Sidebar 
*/
get_header();?>

<section id="main">

    <?php get_template_part('lib/sub-header'); ?>

    <div class="container">
        <div class="row">

            <div id="content" class="site-content col-md-9" role="main">
                <?php

                # Query for FontPage and default template. 
                if (is_front_page()) {
                    $paged = (get_query_var('page')) ? get_query_var('page') : 1;
                }else{
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                }
                
                $args = array('post_type' => 'post','paged' => $paged);
                query_posts($args); 

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
            </div>
            <?php get_sidebar(); ?>

        </div> <!-- .row -->
    </div><!-- .container -->
</section> 

<?php get_footer();