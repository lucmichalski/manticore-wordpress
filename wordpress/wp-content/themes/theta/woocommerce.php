<?php 
//post page

get_header(); ?> 
 
    
   <div class="warp">
        <div class="main-content">

		 
  			<section class="theta-article-content container">

  				<div class="single-content">
					<?php if(have_posts()) : ?>
                    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        
                        <h2><?php the_title(); ?></h2>
                        

                        
                        <div class="blog-article-content">
                            <?php woocommerce_content(); ?>
   
						<?php
                        
                        
                            $defaults = array(
                                'before'           => '<p class="post-paged">' . __( 'Pages:','theta' ),
                                'after'            => '</p>',
                                'link_before'      => '',
                                'link_after'       => '',
                                'next_or_number'   => 'number',
                                'separator'        => ' ',
                                'nextpagelink'     => __( 'Next page','theta' ),
                                'previouspagelink' => __( 'Previous page','theta' ),
                                'pagelink'         => '%',
                                'echo'             => 1
                            );
                         
                            wp_link_pages( $defaults );
                    
                        ?>
                        </div>
                    </div>
                    

                    
                    <?php endif; ?> 
					<?php if(has_tag()){?>
                        <div id="article-tag">
                            <?php the_tags('<strong>'.__( 'Tags:','theta' ).'</strong> ', ''._x( ' , ', 'tags separator', 'theta' ).'', ''); ?>
                        </div> 
                    <?php }?> 
                    
                    
                                    
                
					<?php
                        if(comments_open()){comments_template();}
                    ?>
              	</div>
              
              
            	<?php if(  get_theme_mod( 'enable_woocommerce_siderbar',1) ){get_template_part('woocommerce/sidebar-woocommerce');}?> 
                
                
        	</section>      
        

		</div>
    </div>
 
<?php get_footer(); ?>