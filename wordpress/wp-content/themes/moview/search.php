<?php get_header(); ?>

<?php get_template_part('lib/sub-header')?>

<section id="main" class="container">
    <div class="moview-common-layout">
        
            <?php if(isset($_GET['post_type'])):  ?>
                        
                <?php if(have_posts()):
                $x = 1;    
                while ( have_posts() ) : the_post();
                    if( $x == 1 ){ ?>
                    <div class="row margin-bottom">  
                    <?php }
                    $moview_movie_type = $moview_movie_trailer_info = $moview_release_year = '';
                    if ( function_exists( 'rwmb_meta' ) ){
                        $moview_movie_type      = esc_attr(rwmb_meta('themeum_movie_type'));
                        $moview_movie_trailer_info     = rwmb_meta('themeum_movie_trailer_info');
                        $moview_release_year    = esc_attr(rwmb_meta('themeum_movie_release_year'));
                    }
                    ?>

                    <div class="item col-sm-3">
                        <div class="movie-poster">
                            <?php if ( has_post_thumbnail() && ! post_password_required() ) { ?>
                                <?php the_post_thumbnail('moview-profile', array('class' => 'img-responsive')); ?>
                            <?php } //.entry-thumbnail
                                if( is_array(($moview_movie_trailer_info)) ) {
                                    if(!empty($moview_movie_trailer_info)) {
                                        foreach( $moview_movie_trailer_info as $key=>$value ){
                                            if ($key==0) {
                                                if ($value["themeum_video_link"]) { ?>
                                                    <a class="play-icon play-video" href="<?php echo $value["themeum_video_link"];?>" data-type="<?php echo esc_attr( $value["themeum_video_source"] );?>">
                                                        <i class="themeum-moviewplay"></i>
                                                    </a>
                                                    <div class="content-wrap">
                                                        <div class="video-container">
                                                            <span class="video-close">x</span>
                                                        </div>
                                                    </div> <!--////content-wrap--> 
                                                <?php } else { ?>
                                                    <a class="play-icon" href="<?php echo get_permalink();?>">
                                                    <i class="themeum-moviewenter"></i>
                                                    </a>
                                                <?php }
                                            }
                                        }
                                    }
                                }
                                ?>
                        </div><!--//movie-poster-->
                        <div class="movie-details">
                            <div class="movie-rating-wrapper">
                                <?php if (function_exists('themeum_wp_rating')) { ?>
                                    <div class="movie-rating">
                                        <span class="themeum-moviewstar active"></span>
                                    </div>
                                    <span class="rating-summary"><span><?php echo themeum_wp_rating(get_the_ID(),'single');?></span>/10</span>
                                <?php } ?>
                            </div><!--//movie-rating-wrapper-->
                            <?php
                            $year ='';
                            if ($moview_release_year) { 
                                $year =  '('.esc_attr($moview_release_year).')'; 
                            }?>
                            <div class="movie-name">
                                <h4 class="movie-title"><a href="<?php echo get_permalink();?>"><?php the_title(); echo esc_attr( $year ); ?></a></h4>
                                <?php if ($moview_movie_type) { ?>
                                    <span class="tag"><?php echo esc_attr($moview_movie_type);?></span>
                                <?php }?>
                            </div><!--//movie-name-->
                        </div><!--//movie-details-->                
                    </div><!--//item-->
                    <?php if( $x == (12/3) ){ ?>
                    </div><!--//row --> 
                    <?php $x = 1; 
                    }else{
                        $x++;   
                    }   
                endwhile; 
                if($x !=  1 ){ ?>
                    </div><!--//row --> 
                <?php }

                $page_numb = max( 1, get_query_var('paged') );
                $max_page = $wp_query->max_num_pages;
                echo moview_pagination($page_numb,$max_page);
                ?>
                <?php else: ?>
                <?php echo '<div class="col-sm-12">'.__( 'No Item Found!','moview' ).'</div>'; ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="row" role="main">
                    <div id="content" class="site-content col-md-9" role="main">
                        <?php if ( have_posts() ) : ?>
                                <?php while ( have_posts() ) : the_post(); ?>
                                   <?php get_template_part( 'post-format/content', get_post_format() ); ?>
                                <?php endwhile; ?>
                                <?php 
                                $page_numb = max( 1, get_query_var('paged') );
                                $max_page = $wp_query->max_num_pages;
                                echo moview_pagination($page_numb,$max_page); ?>
                        <?php else: ?>
                                <?php get_template_part( 'post-format/content', 'none' ); ?>
                        <?php endif; ?>
                    </div> <!-- #content -->
                </div> <!-- #content -->
        <?php endif; ?>
    </div><!--//moview-common-layout-->
</section>
<?php get_footer();