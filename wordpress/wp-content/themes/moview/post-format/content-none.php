<div class="page-content">
    <?php if ( is_home() && current_user_can( 'publish_posts' ) ) { ?>

    <p><?php printf( esc_html__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'moview' ), admin_url( 'post-new.php' ) ); ?></p>

    <?php } elseif ( is_search() ) { ?>
    <img src="<?php echo esc_url(get_template_directory_uri().'/images/search-error.png'); ?>"  alt="<?php  esc_html_e( 'Error Search', 'moview' ); ?>">
    <h2 class="search-error-title"><?php esc_html_e( 'Nothing Found', 'moview' ); ?></h2>

    <p class="search-error-text"><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'moview' ); ?></p>
    <?php 
       if(isset($_GET['post_type'])){
            ?>
            <div class="course-search col-md-offset-2 col-sm-12 col-md-8">
                <form role="search" action="<?php echo esc_url(site_url('/')); ?>" method="get">
                    <input class="custom-input" type="search" name="s" placeholder="<?php esc_html_e( 'Find Course, Tutorials','moview' ); ?>"/>
                    <input type="submit" alt="Search" value="" class="transparent-button" />
                </form>
            </div>
            <?php
        }else{
            get_search_form();
        }  
    ?>

    <?php } else { ?>

    <p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'moview' ); ?></p>
    <?php get_search_form(); ?>

    <?php } ?>
</div><!-- .page-content -->