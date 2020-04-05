<?php
/**
 * Admin feature for Custom Meta Box
 *
 * @author 		Themeum
 * @category 	Admin Core
 * @package 	Varsity
 *-------------------------------------------------------------*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Registering meta boxes
 * For more information, please visit:
 * @link http://www.deluxeblogtips.com/meta-box/
 */

add_filter( 'rwmb_meta_boxes', 'themeum_movie_register_meta_boxes' );

/**
 * Register meta boxes
 *
 * @return void
 */

function themeum_movie_register_meta_boxes( $meta_boxes )
{

	global $woocommerce;

	/**
	 * Prefix of meta keys (optional)
	 * Use underscore (_) at the beginning to make keys hidden
	 * Alt.: You also can make prefix empty to disable it
	 */

	$actor_list = get_all_posts('celebrity');

	// Better has an underscore as last sign
	$prefix = 'themeum_';

	/**
	 * Register Post Meta for Movie Post Type
	 *
	 * @return array
	 */


	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Post Open ----------------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------
	$meta_boxes[] = array(
		'id' => 'post-meta-quote',

		// Meta box title - Will appear at the drag and drop handle bar. Required.
		'title' => esc_html__( 'Post Quote Settings', 'themeum-core' ),

		// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
		'pages' => array( 'post'),

		// Where the meta box appear: normal (default), advanced, side. Optional.
		'context' => 'normal',

		// Order of meta box: high (default), low. Optional.
		'priority' => 'high',

		// Auto save: true, false (default). Optional.
		'autosave' => true,

		// List of meta fields
		'fields' => array(
			array(
				// Field name - Will be used as label
				'name'  => esc_html__( 'Qoute Text', 'themeum-core' ),
				// Field ID, i.e. the meta key
				'id'    => "{$prefix}qoute",
				'desc'  => esc_html__( 'Write Your Qoute Here', 'themeum-core' ),
				'type'  => 'textarea',
				// Default value (optional)
				'std'   => ''
			),
			array(
				// Field name - Will be used as label
				'name'  => esc_html__( 'Qoute Author', 'themeum-core' ),
				// Field ID, i.e. the meta key
				'id'    => "{$prefix}qoute_author",
				'desc'  => esc_html__( 'Write Qoute Author or Source', 'themeum-core' ),
				'type'  => 'text',
				// Default value (optional)
				'std'   => ''
			)
			
		)
	);



	$meta_boxes[] = array(
		'id' => 'post-meta-link',

		// Meta box title - Will appear at the drag and drop handle bar. Required.
		'title' => esc_html__( 'Post Link Settings', 'themeum-core' ),

		// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
		'pages' => array( 'post'),

		// Where the meta box appear: normal (default), advanced, side. Optional.
		'context' => 'normal',

		// Order of meta box: high (default), low. Optional.
		'priority' => 'high',

		// Auto save: true, false (default). Optional.
		'autosave' => true,

		// List of meta fields
		'fields' => array(
			array(
				// Field name - Will be used as label
				'name'  => esc_html__( 'Link URL', 'themeum-core' ),
				// Field ID, i.e. the meta key
				'id'    => "{$prefix}link",
				'desc'  => esc_html__( 'Write Your Link', 'themeum-core' ),
				'type'  => 'text',
				// Default value (optional)
				'std'   => ''
			)
			
		)
	);


	$meta_boxes[] = array(
		'id' => 'post-meta-audio',

		// Meta box title - Will appear at the drag and drop handle bar. Required.
		'title' => esc_html__( 'Post Audio Settings', 'themeum-core' ),

		// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
		'pages' => array( 'post'),

		// Where the meta box appear: normal (default), advanced, side. Optional.
		'context' => 'normal',

		// Order of meta box: high (default), low. Optional.
		'priority' => 'high',

		// Auto save: true, false (default). Optional.
		'autosave' => true,

		// List of meta fields
		'fields' => array(
			array(
				// Field name - Will be used as label
				'name'  => esc_html__( 'Audio Embed Code', 'themeum-core' ),
				// Field ID, i.e. the meta key
				'id'    => "{$prefix}audio_code",
				'desc'  => esc_html__( 'Write Your Audio Embed Code Here', 'themeum-core' ),
				'type'  => 'textarea',
				// Default value (optional)
				'std'   => ''
			)
			
		)
	);

	$meta_boxes[] = array(
		'id' => 'post-meta-video',

		// Meta box title - Will appear at the drag and drop handle bar. Required.
		'title' => esc_html__( 'Post Video Settings', 'themeum-core' ),

		// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
		'pages' => array( 'post'),

		// Where the meta box appear: normal (default), advanced, side. Optional.
		'context' => 'normal',

		// Order of meta box: high (default), low. Optional.
		'priority' => 'high',

		// Auto save: true, false (default). Optional.
		'autosave' => true,

		// List of meta fields
		'fields' => array(
			array(
				// Field name - Will be used as label
				'name'  => esc_html__( 'Video Embed Code/ID', 'themeum-core' ),
				// Field ID, i.e. the meta key
				'id'    => "{$prefix}video",
				'desc'  => esc_html__( 'Write Your Vedio Embed Code/ID Here', 'themeum-core' ),
				'type'  => 'textarea',
				// Default value (optional)
				'std'   => ''
			),
			array(
				'name'  => __( 'Video Durations', 'themeum-core' ),
				'id'    => "{$prefix}video_durations",
				'type'  => 'text',
				'std'   => ''
			),			
			array(
				'name'     => esc_html__( 'Select Vedio Type/Source', 'themeum-core' ),
				'id'       => "{$prefix}video_source",
				'type'     => 'select',
				// Array of 'value' => 'Label' pairs for select box
				'options'  => array(
					'1' => esc_html__( 'Embed Code', 'themeum-core' ),
					'2' => esc_html__( 'YouTube', 'themeum-core' ),
					'3' => esc_html__( 'Vimeo', 'themeum-core' ),
				),
				// Select multiple values, optional. Default is false.
				'multiple'    => false,
				'std'         => '1'
			),
			
		)
	);


	$meta_boxes[] = array(
		'id' => 'post-meta-gallery',

		// Meta box title - Will appear at the drag and drop handle bar. Required.
		'title' => esc_html__( 'Post Gallery Settings', 'themeum-core' ),

		// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
		'pages' => array( 'post'),

		// Where the meta box appear: normal (default), advanced, side. Optional.
		'context' => 'normal',

		// Order of meta box: high (default), low. Optional.
		'priority' => 'high',

		// Auto save: true, false (default). Optional.
		'autosave' => true,

		// List of meta fields
		'fields' => array(
			array(
				'name'             => esc_html__( 'Gallery Image Upload', 'themeum-core' ),
				'id'               => "{$prefix}gallery_images",
				'type'             => 'image_advanced',
				'max_file_uploads' => 6,
			)			
		)
	);
	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Post Close ---------------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------


	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Page Open ----------------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------
	$meta_boxes[] = array(
		'id' => 'page-meta-settings',

		// Meta box title - Will appear at the drag and drop handle bar. Required.
		'title' => esc_html__( 'Page Settings', 'themeum-core' ),

		// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
		'pages' => array( 'page'),

		// Where the meta box appear: normal (default), advanced, side. Optional.
		'context' => 'normal',

		// Order of meta box: high (default), low. Optional.
		'priority' => 'high',

		// List of meta fields
		'fields' => array(
			array(
				'name'             => esc_html__( 'Upload Sub Title Banner Image', 'themeum-core' ),
				'id'               => "{$prefix}subtitle_images",
				'type'             => 'image_advanced',
				'max_file_uploads' => 1,
			),	

			array(
				'name'             => esc_html__( 'Upload Sub Title BG Color', 'themeum-core' ),
				'id'               => "{$prefix}subtitle_color",
				'type'             => 'color',
				'std' 			   => "#191919"
			),	
		)
	);	
	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Page Close ---------------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------



	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Movie Open ---------------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------
	// Movie Post Settings
	$meta_boxes[] = array(
		'id' 		=> 'movie-post-meta',
		'title' 	=> __( 'Movie Post Settings', 'themeum-core' ),
		'pages' 	=> array( 'movie'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(

				array(
					'name'  		=> esc_html__( 'Featured Movie', 'themeum-core' ),
					'id'    		=> "{$prefix}featured_movie",
					'desc'  		=> esc_html__( 'Featured Movie', 'themeum-core' ),
					'type'  		=> 'checkbox',
					'std'   		=> 0
				),

				array(
					'name'             => esc_html__( 'Movie Cover Image', 'themeum-core' ),
					'id'               => "{$prefix}movie_image_cover",
					'type'             => 'image_advanced',
					'max_file_uploads' => 1,
				),	

				// Movie Length
				array(
					'name'  		=> __( 'Release Year', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_release_year",
					'desc'  		=> __( 'Add Your Movie Release Year Here(Eg: 2016)', 'themeum-core' ),
					'type'  		=> 'text',
					'std'   		=> ''
				),							
			
				// Movie Type
				array(
					'name'  		=> __( 'Movie Type', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_type",
					'desc'  		=> __( 'Add Your Movie Type Here(Eg: Action)', 'themeum-core' ),
					'type'  		=> 'text',
					'std'   		=> ''
				),

				// Movie Length
				array(
					'name'  		=> __( 'Movie Lenght', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_length",
					'desc'  		=> __( 'Add Your Movie Lenght Here(Eg: 120 Min)', 'themeum-core' ),
					'type'  		=> 'text',
					'std'   		=> ''
				),

				// Movie Director
				array(
					'name'  		=> __( 'Movie Director', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_director",
					'desc'  		=> '',
					'type'     		=> 'select_advanced',
					'options'  		=> $actor_list,
					'multiple'    	=> true,
					'placeholder' 	=> __( 'Select Director', 'themeum-core' ),
				),			

				// Movie Actor
				array(
					'name'  		=> __( 'Movie Actor', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_actor",
					'desc'  		=> '',
					'type'     		=> 'select_advanced',
					'options'  		=> $actor_list,
					'multiple'    	=> true,
					'placeholder' 	=> __( 'Select Actor', 'themeum-core' ),
				),


				// Release Date
				array(
					'name'       => __( 'Release Date', 'themeum-core' ),
					'id'         => "{$prefix}release_date",
					'type'       => 'date',
					'js_options' => array(
						'appendText'      => __( '(yyyy-mm-dd)', 'themeum-core' ),
						'dateFormat'      => __( 'yy-mm-dd', 'themeum-core' ),
						'changeMonth'     => true,
						'changeYear'      => true,
						'showButtonPanel' => true,
					),
				),

				array(
					'name'  		=> __( 'Movie Rating (Min: 0 and Max: 10)', 'themeum-core' ),
					'id'    		=> "rating",
					'desc'  		=> '',
					'type'     		=> 'text',
				),

			)
		);


	// Social Profile Settings
	$meta_boxes[] = array(
		'id' 		=> 'social-post-meta',
		'title' 	=> __( 'Social Profile Settings', 'themeum-core' ),
		'pages' 	=> array( 'movie'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(

				array(
					'name'  		=> __( 'Facebook URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_facebook_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),
				array(
					'name'  		=> __( 'Twitter URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_twitter_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),				
				array(
					'name'  		=> __( 'Vimeo URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_vimeo_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),
				array(
					'name'  		=> __( 'Google Plus URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_google_plus_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),
				array(
					'name'  		=> __( 'Youtube URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_youtube_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),
				array(
					'name'  		=> __( 'Instagram URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_instagram_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),
				array(
					'name'  		=> __( 'Website URL', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_website_url",
					'type'  		=> 'url',
					'std'   		=> ''
				),				
			)
		);


	// Movie Info Settings
	$meta_boxes[] = array(
		'id' 		=> 'info-post-meta',
		'title' 	=> __( 'Movie Info Settings', 'themeum-core' ),
		'pages' 	=> array( 'movie'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(

			// Movie Info
			array(
			    'name' => '', // Optional
			    'id' => 'themeum_movie_info',
			    'type'   => 'group',
			    'clone'  => true,
			    'fields' => array(
					array(
						'name'          => __( 'Movie Info Title', 'themeum-core' ),
						'id'            => "{$prefix}movie_info_type",
						'type'          => 'text',
						'std'           => ''
					),
					array(
						'name'          => __( 'Movie Info Description', 'themeum-core' ),
						'id'            => "{$prefix}movie_info_description",
						'type'          => 'textarea',
						'std'           => ''
					),
					
			    ),
			),

			)
		);


	// Video Info Settings
	$meta_boxes[] = array(
		'id' 		=> 'video-post-meta',
		'title' 	=> __( 'Video Trailer Info Settings', 'themeum-core' ),
		'pages' 	=> array( 'movie'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(


				// Video Info
				array(
				    'name' => '', // Optional
				    'id' => 'themeum_movie_trailer_info',
				    'type'   => 'group',
				    'clone'  => true,
				    'fields' => array(
						array(
							'name'          => __( 'Video Info Title', 'themeum-core' ),
							'id'            => "{$prefix}video_info_title",
							'type'          => 'text',
							'std'           => ''
						),
						array(
							'name'     => esc_html__( 'Select Video Type/Source', 'themeum-core' ),
							'id'       => "{$prefix}video_source",
							'type'     => 'select',
							'options'  => array(
											'self' 		=> esc_html__( 'Self Hosted', 'themeum-core' ),
											'youtube' 	=> esc_html__( 'YouTube', 'themeum-core' ),
											'vimeo' 	=> esc_html__( 'Vimeo', 'themeum-core' ),
											'imdb' 	=> esc_html__( 'IMDb', 'themeum-core' ),
										),
							'multiple'    => false,
							'std'         => '1'
						),				
						array(
							'name'  		=> __( 'Video', 'themeum-core' ),
							'id'    		=> "{$prefix}video_link",
							'type'  		=> 'text',
							'std'   		=> '',
							'desc'  		=> __( 'Add Video ID for Youtube and Vimeo / Video URL for Self Hosted Video.', 'themeum-core' ),
						),
						array(
							'name'             => esc_html__( 'Upload Trailer Image', 'themeum-core' ),
							'id'               => "{$prefix}video_trailer_image",
							'type'             => 'image_advanced',
							'max_file_uploads' => 1,
						),
						
				    ),
				),


			));


	// Showtimes Settings
	$meta_boxes[] = array(
		'id' 		=> 'showtimes-post-meta',
		'title' 	=> __( 'Showtimes Settings', 'themeum-core' ),
		'pages' 	=> array( 'movie'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(
				// Video Info
			array(
			    'name' => '', // Optional
			    'id' => 'themeum_showtimes_info',
			    'type'   => 'group',
			    'clone'  => true,
			    'fields' => array(
					array(
						'name'          => __( 'Theatre Name', 'themeum-core' ),
						'id'            => "{$prefix}movie_theatre_name",
						'type'          => 'text',
						'std'           => ''
					),
					array(
						'name'  		=> __( 'Theatre Location', 'themeum-core' ),
						'id'    		=> "{$prefix}movie_theatre_location",
						'type'  		=> 'text',
						'std'   		=> '',
					),
					array(
						'name'  		=> __( 'Show Time', 'themeum-core' ),
						'id'    		=> "{$prefix}movie_show_time",
						'type'  		=> 'text',
						'std'   		=> '',
						'desc'  		=> __( 'Add Movie Show Time Here(Eg: 12:30AM,8:20PM,9:20PM )', 'themeum-core' ),
					),
					array(
						'name'  		=> __( 'Ticket URL', 'themeum-core' ),
						'id'    		=> "{$prefix}movie_ticket_url",
						'type'  		=> 'url',
					),

					
			    ),
			),
			)
		);
	// --------------------------------------------------------------------------------------------------------------	
	// -----------------------------------------  Movie Close -------------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------



	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Celebrity Open -----------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------	
	// Celebrity General Settings
	$meta_boxes[] = array(
		'id' 		=> 'general-post-meta',
		'title' 	=> __( 'Celebrity General Settings', 'themeum-core' ),
		'pages' 	=> array( 'celebrity'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(
		    	// Celebrity Type
				array(
					'name'             => esc_html__( 'Celebrity Cover Image', 'themeum-core' ),
					'id'               => "{$prefix}celebrity_images",
					'type'             => 'image_advanced',
					'max_file_uploads' => 1,
				),	

				array(
					'name'  		=> __( 'Celebrity Type', 'themeum-core' ),
					'id'    		=> "{$prefix}movie_type",
					'desc'  		=> __( 'Add Your Celebrity Type Here(Eg: Actor, producer)', 'themeum-core' ),
					'type'  		=> 'text',
					'std'   		=> ''
				),
			)
		);



	// Celebrity Info Settings
	$meta_boxes[] = array(
		'id' 		=> 'celebrity-info-post-meta',
		'title' 	=> __( 'Celebrity Info Settings', 'themeum-core' ),
		'pages' 	=> array( 'celebrity'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(
				// Celebrity Info
				array(
				    'name' => '', // Optional
				    'id' => 'celebrity_info',
				    'type'   => 'group',
				    'clone'  => true,
				    'fields' => array(
						array(
							'name'          => __( 'Celebrity Info Title', 'themeum-core' ),
							'id'            => "{$prefix}info_type",
							'type'          => 'text',
							'std'           => ''
						),
						array(
							'name'          => __( 'Celebrity Info Description', 'themeum-core' ),
							'id'            => "{$prefix}info_description",
							'type'          => 'textarea',
							'std'           => ''
						),
						
				    ),
				),
			)
		);


	// Celebrity Sicial Settings
	$meta_boxes[] = array(
		'id' 		=> 'celebrity-socail-post-meta',
		'title' 	=> __( 'Celebrity Social Settings', 'themeum-core' ),
		'pages' 	=> array( 'celebrity'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(
				array(
					'name'  		=> __( 'Facebook URL', 'themeum-core' ),
					'id'    		=> "{$prefix}facebook_url",
					'type'  		=> 'url',
				),
				array(
					'name'  		=> __( 'Twitter URL', 'themeum-core' ),
					'id'    		=> "{$prefix}twitter_url",
					'type'  		=> 'url',
				),
				array(
					'name'  		=> __( 'Google Plus URL', 'themeum-core' ),
					'id'    		=> "{$prefix}google_plus_url",
					'type'  		=> 'url',
				),
				array(
					'name'  		=> __( 'Youtube URL', 'themeum-core' ),
					'id'    		=> "{$prefix}youtube_url",
					'type'  		=> 'url',
				),
				array(
					'name'  		=> __( 'Instagram URL', 'themeum-core' ),
					'id'    		=> "{$prefix}instagram_url",
					'type'  		=> 'url',
				),
				array(
					'name'  		=> __( 'Website URL', 'themeum-core' ),
					'id'    		=> "{$prefix}website_url",
					'type'  		=> 'url',
				),
			)
		);


	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Celebrity Close -----------------------------------------------------	
	// --------------------------------------------------------------------------------------------------------------	



	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Woocommerce Shop Trailer Open --------------------------------------
	// --------------------------------------------------------------------------------------------------------------	

	// Movie Info Settings
	$meta_boxes[] = array(
		'id' 		=> 'trailer-post-meta',
		'title' 	=> __( 'Video Trailer Settings', 'themeum-core' ),
		'pages' 	=> array( 'product'),
		'context' 	=> 'normal',
		'priority' 	=> 'high',
		'autosave' 	=> true,
		'fields' 	=> array(

			// Movie Info
			array(
			    'name' => '', // Optional
			    'id' => 'themeum_shop_info',
			    'type'   => 'group',
			    'clone'  => false,
			    'fields' => array(
						
						array(
							'name'          => __( 'Video Info Title', 'themeum-core' ),
							'id'            => "{$prefix}video_info_title",
							'type'          => 'text',
							'std'           => ''
						),
						array(
							'name'     => esc_html__( 'Select Video Type/Source', 'themeum-core' ),
							'id'       => "{$prefix}video_source",
							'type'     => 'select',
							'options'  => array(
											'self' 		=> esc_html__( 'Self Hosted', 'themeum-core' ),
											'youtube' 	=> esc_html__( 'YouTube', 'themeum-core' ),
											'vimeo' 	=> esc_html__( 'Vimeo', 'themeum-core' ),
										),
							'multiple'    => false,
							'std'         => '1'
						),				
						array(
							'name'  		=> __( 'Video', 'themeum-core' ),
							'id'    		=> "{$prefix}video_link",
							'type'  		=> 'text',
							'std'   		=> '',
							'desc'  		=> __( 'Add Video ID for Youtube and Vimeo / Video URL for Self Hosted Video.', 'themeum-core' ),
						),
						array(
							'name'             => esc_html__( 'Upload Trailer Image', 'themeum-core' ),
							'id'               => "{$prefix}video_trailer_image",
							'type'             => 'image_advanced',
							'max_file_uploads' => 1,
						),	
						
					
			    ),
			),

			)
		);
	// --------------------------------------------------------------------------------------------------------------	
	// ----------------------------------------- Woocommerce Shop Trailer Close -------------------------------------
	// --------------------------------------------------------------------------------------------------------------	





	return $meta_boxes;
}


/**
 * Get list of post from any post type
 *
 * @return array
 */

function get_all_posts($post_type)
{
	$args = array(
			'post_type' => $post_type,  // post type name
			'posts_per_page' => -1,   //-1 for all post
		);

	$posts = get_posts($args);

	$post_list = array();

	if (!empty( $posts ))
	{
		foreach ($posts as $post)
		{
			setup_postdata($post);
			$post_list[$post->post_name] = $post->post_title;
		}
		wp_reset_postdata();
		return $post_list;
	}
	else
	{
		return $post_list;
	}	
}

