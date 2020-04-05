<?php

add_action('widgets_init','register_themeum_double_ads_widget');

function register_themeum_double_ads_widget()
{
	register_widget('Themeum_double_ads_Widget');
}

class Themeum_double_ads_Widget extends WP_Widget{

	public function __construct()
	{
		parent::__construct( 'Themeum_double_ads_Widget','Double Ads',array('description' => 'This Double Ads Widgets'));
	}


	/*-------------------------------------------------------
	 *				Front-end display of widget
	 *-------------------------------------------------------*/

	public function widget( $args, $instance ) {

		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		?>

		<ul class="double-ads">
			<?php if($instance['ads_img1']) { ?>
			<li>
				<?php echo '<a href="'.esc_url($instance['ads_img_link1']).'" target="_blank"><img src="'. esc_url(get_site_url()) . $instance['ads_img1'].'" class="img-responsive" alt="'.esc_html__('Ads','moview').'"></a>';?>
			</li>
			<?php } ?>			
			<?php if($instance['ads_img2']) { ?>
			<li>
				<?php echo '<a href="'.esc_url($instance['ads_img_link2']).'" target="_blank"><img src="'. esc_url(get_site_url()) . $instance['ads_img2'].'" class="img-responsive" alt="'.esc_html__('Ads','moview').'"></a>';?>
			</li>
			<?php } ?>			
			<?php if($instance['ads_img3']) { ?>
			<li>
				<?php echo '<a href="'.esc_url($instance['ads_img_link3']).'" target="_blank"><img src="'. esc_url(get_site_url()) . $instance['ads_img3'].'" class="img-responsive" alt="'.esc_html__('Ads','moview').'"></a>';?>
			</li>
			<?php } ?>			
			<?php if($instance['ads_img4']) { ?>
			<li>
				<?php echo '<a href="'.esc_url($instance['ads_img_link4']).'" target="_blank"><img src="'. esc_url(get_site_url()) . $instance['ads_img4'].'" class="img-responsive" alt="'.esc_html__('Ads','moview').'"></a>';?>
			</li>
			<?php } ?>
		</ul>

		<?php echo $after_widget;
	}


	/*-------------------------------------------------------
	 *				Sanitize data, save and retrive
	 *-------------------------------------------------------*/

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] 				= strip_tags( $new_instance['title'] );
		$instance['ads_img_link1'] 		= $new_instance['ads_img_link1'];
		$instance['ads_img_link2'] 		= $new_instance['ads_img_link2'];
		$instance['ads_img_link3'] 		= $new_instance['ads_img_link3'];
		$instance['ads_img_link4'] 		= $new_instance['ads_img_link4'];
		$instance['ads_img1'] 			= $new_instance['ads_img1'];
		$instance['ads_img2'] 			= $new_instance['ads_img2'];
		$instance['ads_img3'] 			= $new_instance['ads_img3'];
		$instance['ads_img4'] 			= $new_instance['ads_img4'];
	

		return $instance;
	}


	/*-------------------------------------------------------
	 *				Back-End display of widget
	 *-------------------------------------------------------*/
	
	public function form( $instance )
	{

		$defaults = array(  'title' => '',
			'ads_img_link1' => '#',
			'ads_img_link2' => '#',
			'ads_img_link3' => '#',
			'ads_img_link4' => '#',
			'ads_img1' => '',
			'ads_img2' => '',
			'ads_img3' => '',
			'ads_img4' => ''
			);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title : ', 'moview' ); ?></label>
			<input id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr($instance['title']); ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img_link1' )); ?>"><?php esc_html_e( 'Ads Link 1', 'moview' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_img_link1'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img_link1')); ?>" value="<?php echo esc_attr($instance['ads_img_link1']); ?>">
			

			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img1' )); ?>"><?php esc_html_e( 'Ads Image URL 1', 'moview' ); ?></label>

			<input type="hidden" id="<?php echo esc_attr($this->get_field_id('ads_img1'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img1'));?>" class="<?php echo esc_attr($this->get_field_id('ads_img1'));?>" value="<?php echo esc_attr($instance['ads_img1']); ?>"/>
 			<button id="<?php echo esc_attr($this->get_field_id('ads_img1'));?>" class="custom-upload button" data-url="<?php echo esc_url(get_site_url()); ?>"><?php echo esc_html__('Upload image','moview'); ?></button>
 			<img class="<?php echo esc_attr($this->get_field_id('ads_img1'));?>" src="<?php echo esc_url(get_site_url()) . esc_attr($instance['ads_img1']); ?> "/>
		</p>		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img_link2' )); ?>"><?php esc_html_e( 'Ads Link 2', 'moview' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_img_link2'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img_link2')); ?>" value="<?php echo esc_attr($instance['ads_img_link2']); ?>">
			

			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img2' )); ?>"><?php esc_html_e( 'Ads Image URL 2', 'moview' ); ?></label>

			<input type="hidden" id="<?php echo esc_attr($this->get_field_id('ads_img2'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img2'));?>" class="<?php echo esc_attr($this->get_field_id('ads_img2'));?>" value="<?php echo esc_attr($instance['ads_img2']); ?>"/>
 			<button id="<?php echo esc_attr($this->get_field_id('ads_img2'));?>" class="custom-upload button" data-url="<?php echo esc_url(get_site_url()); ?>"><?php echo esc_html__('Upload image','moview'); ?></button>
 			<img class="<?php echo esc_attr($this->get_field_id('ads_img2'));?>" src="<?php echo esc_url(get_site_url()) . esc_attr($instance['ads_img2']); ?> "/>
		</p>		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img_link3' )); ?>"><?php esc_html_e( 'Ads Link 3', 'moview' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_img_link3'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img_link3')); ?>" value="<?php echo esc_attr($instance['ads_img_link3']); ?>">
			

			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img3' )); ?>"><?php esc_html_e( 'Ads Image URL 3', 'moview' ); ?></label>

			<input type="hidden" id="<?php echo esc_attr($this->get_field_id('ads_img3'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img3'));?>" class="<?php echo esc_attr($this->get_field_id('ads_img3'));?>" value="<?php echo esc_attr($instance['ads_img3']); ?>"/>
 			<button id="<?php echo esc_attr($this->get_field_id('ads_img3'));?>" class="custom-upload button" data-url="<?php echo esc_url(get_site_url()); ?>"><?php echo esc_html__('Upload image','moview'); ?></button>
 			<img class="<?php echo esc_attr($this->get_field_id('ads_img3'));?>" src="<?php echo esc_url(get_site_url()) . esc_attr($instance['ads_img3']); ?> "/>
		</p>		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img_link4' )); ?>"><?php esc_html_e( 'Ads Link 4', 'moview' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_img_link4'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img_link4')); ?>" value="<?php echo esc_attr($instance['ads_img_link4']); ?>">
			

			<label for="<?php echo esc_attr($this->get_field_id( 'ads_img4' )); ?>"><?php esc_html_e( 'Ads Image URL 4', 'moview' ); ?></label>

			<input type="hidden" id="<?php echo esc_attr($this->get_field_id('ads_img4'));?>" name="<?php echo esc_attr($this->get_field_name('ads_img4'));?>" class="<?php echo esc_attr($this->get_field_id('ads_img4'));?>" value="<?php echo esc_attr($instance['ads_img4']); ?>"/>
 			<button id="<?php echo esc_attr($this->get_field_id('ads_img4'));?>" class="custom-upload button" data-url="<?php echo esc_url(get_site_url()); ?>"><?php echo esc_html__('Upload image','moview'); ?></button>
 			<img class="<?php echo esc_attr($this->get_field_id('ads_img4'));?>" src="<?php echo esc_url(get_site_url()) . esc_attr($instance['ads_img4']); ?> "/>
		</p>
	
		<?php
	}
}