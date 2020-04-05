/*global $:false */
jQuery(document).ready(function($){'use strict';

	jQuery('input[type=radio][name=rating]').on('change',function(event) {
		for (var i = 0; i <= 10 ; i++) {
			if( i <= jQuery(this).closest( "span" ).index() ){
				jQuery('.commentratingbox span:eq('+i+')').addClass('active');
			}else{
				jQuery('.commentratingbox span:eq('+i+')').removeClass('active');
			}
		}
	});

});