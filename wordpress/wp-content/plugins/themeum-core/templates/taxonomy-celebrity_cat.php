<?php
/**
 * Display Celebrity Category
 *
 * @author 		Themeum
 * @category 	Template
 * @package 	Moview
 *-------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

get_header();
?>
<section id="main" class="clearfix">

   <?php get_template_part('lib/sub-header')?>

  <div id="page" class="container">
    <div class="row">
      <div id="content" class="courses col-md-12" role="main">
        <div class="row">
          <div class="moview-common-layout moview-celebrities-filters">
          <?php
              $count_post = $themeum_options['celebrity-cat-num'];
              if (!is_numeric($count_post)) {
                  $count_post = 6;
              }

              global $wp_query;
              $x = 1;

              while ( have_posts() ) :  the_post();
                  $movie_type         = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
                  $movie_trailer_info = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
                  $release_year       = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_release_year',true));
                  if( $x == 1 ){
                    echo '<div class="row margin-bottom">'; 
                  }
                  ?>
                  <div class="item col-sm-2 col-md-3">
                      <div class="movie-poster">
                          <?php echo get_the_post_thumbnail(get_the_ID(),'moview-profile', array('class' => 'img-responsive')); ?>
                          <a href="<?php echo get_permalink(); ?>" class="play-icon"><i class="themeum-moviewenter"></i></a>
                      </div>
                      <div class="movie-details">
                          <div class="movie-name">
                              <h4 class="movie-title"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h4>
                              <?php if ($movie_type) { ?>
                                  <span class="tag"><?php echo esc_attr($movie_type); ?></span>
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
          ?>
        </div>
      </div><!--/#content-->
      </div>
    </div>
  </div>
</section> 
<?php
get_footer();