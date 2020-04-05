(function($)
{	
    "use strict";
	
    $(window).bind("pageshow", function(event) {
	    if (event.originalEvent.persisted) {
	     avia_site_preloader();
	    }
	});
	
	
	
	
    $(document).ready(function()
    {	
	
		  //return top
		  function gotoTop(min_height){
		  
			$("#gotoTop").click(
				function(){$('html,body').animate({scrollTop:0},700);
			}).hover(
				function(){$(this).addClass("hover");},
				function(){$(this).removeClass("hover");
			});
		  
			min_height ? min_height = min_height : min_height = 600;
		  
			$(window).scroll(function(){
		  
				var s = $(window).scrollTop();
		  
				if( s > min_height){
					$("#gotoTop").fadeIn(100);
				}else{
					$("#gotoTop").fadeOut(200);
				};
			});
		  };
		  gotoTop(); 	
	
	
	    
        theta_responsive_menu();
		theta_adjust_menu_position();
		
		$(window).resize(function() {
			theta_adjust_menu_position();
			
			var browser_width = document.body.clientWidth;

			if( browser_width>1024 ){
				$("#theta-menu ul.menu-mobile").css("display","none");
				$(".theta-menu .menu-icon .icon-close").css("display","none");
				$(".theta-menu .menu-icon .icon-menu").css("display","block");
				
				$(".menu-icon").css('display', 'none');
				
			}else{
				if( $("#theta-top-search").css("display") == "none" ){
					$(".menu-icon").css('display', 'block');	
				}
				
			}
				
		});
	
		var vheight = $(window).height();
		$("#theta-menu ul.menu-mobile").css('height',vheight);
		
		$(window).scroll(function () {
			var scroll_y = $(window).scrollTop();
			
			if (scroll_y > 50) {
				$("header.fixed").addClass('changeh');
				$("header.changeh  .header-wrap .theta-menu ul.menu li").css('height', 50);
				$("header.changeh  .header-wrap .theta-menu ul.menu li ul li").css('height','auto');

				
				$("#theta-top-search").css('paddingTop', 20);
			} else {
				$("header.changeh  .header-wrap .theta-menu ul.menu li").css('height', 74);
				$("header.changeh  .header-wrap .theta-menu ul.menu li ul li").css('height', 'auto');
				$("header.fixed").removeClass('changeh');
			}		
		});		
		
    });


    // -------------------------------------------------------------------------------------------
	// responsive menu function
	// -------------------------------------------------------------------------------------------

    function theta_responsive_menu()
    {
		
		
		$('#theta-menu ul.menu span').clone().attr('id','theta-mobile-menu').removeClass().attr('class','theta-mobile-menu').appendTo( $("#theta-menu ul.menu-mobile") );
		
		 var bigContainer = document.querySelectorAll("#theta-mobile-menu>li");  
            for(var i=bigContainer.length-1;i>-1;i--){  
                document.querySelector("#theta-mobile-menu").appendChild(bigContainer[i]);  
            } 
					
		$(".menu-icon").click(function(){
			$(".theta-menu .menu-icon .icon-close").toggle();
			$(".theta-menu .menu-icon .icon-menu").toggle();
			$("#theta-menu ul.menu-mobile").toggle(500);
  		});
		
		
		//When you click the link on the move menu, the pop-up move menu disappears, and the close icon reverts to a menu icon
		$("#theta-menu ul.menu-mobile").click(function(){
			$(".theta-menu .menu-icon .icon-close").toggle();
			$(".theta-menu .menu-icon .icon-menu").toggle();
			$("#theta-menu ul.menu-mobile").slideUp("slow");;
  		});				
		

		$(".icon-search").click(function(){
			$(".menu-icon").css('display', 'none');
			
			$("#theta-menu ul.menu").css('display', 'none');

			$("#theta-top-search").slideDown("slow");
			
			var browser_width = document.body.clientWidth;				
			if( browser_width>1024 ){
			
				$(".menu-icon").css('display', 'none');
			}
			
  		});	


		
		$(".theta-close-search-field").click(function(){
			
			$(".menu-icon").css('display', 'block');

			$("#theta-menu ul.menu").css('display', 'block');
			$("#theta-top-search").css('display', 'none');
			
			var browser_width = document.body.clientWidth;				
			if( browser_width>1024 ){
			
				$(".menu-icon").css('display', 'none');
			}

  		});
		

    }

    function theta_adjust_menu_position()
    {
		var width = $('.header-wrap').width(),
			logo_width = $('.theta-logo').width(),
			
			header_height = $('.header').height(),
			
			browser_width = document.body.clientWidth

			;

		$("#theta-top-search").css('width', width-logo_width-20);

		$("#theta-top-search").css('paddingTop', (header_height-25)/2 );
		$("#theta-top-search .theta-search-form input").css('max-width', width-logo_width-60 );
		
		$(".theta-menu ul.menu li.menu-item-has-children ul.sub-menu").has(".menu-item-has-children").css('width', width);
		$(".theta-menu ul.menu li.menu-item-has-children ul.sub-menu").has(".menu-item-has-children").css('right', (( browser_width - width) /2) );
		$(".theta-menu ul.menu li.menu-item-has-children ul.sub-menu").has(".menu-item-has-children").css('top', ( header_height-30));
		$(".theta-menu ul.menu li.menu-item-has-children ul.sub-menu").has(".menu-item-has-children").addClass("sub-menu-width");
		
		var num = $('.theta-menu ul.menu li.menu-item-has-children ul.sub-menu-width').children('li').length;
		if(num == 3 ){ $("header .header-wrap .theta-menu ul.menu li ul.sub-menu-width li").css('width', '33%');   }
		if(num == 2 ){ $("header .header-wrap .theta-menu ul.menu li ul.sub-menu-width li").css('width', '50%');   }
		$("header .header-wrap .theta-menu ul.menu li ul.sub-menu li ul.sub-menu li").css('width', '100%');
		//alert(num);

		$("header .header-wrap .theta-menu ul.menu li ul li").parent(".sub-menu-width").find("li").css("background-color", "transparent");		

		//
		
		$("header .header-wrap .theta-menu ul.menu li").css('height', header_height);
		$("header .header-wrap .theta-menu ul.menu li ul li").css('height','auto');
	
		
    }

	// -------------------------------------------------------------------------------------------
	// Smooth scrooling when clicking on anchor links
	// todo: maybe use https://github.com/ryanburnette/scrollToBySpeed/blob/master/src/scrolltobyspeed.jquery.js in the future
	// -------------------------------------------------------------------------------------------

	(function($)
	{
		$.fn.avia_smoothscroll = function(apply_to_container)
		{
			if(!this.length) return;
				
			var the_win = $(window),
				$header = $('#header'),
				$main 	= $('.html_header_top.html_header_sticky #main').not('.page-template-template-blank-php #main'),
				$meta 	= $('.html_header_top.html_header_unstick_top_disabled #header_meta'),
				$alt  	= $('.html_header_top:not(.html_top_nav_header) #header_main_alternate'),
				shrink	= $('.html_header_top.html_header_shrinking').length,
				frame	= $('.av-frame-top'),
				fixedMainPadding = 0,
				isMobile = $.avia_utilities.isMobile,
				sticky_sub = $('.sticky_placeholder:first'), 
				calc_main_padding= function()
				{
					if($header.css('position') == "fixed")
					{
						var tempPadding  		= parseInt($main.data('scroll-offset'),10) || 0,
							non_shrinking		= parseInt($meta.outerHeight(),10) || 0,
							non_shrinking2		= parseInt($alt.outerHeight(),10) || 0; 
						
						if(tempPadding > 0 && shrink) 
						{
							tempPadding = (tempPadding / 2 ) + non_shrinking + non_shrinking2;
						}
						else
						{
							tempPadding = tempPadding + non_shrinking + non_shrinking2;
						}
						
						tempPadding += parseInt($('html').css('margin-top'),10);
						fixedMainPadding = tempPadding; 
					}
					else
					{
						fixedMainPadding = parseInt($('html').css('margin-top'),10);
					}
					
					if(frame.length){
						fixedMainPadding += frame.height();
					}
					
				};
			
			if(isMobile) shrink = false;
			
			calc_main_padding();
			the_win.on("debouncedresize av-height-change",  calc_main_padding);

			var hash = window.location.hash.replace(/\//g, "");
			
			//if a scroll event occurs at pageload and an anchor is set and a coresponding element exists apply the offset to the event
			if (fixedMainPadding > 0 && hash && apply_to_container == 'body' && hash.charAt(1) != "!" && hash.indexOf("=") === -1)
			{
				var scroll_to_el = $(hash), modifier = 0;
				
				if(scroll_to_el.length)
				{
					the_win.on('scroll.avia_first_scroll', function()
					{	
						setTimeout(function(){ //small delay so other scripts can perform necessary resizing
							if(sticky_sub.length && scroll_to_el.offset().top > sticky_sub.offset().top) { modifier = sticky_sub.outerHeight() - 3; }
							the_win.off('scroll.avia_first_scroll').scrollTop( scroll_to_el.offset().top - fixedMainPadding - modifier);
						},10); 
				    });
			    }
			}
			
			return this.each(function()
			{
				$(this).click(function(e) {

				   var newHash  = this.hash.replace(/\//g, ""),
				   	   clicked  = $(this),
				   	   data		= clicked.data();
					
				   if(newHash != '' && newHash != '#' && newHash != '#prev' && newHash != '#next' && !clicked.is('.comment-reply-link, #cancel-comment-reply-link, .no-scroll'))
				   {
					   var container = "", originHash = "";
					   
					   if("#next-section" == newHash)
					   {
					   		originHash  = newHash;
					   		container   = clicked.parents('.container_wrap:eq(0)').nextAll('.container_wrap:eq(0)');
					   		newHash		= '#' + container.attr('id') ;
					   }
					   else
					   {
					   		container = $(this.hash.replace(/\//g, ""));
					   }
					   
					   

						if(container.length)
						{
							var cur_offset = the_win.scrollTop(),
								container_offset = container.offset().top,
								target =  container_offset - fixedMainPadding,
								hash = window.location.hash,
								hash = hash.replace(/\//g, ""),
								oldLocation=window.location.href.replace(hash, ''),
								newLocation=this,
								duration= data.duration || 1200,
								easing= data.easing || 'easeInOutQuint';
							
							if(sticky_sub.length && container_offset > sticky_sub.offset().top) { target -= sticky_sub.outerHeight() - 3;}
							
							// make sure it's the same location
							if(oldLocation+newHash==newLocation || originHash)
							{
								if(cur_offset != target) // if current pos and target are the same dont scroll
								{
									if(!(cur_offset == 0 && target <= 0 )) // if we are at the top dont try to scroll to top or above
									{
										// animate to target and set the hash to the window.location after the animation
										$('html:not(:animated),body:not(:animated)').animate({ scrollTop: target }, duration, easing, function() {
										
											// add new hash to the browser location
											//window.location.href=newLocation;
											if(window.history.replaceState)
											window.history.replaceState("", "", newHash);
										});
									}
								}
								// cancel default click action
								e.preventDefault();
							}
						}
					}
				});
			});
		};
	})(jQuery);

})( jQuery );


