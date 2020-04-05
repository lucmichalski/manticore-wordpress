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
 * TopSearchesWidget Class
 */
class TopSearchesWidget extends WP_Widget {
	var $instance = null;

	/** constructor */
	function __construct() {

		parent::__construct( 'TopSearchesWidget', 'Manticore Related/Top Searches', [
			'classname'   => 'TopSearchesWidget',
			'description' => 'Manticore related/top search terms'
		] );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		$this->instance = $instance;
		extract( $args );
		if ( ! empty( $instance['title_rel'] ) ) {
			$title_rel = apply_filters( 'widget_title', $instance['title_rel'] );
		} else {
			$title_rel = '';
        }

		if ( ! empty( $instance['title_top'] ) ) {
			$title_top = apply_filters( 'widget_title', $instance['title_top'] );
		} else {
			$title_top = '';
        }

		$width            = ! empty( $instance['width'] ) ? $instance['width'] : 0;
		$break            = ! empty( $instance['break'] ) ? $instance['break'] : '...';
		$custom_terms_top = ! empty( $instance['custom_terms_top'] ) ? $instance['custom_terms_top'] : '';
		$front_show       = ! empty( $instance['front_show'] ) ? $instance['front_show'] : 'show';
		$posts_show       = ! empty( $instance['front_show'] ) ? $instance['posts_show'] : 'show_related';
		$search_show      = ! empty( $instance['front_show'] ) ? $instance['search_show'] : 'show_related';
		$show_approved    = ! empty( $instance['show_approved'] ) ? $instance['show_approved'] : false;
		$period_limit     = ! empty( $instance['period_limit'] ) ? intval( $instance['period_limit'] ) : '';

		$show_widget = false;
		//if it is post
		if ( is_search() && $search_show != 'hide' ) {
			$limit = ! empty( $instance['search_limit'] ) ? $instance['search_limit'] : 10;
			if ( $search_show == 'show_related' ) {
				$title      = $title_rel;
				$words_html = $this->get_related( get_search_query(), $limit, $width, $break, $show_approved );
			}
			if ( empty( $words_html ) || $search_show == 'show_top' ) {
				$title      = $title_top;
				$words_html = $this->get_top( $limit, $width, $break, $custom_terms_top, $show_approved,
					$period_limit );
			}
			$show_widget = true;
		} elseif ( is_singular() && $posts_show != 'hide' ) {
			$limit = ! empty( $instance['posts_limit'] ) ? $instance['posts_limit'] : 10;
			if ( $posts_show == 'show_related' ) {
				$title      = $title_rel;
				$keywords   = single_post_title( '', false );
				$words_html = $this->get_related( $keywords, $limit, $width, $break, $show_approved );
			}
			if ( empty( $words_html ) || $posts_show == 'show_top' ) {
				$title      = $title_top;
				$words_html = $this->get_top( $limit, $width, $break, $custom_terms_top, $show_approved,
					$period_limit );
			}
			$show_widget = true;
		} elseif ( $front_show != 'hide' ) {
			$title       = $title_top;
			$limit       = ! empty( $instance['front_limit'] ) ? $instance['front_limit'] : 10;
			$words_html  = $this->get_top( $limit, $width, $break, $custom_terms_top, $show_approved, $period_limit );
			$show_widget = true;
		}

		if ( $show_widget ) {
			echo $before_widget;
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			echo $words_html;
			echo $after_widget;
		}
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                     = $old_instance;
		$instance['title_rel']        = strip_tags( $new_instance['title_rel'] );
		$instance['title_top']        = strip_tags( $new_instance['title_top'] );
		$instance['front_limit']      = strip_tags( $new_instance['front_limit'] );
		$instance['front_show']       = strip_tags( $new_instance['front_show'] );
		$instance['posts_limit']      = strip_tags( $new_instance['posts_limit'] );
		$instance['posts_show']       = strip_tags( $new_instance['posts_show'] );
		$instance['search_limit']     = strip_tags( $new_instance['search_limit'] );
		$instance['search_show']      = strip_tags( $new_instance['search_show'] );
		$instance['custom_terms_top'] = strip_tags( $new_instance['custom_terms_top'] );
		$instance['width']            = strip_tags( $new_instance['width'] );
		$instance['break']            = strip_tags( $new_instance['break'] );
		$instance['show_approved']    = strip_tags( $new_instance['show_approved'] );
		$instance['period_limit']     = strip_tags( $new_instance['period_limit'] );
		$instance['friendly_url']     = strip_tags( $new_instance['friendly_url'] );

		return $instance;
	}

	/** @see WP_Widget::form */

	function form( $instance ) {

		$title_rel        = ! empty( $instance['title_rel'] ) ? esc_attr( $instance['title_rel'] ) : 'Related Searches';
		$title_top        = ! empty( $instance['title_top'] ) ? esc_attr( $instance['title_top'] ) : 'Top Searches';
		$front_show       = ! empty( $instance['front_show'] ) ? esc_attr( $instance['front_show'] ) : 'show';
		$front_limit      = ! empty( $instance['front_limit'] ) ? esc_attr( $instance['front_limit'] ) : 10;
		$posts_show       = ! empty( $instance['posts_show'] ) ? esc_attr( $instance['posts_show'] ) : 'show_related';
		$posts_limit      = ! empty( $instance['posts_limit'] ) ? esc_attr( $instance['posts_limit'] ) : 10;
		$search_show      = ! empty( $instance['search_show'] ) ? esc_attr( $instance['search_show'] ) : 'show_related';
		$search_limit     = ! empty( $instance['search_limit'] ) ? esc_attr( $instance['search_limit'] ) : 10;
		$custom_terms_top = ! empty( $instance['custom_terms_top'] ) ? esc_attr( $instance['custom_terms_top'] ) : '';
		$show_approved    = ! empty( $instance['show_approved'] ) ? esc_attr( $instance['show_approved'] ) : false;
		$width            = ! empty( $instance['width'] ) ? esc_attr( $instance['width'] ) : 0;
		$break            = ! empty( $instance['break'] ) ? esc_attr( $instance['break'] ) : '...';
		$period_limit     = ! empty( $instance['period_limit'] ) ? intval( $instance['period_limit'] ) : '';
		$friendly_url     = ! empty( $instance['friendly_url'] ) ? esc_attr( $instance['friendly_url'] ) : '';
		?>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id( 'show_approved' ); ?>"
                   name="<?php echo $this->get_field_name( 'show_approved' ); ?>"
                   type="checkbox" value="true" <?php echo $show_approved == 'true' ? 'checked="checked"' : ''; ?> />
            <label for="<?php echo $this->get_field_id( 'show_approved' ); ?>">
				<?php _e( 'Show only approved search terms' ); ?>
            </label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'period_limit' ); ?>">
				<?php _e( 'Show top Searches for last:' ); ?>
                <select class="widefat" id="<?php echo $this->get_field_id( 'period_limit' ); ?>"
                        name="<?php echo $this->get_field_name( 'period_limit' ); ?>">>
                    <option value="1"
						<?php echo ( $period_limit == 1 ) ? '  selected="selected"' : '' ?>
                    >One day
                    </option>
                    <option value="7"
						<?php echo ( $period_limit == 7 ) ? '  selected="selected"' : '' ?>
                    >One week
                    </option>
                    <option value="30"
						<?php echo ( $period_limit == 30 ) ? '  selected="selected"' : '' ?>
                    >One month
                    </option>
                    <option value="91"
						<?php echo ( $period_limit == 91 ) ? '  selected="selected"' : '' ?>
                    >3 months
                    </option>
                    <option value="182"
						<?php echo ( $period_limit == 182 ) ? '  selected="selected"' : '' ?>
                    >6 months
                    </option>
                    <option value="365"
						<?php echo ( $period_limit == 365 ) ? '  selected="selected"' : '' ?>
                    >One year
                    </option>
                    <option value=""
						<?php echo ( $period_limit == '' ) ? '  selected="selected"' : '' ?>
                    >Show all dates
                    </option>
                </select>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'title_top' ); ?>">
				<?php _e( 'Title top:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title_top' ); ?>"
                       name="<?php echo $this->get_field_name( 'title_top' ); ?>"
                       type="text" value="<?php echo $title_top; ?>"/>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'title_rel' ); ?>">
				<?php _e( 'Title related:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title_rel' ); ?>"
                       name="<?php echo $this->get_field_name( 'title_rel' ); ?>"
                       type="text" value="<?php echo $title_rel; ?>"/>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'front_show' ); ?>">
				<?php _e( 'Show on front page:' ); ?>
                <select class="widefat" id="<?php echo $this->get_field_id( 'front_show' ); ?>"
                        name="<?php echo $this->get_field_name( 'front_show' ); ?>">>
                    <option value="show"
						<?php echo ( $front_show == 'show' ) ? '  selected="selected"' : '' ?>
                    >Show top searches
                    </option>
                    <option value="hide"
						<?php echo ( $front_show == 'hide' ) ? '  selected="selected"' : '' ?>
                    >Do not show
                    </option>
                </select>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'front_limit' ); ?>">
				<?php _e( 'Number of results:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'front_limit' ); ?>"
                       name="<?php echo $this->get_field_name( 'front_limit' ); ?>"
                       type="text" value="<?php echo $front_limit; ?>"/>
            </label></p>

        <p><label for="<?php echo $this->get_field_id( 'posts_show' ); ?>">
				<?php _e( 'Show on post pages:' ); ?>
                <select class="widefat" id="<?php echo $this->get_field_id( 'posts_show' ); ?>"
                        name="<?php echo $this->get_field_name( 'posts_show' ); ?>">
                    <option value="show_related"
						<?php echo ( $posts_show == 'show_related' ) ? '  selected="selected"' : '' ?>
                    >Show related searches
                    </option>
                    <option value="show_top"
						<?php echo ( $posts_show == 'show_top' ) ? '  selected="selected"' : '' ?>
                    >Show top searches
                    </option>
                    <option value="hide"
						<?php echo ( $posts_show == 'hide' ) ? '  selected="selected"' : '' ?>
                    >Do not show
                    </option>
                </select>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'posts_limit' ); ?>">
				<?php _e( 'Number of results:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'posts_limit' ); ?>"
                       name="<?php echo $this->get_field_name( 'posts_limit' ); ?>"
                       type="text" value="<?php echo $posts_limit; ?>"/>
            </label></p>

        <p><label for="<?php echo $this->get_field_id( 'search_show' ); ?>">
				<?php _e( 'Show on search pages:' ); ?>
                <select class="widefat" id="<?php echo $this->get_field_id( 'search_show' ); ?>"
                        name="<?php echo $this->get_field_name( 'search_show' ); ?>">
                    <option value="show_related"
						<?php echo ( $search_show == 'show_related' ) ? '  selected="selected"' : '' ?>
                    >Show related searches
                    </option>
                    <option value="show_top"
						<?php echo ( $search_show == 'show_top' ) ? '  selected="selected"' : '' ?>
                    >Show top searches
                    </option>
                    <option value="hide"
						<?php echo ( $search_show == 'hide' ) ? '  selected="selected"' : '' ?>
                    >Do not show
                    </option>
                </select>
            </label></p>
        <p><label for="<?php echo $this->get_field_id( 'search_limit' ); ?>">
				<?php _e( 'Number of results:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'search_limit' ); ?>"
                       name="<?php echo $this->get_field_name( 'search_limit' ); ?>"
                       type="text" value="<?php echo $search_limit; ?>"/>
            </label></p>

        <p><label for="<?php echo $this->get_field_id( 'custom_terms_top' ); ?>">
				<?php _e( 'Add your own terms to always display in Top searches list:' ); ?>
                <textarea class="widefat" cols="20" rows="5"
                          id="<?php echo $this->get_field_id( 'custom_terms_top' ); ?>"
                          name="<?php echo $this->get_field_name( 'custom_terms_top' ); ?>"
                ><?php echo $custom_terms_top; ?></textarea>
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

	function get_top(
		$limit = 10,
		$width = 0,
		$break = '...',
		$custom_top = '',
		$show_approved = false,
		$period_limit = 30
	) {
		global $defaultObjectSphinxSearch;

		$custom_top      = trim( $custom_top );
		$custom_top_arry = explode( "\n", $custom_top );

		$permalinkOption = get_option( 'permalink_structure' );
		$permPrefix      = '';
		if ( false !== strpos( $permalinkOption, '/index.php' ) ) {
			$permPrefix = '/index.php';
		}

		$html = "<ul>";
		if ( ! empty( $custom_top_arry ) ) {
			foreach ( $custom_top_arry as $ind => $term ) {
				if ( $limit <= 0 ) {
					break;
				}
				$term = trim( $term );
				if ( empty( $term ) ) {
					unset( $custom_top_arry[ $ind ] );
					continue;
				}
				$limit --;
				if ( "true" == $this->instance['friendly_url'] ) {
					$html .= "<li><a href='" . get_bloginfo( 'url' ) .
					         $permPrefix . "/search/" . urlencode( stripslashes( $term ) ) . "/' title='" .
					         htmlspecialchars( stripslashes( $term ), ENT_QUOTES ) . "'>" .
					         htmlspecialchars( stripslashes( $term ), ENT_QUOTES ) . "</a></li>";
				} else {
					$html .= "<li><a href='" . get_bloginfo( 'url' ) .
					         "/?s=" . urlencode( stripslashes( $term ) ) . "' title='" .
					         htmlspecialchars( stripslashes( $term ), ENT_QUOTES ) . "'>" .
					         htmlspecialchars( stripslashes( $term ), ENT_QUOTES ) . "</a></li>";
				}
			}
		}

		if ( $limit <= 0 ) {
			$html .= "</ul>";

			return $html;
		}

		$result = $defaultObjectSphinxSearch::$plugin->frontend->sphinx_stats_top( $limit, $width, $break, $show_approved,
			$period_limit );
		if ( empty( $result ) ) {
			$html .= "</ul>";

			return $html;
		}

		foreach ( $result as $res ) {
			if (isset($this->instance['friendly_url']) &&  "true" == $this->instance['friendly_url'] ) {
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

		return $html;
	}

	function get_related( $keywords, $limit = 10, $width = 0, $break = '...', $show_approved = false ) {
		global $defaultObjectSphinxSearch;

		$result = $defaultObjectSphinxSearch::$plugin->frontend->sphinx_stats_related( $keywords, $limit, $width, $break,
			$show_approved );
		if ( empty( $result ) ) {
			return false;
		}

		$permalinkOption = get_option( 'permalink_structure' );
		$permPrefix      = '';
		if ( false !== strpos( $permalinkOption, '/index.php' ) ) {
			$permPrefix = '/index.php';
		}

		$html = '';
		$html .= "<ul>";
		foreach ( $result as $res ) {
			if (isset($this->instance['friendly_url']) && "true" == $this->instance['friendly_url'] ) {
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

		return $html;
	}
}


