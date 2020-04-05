<?php
/**
* Template Name: Page Reviews
*/
get_header(); ?>

<section id="main">

        <?php get_template_part('lib/sub-header'); ?>

        <div class="container">
            <div class="row">
                
                <div id="content" class="site-content reviews-page col-md-12" role="main">
                    <?php
                        if ( is_user_logged_in() ) {

                        global $wpdb;
                        $number_of_post = 20;
                        $userID = get_current_user_id();
                        $data = $wpdb->get_col( $wpdb->prepare( "SELECT `comment_post_ID` FROM `".$wpdb->prefix."comments` WHERE `comment_ID` IN (SELECT `comment_id` FROM `".$wpdb->prefix."commentmeta` where `meta_key`='rating') AND `user_id`=%s", $userID ) );
                        

                        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                        $args = array( 'post_type'=>'movie','post__in' => $data, 'posts_per_page'=> $number_of_post, 'paged' => $paged );
                        $query = new WP_Query( $args );
                        $i = 1;


                        if ( $query->have_posts() ) :
                            
                                while ( $query->have_posts() ) :  $query->the_post();
                                    
                                    $comments = get_comments( 
                                                        array( 
                                                            'post_id' => get_the_ID(), 
                                                            'author__in' => array( get_current_user_id() ),
                                                            'meta_query' => array(
                                                                                array(
                                                                                    'key' => 'rating',
                                                                                    'value' => '',
                                                                                    'compare' => '!='
                                                                            )
                                                                        ),
                                                            'status' => 'approve'
                                                            ) 
                                                        );
                                
                                    if(is_array( $comments )){
                                        if(!empty( $comments )){

                                            echo '<div class="review-box">';
                                            echo '<h4 class="movie-title">';
                                                echo '<a href="'.get_permalink().'">'.get_the_title().'</a>';
                                            echo '</h4>';
                                            echo '<div class="moviedb-rating">';
                                            $comments_content = $rate = $comments_ids = '';
                                            if(function_exists('themeum_wp_rating')){
                                                foreach ( $comments as $value) {
                                                    $comments_ids = $value->comment_ID;
                                                    $rate = get_comment_meta( $comments_ids , 'rating', true );
                                                    if( $rate != '' ){
                                                        $comments_content = themeum_wp_rating($rate,'data_only');
                                                    }
                                                }
                                                echo $comments_content;
                                            }
                                            echo '</div>';

                                            echo '<span class="moviedb-rating-summary"><span>';
                                            if(function_exists('themeum_wp_rating')){ echo $rate; }
                                            echo '</span>/'.__("10","moview").'</span>';
                                                echo '<div class="reviewers-review">';
                                                    echo '<div class="date-time">';
                                                        if( is_numeric($comments_ids) ){
                                                            echo '<i class="themeum-moviewclock"></i> <span>'.date_i18n( 'd/m/Y', get_comment_date( 'U' , $comments_ids ) ).'</span>';
                                                        }
                                                    echo '</div>';
                                                    echo '<div class="clearfix"></div>';
                                                    echo '<div class="review-message">';
                                                        if( is_numeric($comments_ids) ){
                                                            echo '<p>'.get_comment_text( $comments_ids ).'</p>';
                                                        }
                                                    echo '</div>';
                                                echo '</div>';
                                            echo '</div>';
                                        }
                                    }

                                endwhile;

                                $page_numb = max( 1, get_query_var('paged') );
                                $max_page = $query->max_num_pages;
                                echo moview_pagination( $page_numb, $max_page );
                            
                            wp_reset_postdata();
                        else :
                            echo '<h1>'.esc_html__('No Reviews Found!','moview').'</h1>';
                        endif;
                       } 
                    ?>
                </div> <!--/#content-->

            </div>
        </div>
    </section> <!--/#main-->
<?php get_footer();