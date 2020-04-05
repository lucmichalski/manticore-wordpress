<?php
/*
 * Front-end Megamenu Class
 */

class Megamenu_Walker extends Walker_Nav_menu{

	private $megamenuId;
	private $columnId;

	function start_lvl( &$output, $depth = 0, $args = array() ) {

        $indent = str_repeat("\t", $depth);

        if($this->megamenuId == 1){
        	$output .= "\n$indent<ul role=\"menu\" class=\"sub-menu megamenu megacol-".$this->columnId."\">\n";
        }else{
        	$output .= "\n$indent<ul role=\"menu\" class=\"sub-menu\">\n";
        }
    }	

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
	{
		global $wp_query;

		$megamenu = 0;
		$column = 1;

		if($depth == 1){            
			$column = get_post_meta( $item->menu_item_parent, '_menu_item_column', true );
			$megamenu = get_post_meta( $item->menu_item_parent, '_menu_item_megamenu', true );
		}

		$this->megamenuId	 = get_post_meta( $item->ID, '_menu_item_megamenu', true );
		$this->columnId		 = get_post_meta( $item->ID, '_menu_item_column', true );


		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = $value = '';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$class_names = join(' ', $classes);

		$class_megamenu = ( $item->megamenu )? ' has-megasub': '';

		if ( $megamenu == 1 ) {
			$class_megamenu .= ' mega-child';
		} else {
			$class_megamenu .= ' has-menu-child';
		}
		
		$post = get_post($item->object_id,ARRAY_A);
		$slug = $post['post_name'];

		if (is_singular('portfolio') &&  $slug == 'portfolio') {
			$class_names = 'class="'.esc_attr( $class_names ) . $class_megamenu.' active"';
		}else if (is_singular('album') &&  $slug == 'albums') {
			$class_names = 'class="'.esc_attr( $class_names ) . $class_megamenu.' active"';
		}else if (is_singular('gallery') &&  $slug == 'gallery') {
			$class_names = 'class="'.esc_attr( $class_names ) . $class_megamenu.' active"';
		}else if(is_singular('event') || is_singular('album') || is_singular('gallery')){
			$class_names = 'class="'.esc_attr( $class_names ) . $class_megamenu.'"';
		}else{
			if(in_array('current-menu-parent', $classes)) { $class_names .= ' active'; }
			if(in_array('current_page_parent', $classes)) { $class_names .= ' active'; }
			if(in_array('current-menu-item', $classes)) { $class_names .= ' active'; }
			$class_names = 'class="'.esc_attr( $class_names ) . $class_megamenu.'"';
		}

		$output .= $indent . '<li ' . $value . $class_names.'>';

		//$attributes  = ! empty( $item->title ) 		? ' title="'  . esc_attr( $item->title 		) .'"' : '';
       	$attributes = ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
       	$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
       	$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

       	$description  = ! empty( $item->description ) ? '<div class="custom-output">'.esc_attr( $item->description ).'</div>' : '';

       	$item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );            
        $item_output .= $args->link_after;
        //$item_output .= $args->link_after.' <span class="subtitle">'.esc_attr($item->attr_title).'</span>';
        $item_output .= '</a>'.do_shortcode($description);
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}