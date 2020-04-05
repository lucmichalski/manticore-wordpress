<?php 
//post page

get_header(); ?> 
 
    
   <div class="warp">
        <div class="main-content">
            <?php 
				if(get_theme_mod( 'enable_breadcrumb_check',1 ) ){  
			?>
        	<div class="container">
			<?php theta_breadcrumb_trail(); ?> 
            </div>
			<?php }?> 
  			<section class="theta-article-list container active">
            
  				<ul class="items list-inline">
                <?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
                
    				<li>
                    	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                        	<div class="shade"><u><i class="icon-pin"></i></u></div>
                            
						<?php 
							if(has_post_thumbnail())
							{
								the_post_thumbnail('theta-thumb');
                            }else{
								 
								$exclude_id = get_the_ID();
								$arr = theta_get_thumbnail($exclude_id);
								if($arr['fullpath'] !=''){
								 
						?>
                        
                        			<img src="<?php echo esc_url($arr['fullpath']);?>" class="img-responsive1 img-responsive" />  
                        <?php 
								}
							}
						?>                              

                            <div class="theta-article-info">
                            	<h4><?php the_title(); ?></h4>
                                <?php the_excerpt(); ?>
                           	</div>
                            <div class="bg"><u></u></div>
                     	</a>
                  	</li>

				<?php endwhile;endif; ?>
  				</ul>
                
                 <?php 
					the_posts_pagination( array(
						'mid_size' => 2,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'screen_reader_text' => ' ',
						
					) );
				?>       
                
			
        </section>      
        
        
        </div>
        <p class="clear"></p>
    </div>
 
<?php get_footer(); ?>