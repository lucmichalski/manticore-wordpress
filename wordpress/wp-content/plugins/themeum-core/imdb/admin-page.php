<?php

add_action('admin_menu', 'tm_add_imdb_settings_page');

function tm_add_imdb_settings_page()
{
	add_submenu_page( 'edit.php?post_type=movie', 'IMDB Settings', 'IMDB Settings', 'manage_options', 'imdb-settings', 'imdb_settings_page_cb');
}


function imdb_settings_page_cb() {
	?>
	<div class="wrap">
		<h1>IMDB Settings</h1>
		<div>
			<form action="options.php" method="POST">
				<?php settings_fields( 'tm-imdb-api-settings' ); ?>
				<?php do_settings_sections( 'tm-imdb-plugin' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php if(get_option( 'tm_imdb_api_key', false )): ?>
		<div>
			<h2>Import Movie</h2>
			<div>
				<input type="text" id="imdb-movie" class="regular-text" placeholder="IMDB Movie ID">
				<input type="button" class="button button-primary" id="add-movie" value="Add Movie">
				<p class="description imdb-import-desc">Import movie by Movie ID. Example: if this is movie link from IMDB http://www.imdb.com/title/tt0918940, This (<code>tt0918940</code>) is movie ID.</p>
			</div>

			<div id="tm-imdb-spinner">
				<span class="spinner is-active" style="float:none;"></span> Your Movie is importing....
			</div>
			<div id="tm-imdb-message"></div>
		</div>
		<style>
			.imdb-import-desc{
				margin-bottom: 30px !important;
    			margin-top: 15px !important;
			}
			#tm-imdb-spinner{
				display: none;
				padding: 12px;
    			font-weight: 700;
				background-color: #fff;
				-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    			box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
			}

			#tm-imdb-spinner .spinner{
    			margin-top: 0 !important;
			}

			#tm-imdb-message{
				display: none;
				padding: 15px;
				-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    			box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
			}

			#tm-imdb-message.error{
				border-left: 3px solid #dc3232;
			}

			#tm-imdb-message.updated{
				border-left: 3px solid #46b450;
			}
		</style>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_init', 'tm_add_imdb_api_key_fields' );
function tm_add_imdb_api_key_fields() {
    register_setting( 'tm-imdb-api-settings', 'tm_imdb_api_key' );
    register_setting( 'tm-imdb-api-settings', 'tm_max_time_out' );
    add_settings_section( 'imdb-api-section', 'IMDB API Settings', 'tm_imdb_api_section_cb', 'tm-imdb-plugin' );
    add_settings_field( 'imdb-api-key', 'API key', 'tm_imdb_api_key_field_cb', 'tm-imdb-plugin', 'imdb-api-section' );
    add_settings_field( 'max-timeout', 'Set Timeout', 'tm_max_time_out_field_cb', 'tm-imdb-plugin', 'imdb-api-section' );
}

function tm_imdb_api_section_cb() {
    echo 'IMDB API Key Settings';
}

function tm_imdb_api_key_field_cb() {
    $tm_imdb_api_key = esc_attr( get_option( 'tm_imdb_api_key' ) );
    echo "<input type='text' name='tm_imdb_api_key' value='$tm_imdb_api_key' class='regular-text' />";
    echo '<p class="description">To Import data you need a token. Get token from here <a href="http://www.myapifilms.com/token.do" target="_blank">Get Token</a></p>';
}

function tm_max_time_out_field_cb() {
    $tm_max_time_out = esc_attr( get_option( 'tm_max_time_out', 300 ) );
    echo "<input type='number' name='tm_max_time_out' value='$tm_max_time_out' class='regular-text' />";
    echo '<p class="description">Set <code>max_execution_time</code> to avoid timeout to import movie.</p>';
}

function tm_enqueue_admin_assests() {
	wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'imdb-js', plugins_url('assets/js/main.js',__FILE__), array( 'jquery' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'tm_enqueue_admin_assests' );


