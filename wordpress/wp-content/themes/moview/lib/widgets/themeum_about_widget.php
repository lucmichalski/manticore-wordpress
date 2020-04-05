<?php

add_action('widgets_init','register_themeum_about_widget');

function register_themeum_about_widget()
{
	register_widget('Themeum_About_Widget');
}

class Themeum_About_Widget extends WP_Widget{

	public function __construct()
	{
		parent::__construct( 'Themeum_About_Widget',esc_html__("Themeum About Us Widgets",'moview'),array('description' => esc_html__("This About Us Widgets",'moview')));
	}


	/*-------------------------------------------------------
	 *				Front-end display of widget
	 *-------------------------------------------------------*/

 	public function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		if($instance['about_img1']) {
			echo '<img src="'. esc_url(get_site_url()) . $instance['about_img1'].'" class="img-responsive" alt="'.esc_html__('image','moview').'">';
		}

		if( isset($instance['about_text']) && $instance['about_text'] ) 
		{
			echo '<div class="about-desc">'.$instance['about_text'].'</div>';
		}
		?>	
			<ul class="themeum-about-info">
				<?php if( isset($instance['about_email']) && $instance['about_email'] ) { ?>
					<li><span><?php esc_html_e('Email: ', 'moview'); ?></span><?php echo $instance['about_email']; ?></li>
				<?php } ?>				

				<?php if( isset($instance['fax_no']) && $instance['fax_no'] ) { ?>
					<li><span><?php esc_html_e('Fax: ', 'moview'); ?></span><?php echo $instance['fax_no']; ?></li>
				<?php } ?>				

				<?php if( isset($instance['phone_no']) && $instance['phone_no'] ) { ?>
					<li><span><?php esc_html_e('Phone: ', 'moview'); ?></span><?php echo $instance['phone_no']; ?></li>
				<?php } ?>				
		
			</ul>

		<?php

		echo $after_widget;
	}


	/*-------------------------------------------------------
	 *				Sanitize data, save and retrive
	 *-------------------------------------------------------*/

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] 				= strip_tags( $new_instance['title'] );
		$instance['about_img1'] 		= $new_instance['about_img1'];
		$instance['about_text'] 		= $new_instance['about_text'];
		$instance['about_email'] 		= $new_instance['about_email'];
		$instance['fax_no'] 			= $new_instance['fax_no'];
		$instance['phone_no'] 			= $new_instance['phone_no'];

		return $instance;
	}


	/*-------------------------------------------------------
	 *				Back-End display of widget
	 *-------------------------------------------------------*/
	
	public function form( $instance )
	{

		$defaults = array(  
				'title' 			=> '',
				'about_img1' 		=> '',
				'about_text' 		=> '',
				'about_email' 		=> '',
				'fax_no' 			=> '',
				'phone_no' 			=> '',
			);

		$instance = wp_parse_args( (array) $instance, $defaults );
	   ?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php _e('Title :', 'moview'); ?></label>
			<input id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr($instance['title']); ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'about_img1' )); ?>"><?php esc_html_e( 'About Image URL', 'moview' ); ?></label>

			<input type="hidden" id="<?php echo $this->get_field_id('about_img1');?>" name="<?php echo $this->get_field_name('about_img1');?>" class="<?php echo $this->get_field_id('about_img1');?>" value="<?php echo $instance['about_img1']; ?>"/>
 			<button id="<?php echo $this->get_field_id('about_img1');?>" class="custom-upload button" data-url="<?php echo esc_url(get_site_url()); ?>"><?php echo esc_html__('Upload image','moview'); ?></button>
 			<img class="<?php echo $this->get_field_id('about_img1');?>" src="<?php echo esc_url(get_site_url()) . $instance['about_img1']; ?> "/>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'about_text' )); ?>"><?php esc_html_e('About Text :', 'moview'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('about_text'));?>" name="<?php echo esc_attr($this->get_field_name('about_text')); ?>" style="height:150px;"><?php echo esc_attr($instance['about_text']); ?></textarea> 
		</p>


		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'about_email' )); ?>"><?php esc_html_e('Email ID: ', 'moview'); ?></label>
			<input id="<?php echo esc_attr($this->get_field_id( 'about_email' )); ?>" name="<?php echo $this->get_field_name( 'about_email' ); ?>" value="<?php echo esc_attr($instance['about_email']); ?>" style="width:100%;" />
		</p>		

		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'fax_no' )); ?>"><?php esc_html_e('Fax No: ', 'moview'); ?></label>
			<input id="<?php echo esc_attr($this->get_field_id( 'fax_no' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'fax_no' )); ?>" value="<?php echo esc_attr($instance['fax_no']); ?>" style="width:100%;" />
		</p>		

		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'phone_no' )); ?>"><?php esc_html_e('Phone No: ', 'moview'); ?></label>
			<input id="<?php echo esc_attr($this->get_field_id( 'phone_no' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'phone_no' )); ?>" value="<?php echo esc_attr($instance['phone_no']); ?>" style="width:100%;" />
		</p>			

	<?php
	}
}