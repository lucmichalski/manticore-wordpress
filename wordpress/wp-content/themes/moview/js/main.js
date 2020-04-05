/*global $:false */
jQuery(document).ready(function($){'use strict';

    // Sticky Nav
    $(window).on('scroll', function(){
        if ( $(window).scrollTop() > 0 ) {
            $('#masthead').addClass('sticky');
        } else {
            $('#masthead').removeClass('sticky');
        }
    });
	
	// Social Share ADD
	$('.prettySocial').prettySocial();

    /* -------------------------------------- */
    //      RTL Support Visual Composer
    /* -------------------------------------- */    
    var delay = 1;
    function themeum_rtl() {
        if( jQuery("html").attr("dir") == 'rtl' ){
            if( jQuery( ".entry-content > div" ).attr( "data-vc-full-width" ) =='true' )    {
                jQuery('.entry-content > div').css({'left':'auto','right':jQuery('.entry-content > div').css('left')}); 
            }
        }
    }
    setTimeout( themeum_rtl , delay);

    jQuery( window ).resize(function() {
        setTimeout( themeum_rtl , delay);
    }); 	


	/* -------------------------------------- */
	// 	Product Single Page Plus Minus Button
	/* -------------------------------------- */
	if ($('.single-product-details').length) {
		$('.single-product-details .quantity input[type="number"]').attr( 'type','text');
		var html = '<button type="button" class="btn-minus">-</button>'+$('.quantity').html()+'<button type="button" class="btn-plus">+</button>';
		$('.single-product-details .quantity').html(html);
	}
	
	$('.btn-minus').on('click', function(event) { 'use strict';
		var sp = $('.quantity input[name="quantity"]').val();
		if( sp != 1 ){
			$('.quantity input[name="quantity"]').attr( 'value', (parseInt(sp)-1) );
		}
	});
	$('.btn-plus').on('click', function(event) { 'use strict';
		var sp = $('.quantity input[name="quantity"]').val();
		$('.quantity input[name="quantity"]').attr( 'value', (parseInt(sp)+1) );
	});

   
      $("a[data-rel^='prettyPhoto']").prettyPhoto();




});