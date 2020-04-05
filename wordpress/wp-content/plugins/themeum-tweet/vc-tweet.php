<?php

//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
	vc_map(array(
		"name" => esc_html__("Tweets", 'themeum-tweet'),
		"base" => "themeum_tweet",
		'icon' => 'icon-thm-tweets',
		"class" => "",
		"description" => esc_html__("Widget Tweets Show", 'themeum-tweet'),
		"category" => esc_html__('Moview', 'themeum-tweet'),
		"params" => array(

			array(
				"type" => "textfield",
				"heading" => esc_html__("Username", "themeum-tweet"),
				"param_name" => "username",
				"value" => "themeum",
				),

		    array(
		        "type" => "textfield",
		        "heading" => esc_html__("Number of Post Show", "themeum-tweet"),
		        "param_name" => "count",
		        "value" => "",
		        ),

		    array(
		          'type' => 'checkbox',
		          'heading' => esc_html__( 'Show Avatar', 'themeum-tweet' ),
		          'param_name' => 'avatar',
		          'value' => array( esc_html__( 'avatar', 'themeum-tweet' ) => true )
		        ),		

		    array(
		          'type' => 'checkbox',
		          'heading' => esc_html__( 'Tweet Time', 'themeum-tweet' ),
		          'param_name' => 'tweet_time',
		          'value' => array( esc_html__( 'Tweet Time', 'themeum-tweet' ) => true )
		        ),	

			array(
		        "type" => "textfield",
		        "heading" => esc_html__("Add Class", "themeum-tweet"),
		        "param_name" => "add_class",
		        "value" => "",
		      ),

			)

		));
}