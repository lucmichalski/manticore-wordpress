
 
  	<footer id="footer" class="footer">
    
               
    	<div class="footer-bottom container">
      		<p class="copyright"><?php if( get_theme_mod( 'footer_code','')!='' ){echo html_entity_decode(esc_html(get_theme_mod( 'footer_code','')));}?> <?php printf(__('Powered by <a href="%1$s">WordPress</a>. Theta theme by <a href="%2$s">CooThemes.com</a>.','theta'),esc_url('http://wordpress.org/'),esc_url('http://www.coothemes.com/'));?></p>
        </div>
    </footer>
    
    
    <?php if( get_theme_mod( 'enable_return_top',1) ){echo '<div id="gotoTop"><i class="fa fa-angle-up" aria-hidden="true"></i></div>';}?> 

    
    <?php wp_footer(); ?>    
</body>
</html>      
   