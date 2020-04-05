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
					<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
                    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        
                        <h2><?php the_title(); ?></h2>
                        
                        <?php if( get_theme_mod( 'enable_article_info',1) ){?>
                        <div class="author-share">
                            <div class="author">
                            	<?php echo get_avatar( the_author_meta('user_nicename'), 48,'','',false ); ?>                                
                                <p><time><?php the_date();?></time></p>
                            </div>
                        </div>
                        <?php }?>
                        
                        <div class="blog-article-content">
                            <?php the_content(); ?>
   
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
                    

                    
                    <?php endwhile;endif; ?> 
					<?php if(has_tag()){?>
                        <div id="article-tag">
                            <?php the_tags('<strong>'.__( 'Tags:','theta' ).'</strong> ', ''._x( ' , ', 'tags separator', 'theta' ).'', ''); ?>
                        </div> 
                    <?php }?> 
                    
                    
                                    
                
					<?php
                        if(comments_open()){comments_template();}
                    ?>
              	</div>
              
              
            	<?php if( get_theme_mod( 'siderbar_layout','right-sidebar') != 'no-sidebar'){get_sidebar();}?>
                
                
        	</section>      
        

		</div>
    </div>
 
<?php get_footer(); ?>