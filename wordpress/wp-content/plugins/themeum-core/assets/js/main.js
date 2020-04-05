/*
* Plugin Name: Themeum Core
* Plugin URI: http://www.themeum.com/item/core
* Author: Themeum
* Author URI: http://www.themeum.com
* License - GNU/GPL V2 or Later
* Description: Themeum Core is a required plugin for this theme.
* Version: 1.0
*/

jQuery(document).ready(function($){'use strict';


    // Featured Movie Carousel
    var owlrtl = false;
    if( jQuery("html").attr("dir") == 'rtl' ){
        owlrtl = true;
    }
    var delay = 1;
    setTimeout(function() {
         //Featured Movie
        var $spmvmovie = $('.movie-featured');
        $spmvmovie.owlCarousel({
            loop:true,
            dots:false,
            nav:false,
            rtl: owlrtl,
            autoplay:false,
            autoplayTimeout:3000,
            autoplayHoverPause:true,
            autoHeight: false,
            lazyLoad:true,
            responsive:{
                0:{
                    items:1
                },
                420:{
                    items:2
                },
                767:{
                    items:3
                },
                1050:{
                    items:4
                },
                1250:{
                    items:5
                }
            }
        });
        $('#moview-movie').css({opacity: 0, visibility: "visible"}).animate({opacity: 1.0}, 500);
    }, delay);
    

    //# prettySocial
    $('.prettySocial').prettySocial();

    //#Animated Number
    $('.themeum-counter-number').each(function(){
      var $this = $(this);
      $({ Counter: 0 }).animate({ Counter: $this.data('digit') }, {
        duration: $this.data('duration'),
        step: function () {
          $this.text(Math.ceil(this.Counter));
        }
      });
    });



    var $spotlightCommon = $(".spotlight");
    
    $spotlightCommon.owlCarousel({
        margin:30,
        nav:true,
        loop:true,
        rtl: owlrtl,
        dots:false,
        autoplay:false,
        autoplayTimeout:3000,
        autoplayHoverPause:true,
        autoHeight: false,
        responsive:{
            0:{
                items:1
            },
            420:{
                items:2
            },
            767:{
                items:3
            },
            1000:{
                items:4
            }
        }
    });


    /* -----------------------------------------
    ----------------- Video Popup -------------
    ----------------------------------------- */
    $('.play-video').on('click', function(event) {
        event.preventDefault();
        var $that     = $(this),
        type    = $that.data('type'),
        videoUrl  = $that.attr('href'), $video;

        if ( type === 'youtube' ) {
            $video = '<iframe id="video-player" src="https://www.youtube.com/embed/' + videoUrl + '?rel=0&amp;showinfo=0&amp;controls=1&amp;autoplay=1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        }
        else if ( type === 'vimeo' ) {
            $video = '<iframe id="video-player" src="//player.vimeo.com/video/' + videoUrl + '?autoplay=1&color=ffffff&title=0&byline=0&portrait=0&badge=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        } else if ( type === 'self' ) {
            $video = '<video id="video-player" controls autoplay> <source src="'+ videoUrl +'">Your browser does not support the video tag.</video>';
        } else if ( type === 'imdb' ) {
            $video = '<div class="embed-responsive embed-responsive-16by9"><iframe id="video-player" class="embed-responsive-item" src="http://m.imdb.com/video/imdb/'+ videoUrl +'/imdb/embed?autoplay=true&width=854" width="854" height="480" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" frameborder="no" scrolling="no"></iframe></div>';
        }
        $('.video-list').slideUp();
        $('#moview-player .video-container').find('#video-player').remove();
        $('#moview-player .video-container').prepend( $video );
        $('#moview-player .video-container').fadeIn();
    });


    $('.video-close').on('click', function(event) {
        event.preventDefault();
        $('.video-container').fadeOut(600, function(){
            $('#video-player').remove();
        });
    });

    $('.video-list-button').on('click', function(event) {
        event.preventDefault();
        $('.video-list').slideToggle();
    });


    /* -----------------------------------------
    ----------------- On Change Year -----------
    ----------------------------------------- */
    $('#sorting-by-years').on('change', function (e) {
        var valueSelected = this.value;
        window.location.href = valueSelected;
    });


    /* -----------------------------------------
    ----------------- Search On Change -----------
    ----------------------------------------- */
    $('#searchword').on('blur change paste keyup ', function (e) {

        var $that = $(this);
        var raw_data = $that.val(), // item container
            ajaxUrl = $that.data('url'),
            category = $("#searchtype").val();

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {raw_data: raw_data,category:category},
            beforeSend: function(){
                if ( !$('#moview-search .search-icon .moview-search-icons .fa-spinner').length ){
                    $('#moview-search .search-icon .moview-search-icons').addClass('spinner');
                    $('<i class="fa fa-spinner fa-spin"></i>').appendTo( "#moview-search .search-icon .moview-search-icons" ).fadeIn(100);
                   // $('#moview-search .search-icon .themeum-moviewsearch').remove();    
                }
            },
            complete:function(){
                $('#moview-search .search-icon .moview-search-icons .fa-spinner ').remove();    
                $('#moview-search .search-icon .moview-search-icons').removeClass('spinner');
            }
        })
        .done(function(data) {
            if(e.type == 'blur') {
               $( ".moview-search-results" ).html('');
            }else{
                $( ".moview-search-results" ).html(data);
            }
        })
        .fail(function() {
            console.log("error");
        });
    });
    $('select#searchtype').on('change', function (e) {
        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;
        $('#post-type-name').val( valueSelected );
    });


    $('.menu-toggler').on('click', function(event) {
        var rotate = 0;
        if( $(this).css("border-spacing") == '0px 0px' ){ rotate = 90 }
        $(this).animate({  borderSpacing: rotate }, {
            step: function(now,fx) {
              $(this).css('transform','rotate('+now+'deg)');  
            },
            duration:'fast'
        },'linear');
    });

});