<?php
/*
 * Template Name: Archive Template
 */

get_header(); ?>

<section id="main">

    <?php get_template_part('lib/sub-header'); ?>

    <div class="container">
        <div class="row">
            <div id="content" class="site-content col-md-12" role="main">
                <div class="archive-wrap">
                    <div class="row">
                        <div class="col-md-4">
                            <h3><?php esc_html_e('Latest Post','moview'); ?></h3>
                            <?php global $post; ?>
                            <?php $latest_post = get_posts(array('post_per_page' => 10, 'post_type' => 'post'));
                            if(count($latest_post)){ ?>
                            <ul class="latest-post">
                                <?php foreach ($latest_post as $post) {
                                    setup_postdata( $post ); 
                                ?>
                                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                                <?php } ?>
                            </ul>
                            <?php
                                }
                                wp_reset_postdata();
                            ?>
                        </div>
                        <div class="col-md-4">
                            <h3><?php esc_html_e('Archive by Monthly', 'moview');?></h3>
                            <ul class="archive-month">
                                <?php wp_get_archives(array( 'type' => 'monthly')); ?>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h3><?php esc_html_e('Archive by Categories','moview'); ?></h3>
                            <ul class="archive-cats">
                                <?php wp_list_categories(array( 'title_li' =>'' )); ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> <!-- #content -->
        </div> <!-- .row -->
    </div> <!-- .container -->
</section>

<?php get_footer();