<?php
/*
    Manticore Search Plugin (contact@manticoresearch.com), 2018
    If you need commercial support, or if you’d like this plugin customized for your needs, we can help.

    Visit our website for the latest news:
    https://manticoresearch.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Office 2, Derby House, 123 Watling Street, Gillingham, Kent, ME7 2YY England
*/

/**
 * LatestSearchesWidget Class
 */
class LatestSearchesWidget extends WP_Widget {
	var $instance = null;

	/** constructor */
	function __construct() {

        parent::__construct(
            'SphinxLatestSearchesWidget',
            'Manticore Last Searches',
            [
                'classname'   => 'SphinxLatestSearchesWidget',
                'description' => 'Manticore last search terms'
            ]
        );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		$this->instance = $instance;
		extract( $args );
		$title         = !empty($instance['title'])? apply_filters( 'widget_title', $instance['title'] ):'';
		$limit         = ! empty( $instance['limit'] ) ? $instance['limit'] : 10;
		$width         = ! empty( $instance['width'] ) ? $instance['width'] : 0;
		$break         = ! empty( $instance['break'] ) ? $instance['break'] : '...';
		$show_approved = ! empty( $instance['show_approved'] ) ? $instance['show_approved'] : false;
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		$this->get_latest( $limit, $width, $break, $show_approved );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                  = $old_instance;
		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['limit']         = strip_tags( $new_instance['limit'] );
		$instance['width']         = strip_tags( $new_instance['width'] );
		$instance['break']         = strip_tags( $new_instance['break'] );
		$instance['show_approved'] = strip_tags( $new_instance['show_approved'] );
		$instance['friendly_url']  = strip_tags( $new_instance['friendly_url'] );

		return $instance;
	}

	/** @see WP_Widget::form */

	function form( $instance ) {

		$title         = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Last Searches';
		$limit         = ! empty( $instance['limit'] ) ? esc_attr( $instance['limit'] ) : 10;
		$width         = ! empty( $instance['width'] ) ? esc_attr( $instance['width'] ) : 0;
		$break         = ! empty( $instance['break'] ) ? esc_attr( $instance['break'] ) : '...';
		$show_approved = ! empty( $instance['show_approved'] ) ? esc_attr( $instance['show_approved'] ) : false;
		$friendly_url  = ! empty( $instance['friendly_url'] ) ? esc_attr( $instance['friendly_url'] ) : '';
		?>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id( 'show_approved' ); ?>"
                   name="<?php echo $this->get_field_name( 'show_approved' ); ?>"
                   type="checkbox" value="true" <?php echo $show_approved == 'true' ? 'checked="checked"' : ''; ?> />
            <label for="<?php echo $this->get_field_id( 'show_approved' ); ?>">
				<?php _e( 'Show only approved search terms:' ); ?>
            </label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>"
                       type="text" value="<?php echo $title; ?>"/>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of results:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>"
                       name="<?php echo $this->get_field_name( 'limit' ); ?>"
                       type="text" value="<?php echo $limit; ?>"/>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Maximum length of search term:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>"
                       name="<?php echo $this->get_field_name( 'width' ); ?>"
                       type="text" value="<?php echo $width; ?>"/>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Break long search term by:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'break' ); ?>"
                       name="<?php echo $this->get_field_name( 'break' ); ?>"
                       type="text" value="<?php echo $break; ?>"/>
            </label></p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id( 'friendly_url' ); ?>"
                   name="<?php echo $this->get_field_name( 'friendly_url' ); ?>"
                   type="checkbox" value="true" <?php echo ( "true" == $friendly_url ) ? 'checked="checked"' : ''; ?>/>
            <label for="<?php echo $this->get_field_id( 'friendly_url' ); ?>"><?php _e( 'Show friendly URLs:' ); ?></label>
        </p>
		<?php

	}

	function get_latest( $limit = 10, $width = 0, $break = '...', $show_approved = false ) {
		global $defaultObjectSphinxSearch;

		$result = $defaultObjectSphinxSearch::$plugin->frontend->sphinx_stats_latest( $limit, $width, $break, $show_approved );

		if ( empty( $result ) ) {
			return false;
		}

		$permalinkOption = get_option( 'permalink_structure' );
		$permPrefix      = '';
		if ( false !== strpos( $permalinkOption, '/index.php' ) ) {
			$permPrefix = '/index.php';
		}

		$html = "<ul>";
		foreach ( $result as $res ) {
			if ( isset($this->instance['friendly_url']) && "true" == $this->instance['friendly_url'] ) {
				$html .= "<li><a href='" . get_bloginfo( 'url' ) .
				         $permPrefix . "/search/" . urlencode( stripslashes( $res->keywords_full ) ) . "/' title='" .
				         htmlspecialchars( stripslashes( $res->keywords ), ENT_QUOTES ) . "'>" .
				         htmlspecialchars( stripslashes( $res->keywords_cut ), ENT_QUOTES ) . "</a></li>";
			} else {
				$html .= "<li><a href='" . get_bloginfo( 'url' ) .
				         "/?s=" . urlencode( stripslashes( $res->keywords_full ) ) . "' title='" .
				         htmlspecialchars( stripslashes( $res->keywords ), ENT_QUOTES ) . "'>" .
				         htmlspecialchars( stripslashes( $res->keywords_cut ), ENT_QUOTES ) . "</a></li>";
			}
		}
		$html .= "</ul>";
		echo $html;
	}
}


