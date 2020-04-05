<?php
/**
 * Display Single Movie 
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

<?php
	while ( have_posts() ) : the_post();

    $image_cover = get_post_meta($post->ID,'themeum_movie_image_cover', true);
    $cover = '';
    if ( $image_cover ) {
    	$cover_img   = wp_get_attachment_image_src($image_cover, 'full');
    	$cover = 'style="background-image:url('.esc_url($cover_img[0]).');background-repeat:no-repeat;background-size: cover;background-position: 50% 50%;"';
    } else {
    	$cover = 'style="background-color: #333;"';
    }
    $release_date    = esc_attr(get_post_meta(get_the_ID(),'themeum_release_date',true));

    $release_year    = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_release_year',true));
    $movie_type   	 = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
    $movie_length    = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_length',true));

    $movie_director  = get_post_meta(get_the_ID(),'themeum_movie_director');
    $movie_actor   	 = get_post_meta(get_the_ID(),'themeum_movie_actor');
    $movie_info   	 = get_post_meta(get_the_ID(),'themeum_movie_info',true);


    $facebook_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_facebook_url',true));
    $twitter_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_twitter_url',true));
    $google_plus_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_google_plus_url',true));
    $youtube_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_youtube_url',true));
    $movie_vimeo_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_vimeo_url',true));
    $instagram_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_instagram_url',true));
    $website_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_website_url',true));

    $movie_trailer_info   	= get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true);
    $movie_showtimes_info   = get_post_meta(get_the_ID(),'themeum_showtimes_info',true);

    $theatre_name   	= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_theatre_name',true));
    $theatre_location   = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_theatre_location',true));
    $show_time   		= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_show_time',true));
    $ticket_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_movie_ticket_url',true));
	?>
	<div class="moview-cover" <?php echo $cover;?>>
		<div class="container">
			<div class="row">
			    <div class="col-sm-9 col-sm-offset-3">
			        <div class="moview-info-warpper">
			            <div class="moview-info">
			                <div class="pull-left">
			                	<h1><?php the_title(); if ($release_year) { echo ' ('.$release_year.')'; } ?></h1>

								<?php if ($movie_type) { ?> <span class="tag"><?php echo $movie_type; ?></span> | <?php }?> <?php if ($movie_length) { ?> <span class="movie-duration"><?php echo $movie_length; ?></span> <?php } ?>
			               
			                    <div class="rating-star">
			                        <span><?php esc_html_e('Rating: ', 'themeum-core');?></span>
								    		<?php 
									    		if (function_exists('themeum_wp_rating')) {
									    			echo themeum_wp_rating(get_the_ID(),'with_html');
									    		}
								    		?>
			                        <span class="moviedb-rating-summary"><?php  if (function_exists('themeum_wp_rating')) { echo themeum_wp_rating(get_the_ID(),'single'); } ?>/10</span>
			                    </div> <!--//rating-star -->

								<?php if ( $facebook_url || $twitter_url || $google_plus_url || $youtube_url || $instagram_url || $website_url || $movie_vimeo_url ) { ?>
				                    <div class="moview-social-icon">
				                        <ul>
				                        	<?php if ( $facebook_url ) { ?>
				                        		<li> <a class="facebook" href="<?php echo $facebook_url; ?>"> <i class="fa fa-facebook"></i> </a> </li>
				                        	<?php } ?>
				                        	<?php if ( $twitter_url ) { ?>
				                        		<li> <a class="twitter" href="<?php echo $twitter_url; ?>"> <i class="fa fa-twitter"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $google_plus_url ) { ?>
				                        		<li> <a class="googleplus" href="<?php echo $google_plus_url; ?>">  <i class="fa fa-google-plus"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $youtube_url ) { ?>
				                        		<li> <a class="youtube" href="<?php echo $youtube_url; ?>">  <i class="fa fa-youtube"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $movie_vimeo_url ) { ?>
				                        		<li> <a class="vimeo" href="<?php echo $movie_vimeo_url; ?>">  <i class="fa fa-vimeo"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $instagram_url ) { ?>
				                        		<li> <a class="instagram" href="<?php echo $instagram_url; ?>">  <i class="fa fa-instagram"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $website_url ) { ?>
				                        		<li> <a class="globe" href="<?php echo $website_url; ?>">  <i class="fa fa-globe"></i> </a> </li>
				                        	<?php } ?>
				                        </ul>
				                    </div> <!-- //social-icon -->
								<?php } ?>

			                </div> <!-- //pull-left -->
			                <div class="pull-right count-rating-wrapper">
			                    <div class="count-rating">
			                        <span><?php  if (function_exists('themeum_wp_rating')) { echo themeum_wp_rating(get_the_ID(),'single'); } ?></span>
			                    </div>
			                </div> <!-- //right -->
			            </div> <!--//moview-info-->
			            <div class="clearfix"></div>
			        </div> <!--//moview-info-warpper-->
			    </div> <!--//col-sm-9-->
			</div> <!--//row-->
		</div> <!--//container-->
	</div> <!--//moview-cover-->


	<div class="moview-details-wrap">
		<div class="container" >
			<div class="row">
				<div class="moview-wapper-total">
					<div id="moview-info-sidebar" class="col-sm-3 moview-info-sidebar">
						<div class="img-wrap">
							
              				<?php if ( has_post_thumbnail() && ! post_password_required() ) { ?>
              					<div class="item-img">
								<?php the_post_thumbnail('moview-profile', array('class' => 'img-responsive')); ?>
								</div> <!--/.item-img-->
							<?php } //.entry-thumbnail ?>
							
						 	<div class="details-wrapper">
						 		<h3 class="title"><?php esc_html_e('Movie Info', 'themeum-core');?></h3>
						 		<ul class="list-style-none list-inline">

									<?php if( is_array(($movie_director)) ) { ?>
										<?php if(!empty($movie_director)) { ?>
										    <li class="director">
											    <span><?php esc_html_e('Director:', 'themeum-core');?></span>
				                                <?php $posts_id = array();
				                                  foreach ( $movie_director as $value ) {
				                                    $posts = get_posts(array('post_type' => 'celebrity', 'name' => $value));
				                                    $posts_id[] = $posts[0]->ID;
				                                  }
				                                  $movie_director = get_posts( array( 'post_type' => 'celebrity', 'post__in' => $posts_id, 'posts_per_page'   => 20) );
				                                ?>
				                                <?php foreach ($movie_director as $key=>$post) {
				                                setup_postdata( $post ); ?>
				                                    <a href="<?php echo get_permalink($post->ID);?>"><?php the_title(); ?></a>
				                                <?php } ?>
				                                <?php wp_reset_postdata(); ?>
				                            </li>
			                            <?php } ?>
		                            <?php } ?>									

									<?php if( is_array(($movie_actor)) ) { ?>
										<?php if(!empty($movie_actor)) { ?>
										    <li class="actors">
											    <span><?php esc_html_e('Actors:', 'themeum-core');?></span>
				                                <?php $posts_id = array();
				                                  foreach ( $movie_actor as $value ) {
				                                    $posts = get_posts(array('post_type' => 'celebrity', 'name' => $value, 'order' => 'ASC'));
				                                    $posts_id[] = $posts[0]->ID;
				                                  }
				                                  $movie_actor = get_posts( array( 'post_type' => 'celebrity', 'post__in' => $posts_id, 'posts_per_page'   => 20, 'order' => 'ASC') );
				                                ?>
				                                <?php foreach ($movie_actor as $key=>$post) {
				                                setup_postdata( $post ); ?>
				                                    <a href="<?php echo get_permalink($post->ID);?>">
				                                    	<?php 
					                                    	the_title();
					                                    	$total = count($movie_actor);
					                                    	if ( isset($total) && !empty($total)) {
					                                    		if ( $total !== ($key + 1) ) {
							                                    	echo ", ";
							                                    }else{
							                                    	echo " ";
							                                    }
					                                    	} 
				                                    	?>
				        	 	
				                                    </a>
				                                <?php } ?>
				                                <?php wp_reset_postdata(); ?>
				                            </li>
			                            <?php } ?>
		                            <?php } ?>
									
									<?php if( is_array(($movie_info)) ) { ?>
										<?php if(!empty($movie_info)) { ?>
	
											<li class="common-list">
												<span><?php echo 'Release Date: ';?></span> 
						                    	<?php echo date_i18n("d M, ", strtotime($release_date)); ?> <?php echo date_i18n("Y", strtotime($release_date)) ?>
						                    </li>

											<?php foreach( $movie_info as $value ){ ?>
								                <li class="common-list">
								                    <span><?php echo esc_attr($value["themeum_movie_info_type"]);?></span> 
								                    <?php echo esc_attr($value["themeum_movie_info_description"]);?>
								                </li>
							                <?php } ?>
						                <?php } ?>
					                <?php } ?>
						 		</ul>						
						 	</div> <!--//details-wrapper -->
						</div> <!--//img-wrap-->
					</div> <!--//moview-info-sidebar -->

					<div class="col-sm-9 movie-info-warpper">
						<!-- movie-details -->
						<div class="moview-details">
							<div class="header-title">
								<span><i class="themeum-moviewbook"></i></span> <h3 class="title"><?php esc_html_e('Movie Story', 'themeum-core');?></h3>
							</div>
							<div class="moview-details-text">
								<?php the_content(); ?>							
							</div>
							<div class="social-shares">
								<div class="social-share-title"><?php _e('Share','themeum-core'); ?>:</div>
								<?php echo themeum_social_share(get_the_ID()); ?>
							</div>
						</div> <!-- //movie-details -->

						<?php if( is_array(($movie_trailer_info)) ) { 
							  if(!empty($movie_trailer_info)) {
							  if( !empty( $movie_trailer_info[0]['themeum_video_info_title'] ) ){
							  	?>	
							<div class="trailers-videos"> <!-- start trailers-videos -->
								<div class="header-title">
									<span><i class="themeum-moviewcamera"></i></span>
									<h3 class="title"><?php esc_html_e('Trailers & Videos', 'themeum-core');?></h3>
								</div>
								<div class="row">
									<?php 
									$i = 1;	
									if(is_array( $movie_trailer_info )){
										if(!empty( $movie_trailer_info )){
											foreach( $movie_trailer_info as $value ){
												
												if ( $i == '1' ) { 
													if(isset( $value["themeum_video_trailer_image"] )) {
													$image = $value["themeum_video_trailer_image"];
													$trailer_image   = wp_get_attachment_image_src($image[0], 'moview-medium');	?>
													<div class="trailer-item leading col-sm-12">
														<div class="trailer">
															<div class="trailer-image-wrap">
																<?php if ($trailer_image[0]!='') { ?>
																	<img class="img-responsive" src="<?php echo $trailer_image[0];?>" alt="<?php esc_html_e('trailers', 'themeum-core');?>">
																	<?php } else{ ?>
																		<div class="trailer-no-video"></div>
																<?php }?>
																<a class="play-video" href="<?php echo $value["themeum_video_link"];?>" data-type="<?php echo $value["themeum_video_source"]; ?>">
																	<i class="play-icon themeum-moviewplay"></i>
																</a>
																<div class="content-wrap">
																	<div class="video-container">
																		<span class="video-close">x</span>
																	</div>
																</div>
															</div> <!-- trailer-image-wrap -->
															<div class="trailer-info trailers-info">
																<div class="trailer-info-block">
										              				<?php if ( has_post_thumbnail() && ! post_password_required() ) { ?>
																		<?php the_post_thumbnail('moview-small', array('class' => 'img-responsive thumb-img')); ?>
																	<?php } //.entry-thumbnail ?>															
																	<h4 class="movie-title"><?php echo esc_attr($value["themeum_video_info_title"]);?></h4>
																	<p class="genry"><?php echo $movie_type; ?></p>
																</div>
															</div>
														</div>
													</div> <!--//trailer-item-->

												<?php } } else { 
													if(isset($value["themeum_video_trailer_image"])){
													$image = $value["themeum_video_trailer_image"];
													$trailer_image   = wp_get_attachment_image_src($image[0], 'moview-trailer');
													?>
													<div class="trailer-item subleading col-sm-4">
														<div class="trailer">
															<div class="trailer-image-wrap">
																<?php if ($trailer_image[0]!='') { ?>
																<img class="img-responsive" src="<?php echo $trailer_image[0];?>" alt="<?php esc_html_e('trailers', 'themeum-core');?>">
																<?php } else{ ?>
																<div class="trailer-smail-no-video"></div>
																<?php }?>
																<a class="play-video" href="<?php echo $value["themeum_video_link"];?>" data-type="<?php echo $value["themeum_video_source"]; ?>">
																	<i class="play-icon themeum-moviewplay"></i>
																</a>
															</div> <!-- trailer-image-wrap -->
															<div class="trailer-info sp-trailers-info">
																<div class="trailer-info-block">
																	<h4 class="movie-title"><?php echo esc_attr($value["themeum_video_info_title"]);?></h4>
																</div>
															</div>

														</div>
													</div> <!--//trailer-item-->
												<?php }
												}
												$i++;
											} 
										}
									}
									?>
								</div> <!-- //row -->
							</div> <!-- //trailers-videos -->
						<?php } ?>
						<?php } ?>
					    <?php } ?>	
						
						<?php
                            if ( comments_open() || get_comments_number() ) {
                                if ( isset($themeum_options['blog-single-comment-en']) && $themeum_options['blog-single-comment-en'] ) {
                                   comments_template();
                                }
                            }
                        ?>

						
						
							<?php
							
							if(is_array($movie_showtimes_info)){
								if(!empty( $movie_showtimes_info )){ 
									if($movie_showtimes_info[0]['themeum_movie_theatre_name'] != ""){
							?>

							<!-- Movie Showtime -->
							<div class="movie-showtime">
							<div class="header-title">
								<span><i class="themeum-moviewpopcorn"></i></span> <h3 class="title"><?php esc_html_e('Movie Showtimes', 'themeum-core');?></h3>
							</div>
							<?php
								foreach ( $movie_showtimes_info as $value) {
									if( $value['themeum_movie_theatre_name'] != '' ){
								?>
									<div class="movie-schedule row">
										<div class="col-sm-4 location">
											<p class="location-name"><?php echo $value['themeum_movie_theatre_name']; ?></p>
											<p class="address"><i class="fa fa-map-marker"></i> <?php echo $value['themeum_movie_theatre_location']; ?></p>
										</div>
										<div class="col-sm-8">
											<div class="times pull-left">
												<p class="visible-xs show-time">Show times</p>
												<?php 
													$time = $value['themeum_movie_show_time']; 
													if($time!=""){
														echo '<ul class="list-style-none list-inline">';
														$time = explode(",", $time);
														foreach ($time as $var) {
															echo '<li><span>'.$var.'</span></li>';
														}
														echo '</ul>';
													}
												?>
											</div>
											<div class="ticket-urls pull-right">
												<?php if( $value['themeum_movie_ticket_url'] != '' ){ ?>
												<a href="<?php echo $value['themeum_movie_ticket_url']; ?>" class="btn btn-primary buy-ticket" target="_blank">
													<i class="icon-ticket"></i><?php _e('Buy Ticket','themeum-core'); ?>
												</a>
												<?php } ?>
											</div>
										</div>
									</div> <!-- //movie-schedule -->
									<div class="clearfix"></div>
								<?php
								}
								}
							?>
						 	</div> <!-- //Movie Showtime -->
						 	<?php
						 			}
						 		}
							}
						 	?>

							<!-- Recommend movies -->
							<div class="moview-common-layout recommend-movies">
								<div class="header-title">
									<span><i class="themeum-moviewlike"></i></span> <h3 class="title"><?php _e( 'Recommend movies','themeum-core' ); ?></h3>
								</div>
								<div class="row">
									<?php
									

									$terms = get_the_terms( $post->ID , 'movie_cat', 'themeum-core');
                					$term_ids = wp_list_pluck($terms,'term_id');


                					$arr = new WP_Query( 
    									array(
						                    'post_type' => 'movie',
						                    'tax_query' => array(
						                                array(
						                                    'taxonomy'  => 'movie_cat',
						                                    'field'     => 'id',
						                                    'terms'     => $term_ids,
						                                    'operator'  => 'IN'
						                                 )),
						                    'posts_per_page'            => 3,
						                    'ignore_sticky_posts'       => 1,
						                    'post__not_in'              =>array($post->ID)
						                )
						            );

									if($arr->have_posts()) { 
									while ($arr->have_posts() ) : $arr->the_post();

									$movie_trailer_info2 = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true); ?>

										<div class="item col-sm-4 col-xs-12">
											<div class="movie-poster">
												<?php the_post_thumbnail('moview-profile', array('class' => 'img-responsive')); ?>
					                            <?php if( is_array(($movie_trailer_info2)) ) {
			                                    if(!empty($movie_trailer_info2)) {
			                                        foreach( $movie_trailer_info2 as $key=>$value ){
			                                            if ($key==0) { 
			                                                if ( isset($value['themeum_video_link']) && !empty($value['themeum_video_link'])  ) { ?>
			                                                    <a class="play-icon play-video" href="<?php echo $value["themeum_video_link"];?>" data-type="<?php echo $value["themeum_video_source"];?>">
			                                                    <i class="themeum-moviewplay"></i>
			                                                    </a>
			                                                    <div class="content-wrap">
			                                                        <div class="video-container">
			                                                            <span class="video-close">x</span>
			                                                        </div>
			                                                    </div>
			                                                <?php }  else { ?>
			                                                    <a class="play-icon" href="<?php echo get_permalink();?>">
			                                                    <i class="themeum-moviewenter"></i>
			                                                    </a>
			                                                <?php }
			                                            }
			                                        }
			                                    }
					                            }
					                        	?> 
											</div>
											<div class="movie-details">
												<div class="movie-rating-wrapper">
					                            <?php if (function_exists('themeum_wp_rating')) { ?>
					                                <div class="movie-rating">
					                                    <span class="themeum-moviewstar active"></span>
					                                </div>
					                                <span class="rating-summary"><span><?php echo themeum_wp_rating(get_the_ID(),'single');?></span>/10</span>
					                            <?php }?>
												</div>
												<div class="movie-name">
													<h4 class="movie-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
												</div>
											</div>
										</div> 

									<?php 
									endwhile;
									wp_reset_query();
								}
									?>
								</div> <!-- //row -->
							</div> <!-- //Recommend movies -->
						</div> <!-- //col-sm-9 -->
					
				</div><!--/#post-->
			</div><!--/.row-->
		</div><!--/.container-->
	</div><!--/.moview-details-wrap-->
<?php endwhile; ?>
</section>

<?php get_footer();