<?php
/**
 * Display Single Celebrity 
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
    $image_cover = get_post_meta($post->ID,'themeum_celebrity_images', true);
    $cover = '';
    if ( $image_cover ) {
    	$cover_img   = wp_get_attachment_image_src($image_cover, 'full');
    	$cover = 'style="background-image:url('.esc_url($cover_img[0]).');background-repeat:no-repeat;background-size: cover;background-position: 50% 50%;"';
    } else {
    	$cover = 'style="background-color: #333;"';
    }
    $movie_type   	 = esc_attr(get_post_meta(get_the_ID(),'themeum_movie_type',true));
    $movie_info   	 = get_post_meta(get_the_ID(),'celebrity_info',true);

    $facebook_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_facebook_url',true));
    $twitter_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_twitter_url',true));
    $google_plus_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_google_plus_url',true));
    $youtube_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_youtube_url',true));
    $movie_vimeo_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_vimeo_url',true));
    $instagram_url   	= esc_attr(get_post_meta(get_the_ID(),'themeum_instagram_url',true));
    $website_url   		= esc_attr(get_post_meta(get_the_ID(),'themeum_website_url',true));
    $movie_trailer_info = array();
	?>
	<div class="moview-cover" <?php echo $cover;?>>
		<div class="container">
			<div class="row">
			    <div class="col-sm-9 col-sm-offset-3">
			        <div class="moview-info-warpper">
			            <div class="moview-info">
		                	<h1><?php the_title(); ?></h1>

							<?php if ($movie_type) { ?> <span class="tag"><?php echo esc_attr($movie_type); ?></span><?php } ?>

							<div class="social-shares">
								<div class="social-share-title"><?php _e('Share','themeum-core'); ?>:</div>
								<?php echo themeum_social_share(get_the_ID()); ?>
							</div>
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
						 		
								<?php if( is_array(($movie_info)) ) { ?>
									<?php if(!empty($movie_info)) { ?>
										<?php if($movie_info[0]['themeum_info_type'] != '') { ?>
											<h3 class="title"><?php esc_html_e('Personal Info', 'themeum-core');?></h3>
											<ul class="list-style-none list-inline">
												<?php foreach( $movie_info as $value ){ ?>
									                <li class="common-list">
									                    <span> <?php echo esc_attr($value["themeum_info_type"]);?> </span> <?php echo esc_attr($value["themeum_info_description"]);?>
									                </li>
								                <?php } ?>
								            </ul>    
					                	<?php } ?>
					                <?php } ?>
				                <?php } ?>
						 		
						 		<?php if ( $facebook_url || $twitter_url || $google_plus_url || $youtube_url || $instagram_url || $website_url || $movie_vimeo_url ) { ?>
				                    <div class="moview-social-icon">
				                        <span>Social: </span>
				                        <ul>
				                        	<?php if ( $facebook_url ) { ?>
				                        		<li> <a class="facebook" href="<?php echo esc_url($facebook_url); ?>"> <i class="fa fa-facebook"></i> </a> </li>
				                        	<?php } ?>
				                        	<?php if ( $twitter_url ) { ?>
				                        		<li> <a class="twitter" href="<?php echo esc_url($twitter_url); ?>"> <i class="fa fa-twitter"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $google_plus_url ) { ?>
				                        		<li> <a class="googleplus" href="<?php echo esc_url($google_plus_url); ?>">  <i class="fa fa-google-plus"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $youtube_url ) { ?>
				                        		<li> <a class="youtube" href="<?php echo esc_url($youtube_url); ?>">  <i class="fa fa-youtube"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $movie_vimeo_url ) { ?>
				                        		<li> <a class="vimeo" href="<?php echo esc_url($movie_vimeo_url); ?>">  <i class="fa fa-vimeo"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $instagram_url ) { ?>
				                        		<li> <a class="instagram" href="<?php echo esc_url($instagram_url); ?>">  <i class="fa fa-instagram"></i> </a> </li>
				                        	<?php } ?>				                        	
				                        	<?php if ( $website_url ) { ?>
				                        		<li> <a class="globe" href="<?php echo esc_url($website_url); ?>">  <i class="fa fa-globe"></i> </a> </li>
				                        	<?php } ?>
				                        </ul>
				                    </div> <!-- //social-icon -->
								<?php } ?>
						 	</div> <!--//details-wrapper -->
						</div> <!--//img-wrap-->
					</div> <!--//moview-info-sidebar -->

					<div class="col-sm-9 movie-info-warpper">
						<!-- movie-details -->
						<div class="moview-details">
							<div class="header-title">
								<span><i class="themeum-moviewbook"></i></span> <h3 class="title"><?php esc_html_e('Biography', 'themeum-core');?></h3>
							</div>
							<div class="moview-details-text">
								<?php the_content(); ?>							
							</div>
						</div> <!-- //movie-details -->

						<?php
						$post_data = get_post( get_the_ID(),ARRAY_A );
						$post_data = $post_data['post_name'];



						if($post_data){
							$args = array(
								'post_type' 	=> 'movie',
								'meta_query' 	=> array(
														array( 
															'key'     => 'themeum_movie_actor',
															'value'   => $post_data,
															'compare' => '=',
														),
													),
								'posts_per_page'=> 10
							);
							$query = new WP_Query( $args );



							// The Loop
							if ( $query->have_posts() ) { ?>
							<!-- celebrity flim -->
							<div class="celebrity-filmography">
								<div class="header-title">
									<span><i class="themeum-moviewfilm"></i></span> <h3 class="title"><?php esc_html_e('Filmography', 'themeum-core');?></h3>
								</div>

								<ul class="list-unstyled movie-film-list">
									<li class="main-title">
								    	<p class="pull-left"><?php _e('Movie Name','themeum-core'); ?></p>
								    	<p class="pull-right"><?php _e('Ratings','themeum-core'); ?></p>
									</li>
								<?php while ( $query->have_posts() ) {
									$query->the_post(); ?>

									<?php
										$info = get_post_meta(get_the_ID(),'themeum_movie_trailer_info',true );
										if( isset($info[0]["themeum_video_info_title"]) ){
											if( $info[0]["themeum_video_info_title"] != '' ){
												$movie_trailer_info[] = $info[0];
											}
										}
									?>

									<!-- //main-title -->
									<li>
								    	<div class="details pull-left">
											<?php if (has_post_thumbnail( get_the_ID() ) ): ?>
											  <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'moview-small' ); ?>
											  <div class="img-warp pull-left" style="background-image: url(<?php echo esc_url($image[0]); ?>); "></div>
											<?php endif; ?>
								         	<div class="filmography-info">
									            <a href="<?php the_permalink(); ?>" class="movie-name">
									            	<strong><?php the_title(); ?></strong>
									            </a>
									            <div class="clelarfix"></div>
									            <p class="celebrity-movie-genres"><?php echo get_post_meta( get_the_ID(), "themeum_movie_type", true ); ?></p>
								        	</div>
								    	</div>
								    	<!-- //details -->
								    	<div class="pull-right movie-rating-wrap">
								    		<?php 
									    		if (function_exists('themeum_wp_rating')) {
									    			echo themeum_wp_rating(get_the_ID(),'with_html');
									    		}
								    		?>
								        	<span class="movie-rating-summary"><span>
								        	<?php 
									    		if (function_exists('themeum_wp_rating')) {
									    			echo themeum_wp_rating(get_the_ID(),'single');
									    		}
								    		?>
								        	</span>/<?php _e('10','themeum-core'); ?></span>										
								    	</div>
								      <!-- /.sp-moviedb-rating-wrap -->
									</li>
								<?php } ?>
								</ul>
							</div> <!-- //celebrity flim -->
							<?php }
							wp_reset_postdata();
						}
						?>

						<!-- Trailers Videos -->
						<?php if( is_array(($movie_trailer_info)) ) {
							  if(!empty($movie_trailer_info)) {
							  $x=1;
							  if($movie_trailer_info[0]['themeum_video_info_title'] != '' ){
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
												if( $x <= 7 ){
													if ( $i == '1' ) { 
														$image = $value["themeum_video_trailer_image"];
														$trailer_image   = wp_get_attachment_image_src($image[0], 'moview-medium');	?>
														<div class="trailer-item leading col-sm-12">
															<div class="trailer">
																<div class="trailer-image-wrap">
																	<?php if ($trailer_image[0]!='') { ?>
																		<img class="img-responsive" src="<?php echo esc_url($trailer_image[0]);?>" alt="<?php esc_html_e('trailers', 'themeum-core');?>">
																		<?php } else{ ?>
																			<div class="trailer-no-video"></div>
																	<?php }?>
																	<a class="play-video" href="<?php echo $value["themeum_video_link"];?>" data-type="<?php echo esc_attr( $value["themeum_video_source"] ); ?>">
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
																		<h4 class="movie-title"><?php echo esc_attr( $value["themeum_video_info_title"] );?></h4>
																		<p class="genry"><?php echo $movie_type; ?></p>
																	</div>
																</div>
															</div>
														</div> <!--//trailer-item-->

													<?php } else { 
														$image = $value["themeum_video_trailer_image"];
														$trailer_image   = wp_get_attachment_image_src($image[0], 'moview-trailer');
														?>
														<div class="trailer-item subleading col-sm-4">
															<div class="trailer">
																<div class="trailer-image-wrap">
																	<?php if ($trailer_image[0]!='') { ?>
																	<img class="img-responsive" src="<?php echo esc_url($trailer_image[0]);?>" alt="<?php esc_html_e('trailers', 'themeum-core');?>">
																	<?php } else{ ?>
																	<div class="trailer-smail-no-video"></div>
																	<?php }?>
																	<a class="play-video" href="<?php echo $value["themeum_video_link"];?>" data-type="<?php echo esc_attr($value["themeum_video_source"]); ?>">
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
													$i++;
													$x++;	
												}
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
	                        $count_post = esc_attr( get_post_meta( $post->ID, '_post_views_count', true) );
	                        if( $count_post == ''){
	                            $count_post = 1;
	                            add_post_meta( $post->ID, '_post_views_count', $count_post);
	                        }else{
	                            $count_post = (int)$count_post + 1;
	                            update_post_meta( $post->ID, '_post_views_count', $count_post);
	                        }
	                	?>
						</div> <!-- //col-sm-9 -->
					</div>
				
			</div><!--/.row-->

		</div><!--/.container-->
	</div><!--/.moview-details-wrap-->
	<?php endwhile; ?>
</section>

<?php get_footer();