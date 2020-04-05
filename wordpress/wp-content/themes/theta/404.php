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
            			
  			<section class="theta-article-content container">

  				<div class="single-content">
                <?php 
					if( get_theme_mod( '404_page_content','') != ''){
					
						echo html_entity_decode(esc_html(get_theme_mod( '404_page_content','')));
					
					}else{
				?>
                
                
                    		<h3><?php esc_html_e('404 Page!','theta');?></h3>
                    		<div class="quote">
                            	<p><?php  esc_html_e('404 not found!','theta')?> <a href="<?php echo esc_url(home_url('/'));?>"><i class="fa fa-home"></i> <?php  esc_html_e('Please, return to homepage!','theta')?></a></p>
							</div>
                            
                <?php }?>
              	</div>
              
              
            	<?php if( get_theme_mod( 'siderbar_layout','right-sidebar') != 'no-sidebar'){get_sidebar();}?>
                            
        	</section>      
        

		</div>
    </div>
 
<?php get_footer(); ?>