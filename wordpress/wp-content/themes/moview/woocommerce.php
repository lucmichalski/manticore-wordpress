<?php get_header(); ?>
<section id="main">
    <?php get_template_part('lib/sub-header')?>
    <div class="container">
    	<div class="row">
            <div id="content" class="col-md-9" role="main">
                <div class="site-content">
                    <?php woocommerce_content(); ?>
                </div>
            </div> <!-- #content -->

            <div id="sidebar" class="col-md-3" role="complementary">
                <aside class="widget-area">
                    <?php dynamic_sidebar('shop');?>
                </aside>
            </div>
        </div> <!-- .row -->
    </div> <!-- .container -->
</section>

<?php get_footer(); ?>