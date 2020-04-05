<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if (!is_single() ) { ?>
		<div class="entry-content-list-blog">
	        <div class="featured-wrap">
	        <?php if(function_exists('rwmb_meta')){ ?>
		        <?php  if ( get_post_meta( get_the_ID(), 'themeum_audio_code',true ) ) { ?>
			        <div class="entry-audio embed-responsive embed-responsive-16by9">
			            <?php echo get_post_meta( get_the_ID(), 'themeum_audio_code',true ); ?>
			        </div> <!--/.audio-content -->
			    <?php } ?>
		    <?php } ?>
	        </div>
	        <?php get_template_part( 'post-format/entry-content' );?>
	    </div>
	<?php } ?>	        
    <?php if ( is_single() ) { ?>
    <div class="entry-content-list video-postformat">
	    <?php if(function_exists('rwmb_meta')){ ?>
	        <?php  if ( get_post_meta( get_the_ID(), 'themeum_audio_code',true ) ) { ?>
		        <div class="entry-audio embed-responsive embed-responsive-16by9">
		            <?php echo get_post_meta( get_the_ID(), 'themeum_audio_code',true ); ?>
		        </div> <!--/.audio-content -->
		    <?php } ?>
	    <?php } ?>
		<div class="container">
		    <?php get_template_part( 'post-format/entry-content' ); ?>
		</div>
	</div>
	<?php } ?>			

    <div class="container">
		<div class="row">
			<div class="col-sm-12">	
				<?php get_template_part( 'post-format/entry-content-single' ); ?> 
			</div>
		</div>
	</div>
</article> <!--/#post -->
