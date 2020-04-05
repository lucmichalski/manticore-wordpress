<?php
add_shortcode( 'themeum_title', function($atts, $content = null) {

	extract(shortcode_atts(array(
		'title' 				=> '',
		'layout' 				=> 'normal_title',
		'border_en' 			=> 'yes',
		'icon_select' 			=> 'awesome_icon',
		'title_weight'			=> '400',
		'fontawesome_icon' 		=> '',
		'themeum_icon' 			=> '',
		'icon_color' 			=> '',
		'icon_size' 			=> '35',
		'title_heading' 		=> 'h3',
		'color'					=> '',
		'size'					=> '24',
		'btn_url'				=> '',
		'btn_text'				=> '',
		'class'					=> ''
		), $atts));

	$inline1 ='';
	$icon_style = '';
	$output = '';

	if($icon_color) $icon_style .= 'color:' . esc_attr( $icon_color ) .';';
	if($icon_size) $icon_style .= 'font-size:' . (int) esc_attr( $icon_size ) . 'px;line-height: normal;';

	$output .= '<div class="themeum-title '.esc_attr($border_en).' '.esc_attr( $class ).'">';
	if ($title) {
		if($title_weight) $inline1 .= 'font-weight:' . (int) esc_attr( $title_weight ) . ';';
		if($size) $inline1 .= 'font-size:' . (int) esc_attr( $size ) . 'px;line-height: normal;';
		if($color) $inline1 .= 'color:' . esc_attr( $color )  . ';';
		if ( $layout == 'with_icon' ) {
			if ( $icon_select == 'awesome_icon' ) {
				$output .= '<'.$title_heading.' class="style-title" style="'.$inline1.'"><span class="title-icon-style" style="'.$icon_style.'"><i class="fa ' . esc_attr($fontawesome_icon) . '"></i></span>' . esc_attr( $title ) . '</'.$title_heading.'>';	
			} else {
				$output .= '<'.$title_heading.' class="style-title" style="'.$inline1.'"><span class="title-icon-style" style="'.$icon_style.'"><i class="themeum-'. esc_attr($themeum_icon) . '"></i></span>' . esc_attr( $title ) . '</'.$title_heading.'>';	
			}	
		} else {
			$output .= '<'.$title_heading.' class="style-title" style="'.$inline1.'">' . esc_attr( $title ) . '</'.$title_heading.'>';
		}	
	}		
	if($btn_url && $btn_text) {
		$output .= '<a href="'. esc_url( $btn_url ).'" class="title-link pull-right">' .esc_attr( $btn_text ). '<i class="themeum-moviewangle-double-right"></i></a>';
	}

	$output .= '</div>';

	return $output;

});


//Visual Composer
if (class_exists('WPBakeryVisualComposerAbstract')) {
vc_map(array(
	"name" => esc_html__("Title", 'themeum-core'),
	"base" => "themeum_title",
	'icon' => 'icon-thm-title',
	"class" => "",
	"description" => esc_html__("Widget Title Heading", 'themeum-core'),
	"category" => esc_html__('Moview', 'themeum-core'),
	"params" => array(

		array(
			"type" => "textfield",
			"heading" => esc_html__("Title", 'themeum-core'),
			"param_name" => "title",
			"value" => "",
		),	

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Title Style", 'themeum-core'),
			"param_name" => "layout",
			"value" => array('None'=>'','Title With Icon'=>'with_icon','Normal Title'=>'normal_title'),
		),

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Border Enable", 'themeum-core'),
			"param_name" => "border_en",
			"value" => array('None'=>'','NO'=>'no','YES'=>'yes'),
		),	

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Slect Icon", 'themeum-core'),
			"param_name" => "icon_select",
			"value" => array('None'=>'','moview icon'=>'moview_icon','Fontawesome Icon'=>'awesome_icon'),
			"dependency" => array("element" => "layout", "value" => array("with_icon"))
		),		

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Fontawesome Icon Name ", 'themeum-core'),
			"param_name" => "fontawesome_icon",
			"value" => getIconsList(),
			"admin_label"=>true,
			"dependency" => array("element" => "icon_select", "value" => array("awesome_icon"))
		),

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Moview Icon Name ", 'themeum-core'),
			"param_name" => "themeum_icon",
			"value" => getMoviewIconsList(),
			"admin_label"=>true,
			"dependency" => array("element" => "icon_select", "value" => array("moview_icon"))
		),	

		array(
			"type" => "textfield",
			"heading" => esc_html__("Icon Font Size", 'themeum-core'),
			"param_name" => "icon_size",
			"value" => "",
			"dependency" => array("element" => "layout", "value" => array("with_icon"))
		),			

		array(
			"type" => "colorpicker",
			"heading" => esc_html__("Icon Color", 'themeum-core'),
			"param_name" => "icon_color",
			"value" => "",
			"dependency" => array("element" => "layout", "value" => array("with_icon"))
		),	

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Title Heading", 'themeum-core'),
			"param_name" => "title_heading",
			"value" => array('Select'=>'','h1'=>'h1','h2'=>'h2','h3'=>'h3','h4'=>'h4','h5'=>'h5','h6'=>'h6'),
		),				

		array(
			"type" => "textfield",
			"heading" => esc_html__("Title Font Size", 'themeum-core'),
			"param_name" => "size",
			"value" => "",
		),	

		array(
			"type" => "dropdown",
			"heading" => esc_html__("Title Font Weight", 'themeum-core'),
			"param_name" => "title_weight",
			"value" => array('Select'=>'','200'=>'200','300'=>'300','400'=>'400','500'=>'500','600'=>'600','700'=>'700'),
		),									

		array(
			"type" => "colorpicker",
			"heading" => esc_html__("Title Color", 'themeum-core'),
			"param_name" => "color",
			"value" => "",
		),			

		array(
			"type" => "textfield",
			"heading" => esc_html__("Button URL", 'themeum-core'),
			"param_name" => "btn_url",
			"value" => "",
		),	

		array(
			"type" => "textfield",
			"heading" => esc_html__("Button Text", 'themeum-core'),
			"param_name" => "btn_text",
			"value" => "",
		),		

		array(
			"type" => "textfield",
			"heading" => esc_html__("Custom Class ", 'themeum-core'),
			"param_name" => "class",
			"value" => "",
		),

		)
	));
}