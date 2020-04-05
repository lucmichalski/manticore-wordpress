<?php 
?>
<div id="moview_search" class="moview-search moview_search">
	<div class="input-group moview-search-wrap">
		<form id="moview-search"  action="<?php echo esc_url(home_url( '/' )); ?>">
			<div class="search-panel">
				<div class="select-menu">
					<select name="searchtype" id="searchtype" class="selectpicker">
						<option value="movie"> <?php esc_html_e('Movies','moview');?></option>
						<option value="celebrity"><?php esc_html_e('Celebrities','moview');?></option>
					</select>
				</div>
			</div>
			<div class="input-box">
				<input type="hidden" id="post-type-name" name="post_type" value="movie">
				<input type="text" id="searchword" name="s" class="moview-search-input form-control" value="" placeholder="" autocomplete="off" data-url="<?php echo get_template_directory_uri().'/lib/search-data.php'; ?>">
			</div>
			<span class="search-icon">
				<button type="submit" class="moview-search-submit">
					<span class="moview-search-icons"> 
						<i class="themeum-moviewsearch"></i>
					</span>
				</button>
			</span>
		</form>	
		<div class="moview-search-results"></div>
	</div>
</div>