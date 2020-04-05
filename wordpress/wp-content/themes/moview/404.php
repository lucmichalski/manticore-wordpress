<?php get_header('alternative'); 
/*
*Template Name: 404 Page Template
*/
?>

<section class="error-page-inner">
    <div>
        <div class="error-msg">
            <?php if( moview_options_url('errorpage','url') ): ?>
                <div class="logo-top">
                    <img src="<?php echo esc_url( moview_options_url('errorpage','url') ); ?>" alt="<?php  esc_html_e( '404 error', 'moview' ); ?>">
                </div>
            <?php endif; ?>
            <p class="error-message"><?php  esc_html_e( 'Page not found.', 'moview' ); ?></p>
            <a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url('/') ); ?>" title="<?php  esc_html_e( 'HOME', 'moview' ); ?>"><i class="fa fa-chevron-left"></i> <?php  esc_html_e( 'Go Back Home', 'moview' ); ?></a>
        </div>
    </div>
</section>

<?php get_footer('alternative'); ?>