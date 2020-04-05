<?php get_header(); ?>

<section id="main">
    <?php get_template_part('lib/sub-header')?>
    <div class="container">
        <div class="row">
            <div id="content" class="site-content col-sm-12 moviews-tag" role="main">
                <?php
                    $x = 1;
                    if ( have_posts() ) :
                        while ( have_posts() ) : the_post();
                              $movie_type         = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                              $movie_trailer_info = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                              $release_year       = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_release_year',true));
                              if( $x == 1 ){
                                echo '<div class="row margin-bottom">'; 
                              }
                              ?>
                              <div class="item col-sm-2 col-md-3">
                                  <div class="movie-poster">
                                    <?php echo get_the_post_thumbnail(get_the_ID(),'moview-profile', array('class' => 'img-responsive')); 
                                    if( is_array(($movie_trailer_info)) ) {
                                        if(!empty($movie_trailer_info)) {
                                            foreach( $movie_trailer_info as $key=>$value ){
                                                if ($key==0) {
                                                    if ($value["themeum_video_link"]) {
                                                      ?>
                                                        <a class="play-icon play-video" href="<?php echo $value["themeum_video_link"]; ?>" data-type="<?php echo esc_attr( $value["themeum_video_source"] ); ?>">
                                                        <i class="themeum-moviewplay"></i>
                                                        </a>
                                                        <div class="content-wrap">
                                                            <div class="video-container">
                                                                <span class="video-close">x</span>
                                                            </div>
                                                        </div>
                                                    <?php } else { ?>
                                                        <a class="play-icon" href="<?php echo get_permalink(); ?>">
                                                        <i class="themeum-moviewenter"></i>
                                                        </a>
                                                    <?php
                                                    }
                                                }
                                            }
                                        }
                                    } ?>
                                  </div>
                                 <div class="movie-details">
                                      <div class="movie-rating-wrapper">
                                      <?php if (function_exists('themeum_wp_rating')) { ?>
                                          <div class="movie-rating">
                                              <span class="themeum-moviewstar active"></span>
                                          </div>
                                          <span class="rating-summary"><span><?php echo themeum_wp_rating( get_the_ID(),'single' ); ?></span>/<?php _e( '10','themeum-core' ); ?></span>
                                      <?php } ?>
                                      </div>
                                      <?php
                                        $year ='';
                                        if ( $release_year ){
                                            $year =  '('.esc_attr($release_year).')'; 
                                        }
                                      ?>
                                      <div class="movie-name">
                                          <h4 class="movie-title"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title().$year; ?></a></h4>
                                          <?php if ($movie_type) { ?>
                                              <span class="tag"><?php esc_attr($movie_type); ?></span>
                                          <?php } ?>
                                      </div>
                                  </div>
                              </div><!-- .item col-sm-2 col-md-4 -->
                              <?php 
                              if( $x == 4 ){
                                  echo '</div>'; //row  
                                  $x = 1; 
                              }else{
                                  $x++; 
                              }
                        endwhile;
                              if($x !=  1 ){
                                  echo '</div>'; //row  
                              }

                              echo '<div class="themeum-pagination">';
                                $big = 999999999; // need an unlikely integer
                                echo paginate_links( array(
                                  'type'               => 'list',
                                  'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) )),
                                  'format' => '?paged=%#%',
                                  'current' => max( 1, get_query_var('paged') ),
                                  'total' => $wp_query->max_num_pages
                                ) );
                              echo '</div>'; //pagination-in

                              wp_reset_postdata();
                    else:
                        get_template_part( 'post-format/content', 'none' );
                    endif;
                ?>
            </div> <!-- #content -->
        </div> <!-- .row -->
    </div>
</section> <!-- .container -->

<?php get_footer();