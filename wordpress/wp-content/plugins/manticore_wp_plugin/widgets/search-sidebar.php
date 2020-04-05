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
 * SearcheSidebarWidget Class
 */
class SearchSidebarWidget extends WP_Widget {
	/**
	 * Define use WP AJAX if true, or widget path if false.
	 * Widget without init WP works more quickly
	 *
	 * @var bool
	 */
	private $useSystemPath = true;

	/** constructor */
	function __construct() {

		parent::__construct(
			'SearchSidebarWidget',
			$name = 'Manticore Search sidebar',
			[
				'classname'   => 'SearchSidebarWidget',
				'description' => 'Manticore search sidebar'
			] );


		$config = '';

		if ( ManticoreSearch::$plugin->config->admin_options['is_autocomplete_configured'] == 'true' ) {

			$config_path = SPHINXSEARCH_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'autocomplete' .
			               DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'config.ini.php';

			if ( file_exists( $config_path ) ) {
				$config = (object) parse_ini_file( $config_path );
			}
		}


		if ( ! empty( $config->table ) ) {
			/*
			 * Checking default is default config readable
			 */
			$this->useSystemPath = false;
		} else {

			/*
			 * Default config is not readable or empty
			 * Checking tmp config
			 */

			$tmpDir      = sys_get_temp_dir();
			$config_path = $tmpDir . DIRECTORY_SEPARATOR . 'autocomplete_'.get_current_blog_id().'.tmp';

			if ( file_exists( $config_path ) ) {
				$config = (object) parse_ini_file( $config_path );
			}

			if ( empty( $config->table ) ) {

				/*
				 * Tmp config absent
				 */
				if ( $this->makeConfig( $config_path ) ) {
					/*
					 * Try save config, if saved => use it
					 */
					$this->useSystemPath = false;
				}
			} else {
				$this->useSystemPath = false;
			}
		}
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		if ( empty( $instance['title'] ) ) {
			$instance['title'] = '';
		}
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		$this->get_sidebar();
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	function makeConfig( $filename ) {
		global $table_prefix;
		$tags_names = get_tags();
		$tags       = [];
		if ( ! empty( $tags_names ) ) {
			foreach ( $tags_names as $tag ) {
				$tags[] = $tag->slug;
			}
		}

		if ( empty( ManticoreSearch::$plugin->config->admin_options['search_in_blogs'] ) ) {
			ManticoreSearch::$plugin->config->admin_options['search_in_blogs'] = [ get_current_blog_id() ];
		}

		if ( ManticoreSearch::$plugin->config->admin_options['manticore_use_http'] == 'true' ) {

			$connect = 'use_remote = 1' . PHP_EOL .
			           'api_host = "' . ManticoreSearch::$plugin->config->admin_options['api_host'] . '"' . PHP_EOL .
			           'secure_token = "' . ManticoreSearch::$plugin->config->admin_options['secure_key'] . '"' . PHP_EOL;
		} else {
			$connect = ( ManticoreSearch::$plugin->config->admin_options['sphinx_use_socket'] == 'true'
					? 'searchd_socket = "' . ManticoreSearch::$plugin->config->admin_options['sphinx_socket'] . '"'
					: 'searchd_host = "' . ManticoreSearch::$plugin->config->admin_options['sphinx_host'] . '"' . PHP_EOL .
					  'searchd_port = ' . ManticoreSearch::$plugin->config->admin_options['sphinx_port'] ) . PHP_EOL;
		}

		$content = ';<?die;?>' . PHP_EOL .
		           $connect .
		           'main_index = "'.Manticore_Config_Maker::MAIN_DISTRIBUTED_INDEX_PREFIX.get_current_blog_id().'"'. PHP_EOL .
		           'autocomplete_index = "'.Manticore_Config_Maker::AUTOCOMPLETE_DISTRIBUTED_INDEX_PREFIX.get_current_blog_id().'"'. PHP_EOL .
		           'search_in_blogs = "'.implode(' |*| ', ManticoreSearch::$plugin->config->admin_options['search_in_blogs']).'"'.PHP_EOL.
		           'blog_id = '.get_current_blog_id(). PHP_EOL .
                   'tags_names = "' . implode( ' |*| ', $tags ) . '"' . PHP_EOL .
		           'taxonomy_names = "' . implode( ' |*| ', ManticoreSearch::$plugin->config->admin_options['taxonomy_indexing_fields'] ) . '"' . PHP_EOL .
		           'custom_fields_names = "' . implode( ' |*| ', ManticoreSearch::$plugin->config->admin_options['custom_fields_for_indexing'] ) . '"' . PHP_EOL .
		           'suggest_on = "edge"' . PHP_EOL .
		           'corrected_dic_size = 10000000' . PHP_EOL .
		           'corrected_str_mlen = 2' . PHP_EOL .
		           'corrected_levenshtein_limit = 20' . PHP_EOL .
		           'corrected_levenshtein_min = 5' . PHP_EOL .
		           'mysql_host = "' . DB_HOST . '"' . PHP_EOL .
		           'mysql_posts_table = "' . $table_prefix . 'posts' . '"' . PHP_EOL .
		           'mysql_user = "' . DB_USER . '"' . PHP_EOL .
		           'mysql_pass = "' . DB_PASSWORD . '"' . PHP_EOL .
		           'mysql_db = "' . DB_NAME . '"';

		return file_put_contents( $filename, $content );
	}

	/** @see WP_Widget::form */

	function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>"
                       type="text" value="<?php echo $title; ?>"/>
            </label></p>
		<?php

	}

	function get_sidebar() {
		global $defaultObjectSphinxSearch;

		if ( 'true' == ManticoreSearch::$plugin->frontend->params['search_posts'] ) {
			$search_posts = "checked='checked'";
		} else {
			$search_posts = '';
		}

		if ( 'true' == ManticoreSearch::$plugin->frontend->params['search_pages'] ) {
			$search_pages = "checked='checked'";
		} else {
			$search_pages = '';
		}

		if ( 'true' == ManticoreSearch::$plugin->frontend->params['search_comments'] ) {
			$search_comments = "checked='checked'";
		} else {
			$search_comments = '';
		}

		$autocomplete_enabled = ManticoreSearch::$plugin->config->admin_options['autocomplete_enable'];

		$search_sorting = ManticoreSearch::$plugin->config->admin_options['search_sorting'];

		$search_sortby_relevance = $search_sortby_date = '';
		if ( ! empty( ManticoreSearch::$plugin->frontend->params['search_sortby'] ) ) {
			$ss_sort_by = ManticoreSearch::$plugin->frontend->params['search_sortby'];
		}
		if ( ! empty( $ss_sort_by ) && $ss_sort_by == 'date' || $ss_sort_by == 'date_added' ) {
			$search_sortby_date = 'checked="true"';
		} elseif ( ! empty( $ss_sort_by ) && $ss_sort_by == 'relevance' ) {
			$search_sortby_relevance = 'checked="true"';
		} else {
			$search_sortby_date      = 'checked="true"';
			$search_sortby_relevance = 'checked="true"';
		}

		if ( ! $this->useSystemPath ) {
			$autocomplete_url = plugins_url(
				'autocomplete/autocomplete.php?search_in=' . get_current_blog_id(), __DIR__ );
		} else {
			$autocomplete_url = get_admin_url( '', 'admin-ajax.php?search_in=' . get_current_blog_id() );
		}


		require_once( SPHINXSEARCH_PLUGIN_DIR . '/templates/sphinx_search_bar.htm' );
	}
}


