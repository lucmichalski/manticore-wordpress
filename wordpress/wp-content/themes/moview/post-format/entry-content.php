<div class="entry-header has-post-format">
    <div class="media">
        <a href="<?php the_permalink(); ?>">
        <time class="entry-date-wrapper pull-left" datetime="<?php the_time( 'c' ); ?>">
            <span class="entry-date-day"><?php the_time('j'); ?></span>
            <span class="entry-date-month-year"><?php the_time('M Y'); ?></span>
        </time>
        </a>  
        <div class="entry-blog-meta media-body">
            <h2 class="entry-title blog-entry-title">
                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                <?php if ( is_sticky() && is_home() && ! is_paged() ) { ?>
                <sup class="featured-post"><i class="fa fa-star-o"></i><?php esc_html_e( 'Sticky', 'moview' ) ?></sup>
                <?php } ?>
            </h2> <!-- //.entry-title --> 

            <ul class="article-info">
                <li>
                    <span class="info-block-title"><?php esc_html_e('By','moview');?> </span>
                    <?php if ( get_the_author_meta('first_name') != "" || get_the_author_meta('last_name') != "" ) { ?>
                        <span class="author"><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php echo get_the_author_meta('first_name');?> <?php echo get_the_author_meta('last_name');?></a></span>
                    <?php } else { ?>
                        <span class="author"> <?php the_author_posts_link() ?></span>
                    <?php }?>
                </li>
                <li class="category-name">
                    <span class="info-block-title"><?php esc_html_e('Category: ','moview');?></span>      
                    <?php echo get_the_category_list(', '); ?>
                </li>  
                <li class="comment">
                    <?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
                        <i class="fa fa-comments-o"></i>
                        <?php comments_popup_link( '<span class="leave-reply">' . esc_html__( 'No comment', 'moview' ) . '</span>', esc_html__( 'One comment', 'moview' ), esc_html__( '% comments', 'moview' ) ); ?>
                    <?php endif; //.comment-link ?>
                </li>
                <li class="hits">
                    <span class="info-block-hits"><?php esc_html_e('Hits: ','moview');?></span>  
                    <?php
                        $visitor_count = get_post_meta( $post->ID, '_post_views_count', true);
                        if( $visitor_count == '' ){ $visitor_count = 0; }
                        if( $visitor_count >= 1000 ){
                            $visitor_count = round( ($visitor_count/1000), 2 );
                            $visitor_count = $visitor_count.'k';
                        }
                    ?>
                    <span class="number"><?php echo esc_attr( $visitor_count ); ?></span>
                </li>    
            </ul>
        </div> <!--/.entry-meta -->
    </div> <!--/.media -->
</div> <!--/.has-post-format -->






