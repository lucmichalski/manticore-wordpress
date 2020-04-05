<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	if ( has_post_thumbnail() ){ 
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'moview-large' );
			$img ='style="background-image: url('.esc_url($image[0]).');background-position: 50% 50%;background-repeat: no-repeat;height: 700px;background-size: cover;"'; 
		?>
		<?php if (!is_single() ) { ?>
		<div class="entry-content-list-blog">
			<div class="featured-wrap">
		        <a href="<?php the_permalink(); ?>" rel="bookmark">
		            <?php the_post_thumbnail('moview-large', array('class' => 'img-responsive')); ?>
		        </a>
	        </div>
	        <?php get_template_part( 'post-format/entry-content' );?>
        </div>
		<?php }?>
		
        <?php if ( is_single() ) { ?>
        <div class="entry-content-list" <?php echo $img; ?>>
        	<div class="row-fluid entry-header-title-wrap">	
				<div class="container">
				    <?php get_template_part( 'post-format/entry-content' ); ?>
				</div>
			</div>
		</div>
		<?php }?>	
        
	<?php } else { ?>
		<div class="entry-content-list-normal">
	        <?php if ( is_single() ) { ?>
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
				        <?php get_template_part( 'post-format/entry-content' ); ?>
						</div>
					</div>
				</div>
			<?php } else {
				 get_template_part( 'post-format/entry-content' );
			}?>	
		</div>
	<?php } ?>

	<div class="container">
		<div class="row">
			<div class="col-sm-12">	
				<?php get_template_part( 'post-format/entry-content-single' ); ?> 
			</div>
		</div>
	</div>

</article> <!--/#post-->
