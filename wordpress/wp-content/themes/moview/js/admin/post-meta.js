jQuery(document).ready(function($){ 'use strict';

	$('#post-formats-select input').change(checkFormate2);
	$('#adv-settings input').change(checkFormate);

	function checkFormate(){
		for (var i =0; i <= $( '#adv-settings input:checked').length; i++) {
			var formate2 = $( '#adv-settings input:checked:eq('+i+')' ).attr('value');
			if(typeof formate2 != 'undefined'){
				$( '#'+formate2 ).show();
			}
		}
	}

	function checkFormate2(){
		for (var i =0; i <= $( '#post-formats-select input').length; i++) {
			var formate = $( '#post-formats-select input:eq('+i+')' ).attr('value');
			$('div[id^=post-meta-'+formate+']').hide();
		}
		var formate = $( '#post-formats-select input:checked' ).attr('value');
		if(typeof formate != 'undefined'){
			$('div[id^=post-meta-'+formate+']').stop(true,true).fadeIn(600);
		}
	}

	$(window).load(function(){'use strict',
		//checkFormate2();
		checkFormate();
		checkFormate2();
	})

});