<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    
	<?php if (!is_single() ) { ?>
	<div class="entry-content-list-blog">
	    <div class="featured-wrap">
	        <div class="entry-link-post-format">
		        <?php if(function_exists('rwmb_meta')){ ?>
		        	<?php  if ( get_post_meta( get_the_ID(), 'themeum_link',true ) ) { ?>
		            	<h4><?php echo esc_url( get_post_meta( get_the_ID(), 'themeum_link',true ) ); ?></h4>
		            <?php } ?>
		        <?php } ?>
	        </div>     
	    </div>
	    <?php get_template_part( 'post-format/entry-content' );?>
	</div>
    <?php }?>
    <?php if ( is_single() ) { ?>
	    <div class="entry-link-post-format">
	    	<div class="row-fluid entry-header-title-wrap">	
				<div class="container"> 
					<?php if(function_exists('rwmb_meta')){ ?>
			        	<?php  if ( get_post_meta( get_the_ID(), 'themeum_link',true ) ) { ?>
			            	<h4><?php echo esc_url( get_post_meta( get_the_ID(), 'themeum_link',true ) ); ?></h4>
			            <?php } ?>
			        <?php } ?>
				    <?php get_template_part( 'post-format/entry-content' ); ?>
				</div>
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