<?php if ( is_single() ) { ?>
<div class="single-entry-summary clearfix">
   <?php the_content(); ?>
</div> <!-- //.entry-summary -->
<?php } else {
    if ( moview_options('blog-introtext')  ) { ?>
    <div class="post-entry-summary clearfix">
        <?php echo moview_excerpt_max_charlength(150);
        if( moview_options('blog-continue-en')){
            if ( moview_options('blog-continue-en') == 1 ) {
                if ( moview_options('blog-continue') ) {
                    $continue = esc_html( moview_options('blog-continue') );
                    echo '<p class="wrap-btn-style"><a class="btn btn-style" href="'.get_permalink().'">'. $continue .'</a></p>';
                } else {
                    echo '<p class="wrap-btn-style"><a class="btn btn-style" href="'.get_permalink().'">'. esc_html__( 'Continue Reading', 'moview' ) .'</a></p>';
                } 
            }
        } ?>
    </div>
    <?php }

} 
wp_link_pages( array(
    'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'moview' ) . '</span>',
    'after'       => '</div>',
    'link_before' => '<span>',
    'link_after'  => '</span>',
) );
?>

<?php if ( is_single() ) { ?>
<?php if ( moview_options('blog-social') || moview_options('blog-tag') ) { ?>
<div class="post-meta-info clearfix">
        <?php if ( moview_options('blog-social') ) { ?>
            <?php get_template_part( 'post-format/social-buttons' ); ?>
        <?php }?> 
    <?php if ( moview_options('blog-tag') ) { ?>
        <div class="post-meta-info-list pull-right">
            <span class="tags"><?php esc_html_e('Tags: ','moview');?></span>
            <?php the_tags('', ', ', '<br />'); ?>
        </div>   
    <?php }?> 
</div>
<?php } else {?>
    <div class="post-meta-info clearfix">
        <div class="post-meta-info-list pull-right">
            <span class="tags"><?php esc_html_e('Tags: ','moview');?></span>
            <?php the_tags('', ', ', '<br />'); ?>
        </div>   
    </div>
    <?php }?>  
<?php }?>



