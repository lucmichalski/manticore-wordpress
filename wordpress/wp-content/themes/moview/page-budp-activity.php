<?php
/**
* Template Name: Buddypress Activity
*/
get_header(); ?>

<section id="main">
    
   <?php
        $output = '';
        if( moview_options_url('buddypress-ac-banner','url') ){
            $output = 'style="background-image:url('.esc_url( moview_options_url('buddypress-ac-banner','url')).');background-size: cover;background-position: 50% 50%;padding: 65px 0;"';
        }else{
            if( moview_options('blog-subtitle-bg-color') ){
                $output = 'style="background-color:'.esc_attr( moview_options('blog-subtitle-bg-color') ).';padding: 65px 0;"';
            }else{
                $output = 'style="background-color:#ccc; padding: 65px 0;"';
            }
        }
   ?>
   <div class="sub-title" <?php echo $output;?>>
        <div class="container">
            <div class="sub-title-inner">
                <div class="row">
                    <div class="col-sm-12">
                        <?php 
                        global $wp_query; 
                        if(isset($wp_query->queried_object->name)){
                            if($wp_query->queried_object->name != ''){
                                echo '<h2>'.$wp_query->queried_object->name.'</h2>';  
                            }else{
                                echo '<h2>'.get_the_title().'</h2>';
                            }
                        }else{
                            if(is_search()){
                                $first_char = __('Search','moview');
                                echo '<h2>'.$first_char.'</h2>';
                            }else{
                                echo '<h2>'.get_the_title().'</h2>';
                            }
                        }
                        ?>
                        <?php moview_breadcrumbs(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div id="content" class="site-content" role="main">

            <?php while ( have_posts() ): the_post(); ?>

            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
                <div class="row">
                    <div class="entry-thumbnail col-md-12">
                        <?php the_post_thumbnail(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                    the_content();
                    
                    wp_link_pages( array(
                        'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'moview' ) . '</span>',
                        'after'       => '</div>',
                        'link_before' => '<span>',
                        'link_after'  => '</span>',
                    ) ); ?>
            </div>
            <?php
                // If comments are open or we have at least one comment, load up the comment template.
                if ( comments_open() || get_comments_number() ) :
                comments_template();
                endif;
            ?>
        </div>
        <?php endwhile; ?>
        </div> <!--/#content-->
    </div>
</section> <!--/#main-->
<?php get_footer();
