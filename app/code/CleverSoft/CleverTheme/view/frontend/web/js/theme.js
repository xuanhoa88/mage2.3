/**
 * Copyright © 2017 CleverSoft., JSC. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'jQueryLibMin',
    'domReady!'
], function ($,keyboardHandler) {
    'use strict';
    function calcright($floatBar,$main,rightPos,$header) {
        var rightSize = 0;
        if($floatBar.length == 0 || $main.length == 0) return;
        rightSize = $main.offset().left + $main.innerWidth() + rightPos;
        $floatBar.css({
            right: rightSize,
            top: $header.outerHeight() +30,
            visibility: 'hidden'
        });
    }
    function calcleft($floatBar,$main,rightPos,$header) {
        var leftSize = 0;
        if($floatBar.length == 0 || $main.length == 0) return;
        leftSize = $main.offset().left + $main.innerWidth() + rightPos;
        $floatBar.css({
            left: leftSize,
            top: $header.outerHeight() +30,
            visibility: 'hidden'
        });
    }
    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }
    $('.cart-summary').mage('sticky', {
        container: '#zoo-main-content'
    });
    $('.panel.header > .header.links').clone().appendTo('#store\\.links');
    keyboardHandler.apply();
    //merge jsclevertheme.js
    window.shopSidebarWithoutLeftRightToggle = function() {
        var shopSidebarWithoutLeftRight = $( 'body' ).find( '#sidebar-without' ),
            shopSidebarWithoutLeftRightWidth = shopSidebarWithoutLeftRight.outerWidth();
        if ( $( '#sidebar-without' ).length ) {
            $('.layered-nav-toggle').on('click', function (e) {
                e.preventDefault();
                $('body').toggleClass('show-sidebar');
                if ($('body').hasClass('show-sidebar')) {
                    $(' #zoo-main-content .columns').css('overflow', 'initial');
                    $(' #zoo-main-content .columns').css('z-index', 'auto');
                    $(' #sidebar-without, #zoo-layer-navigation ').css('left', '0' );
                } else {
                    shopSidebarWithoutLeftRight.removeAttr('style');
                }
            });
            $('#sidebar-without .clever-icon-close').on('click', function (e) {
                $('body').removeClass('show-sidebar');
                shopSidebarWithoutLeftRight.removeAttr('style');
                $( '#zoo-layer-navigation' ).removeAttr('style');
            });
            $('#sidebar-without .transparent-bg').on('click', function (e) {
                $('body').removeClass('show-sidebar');
                shopSidebarWithoutLeftRight.removeAttr('style');
                $( '#zoo-layer-navigation' ).removeAttr('style');
            });
        }
    }
    window.shopSidebarRightToggle = function() {
        var shopSidebarRight = $( 'body' ).find( '#shop-sidebar-right' ),
            shopSidebarRightWidth = shopSidebarRight.outerWidth();
        if ( $( '#shop-sidebar-right' ).length ) {
            $('.layered-nav-toggle').on('click', function (e) {
                e.preventDefault();
                if($(window).width() < 992){
                    $('#zoo-layer-navigation').addClass('active').find('.filter').addClass('active');
                    $('body').toggleClass('filter-active');
                    $('body .transparent-bg').on('click',function(e){
                        if($(window).width() < 992){
                            $('body').removeClass('filter-active');
                        }

                    });
                }
            });
        }
    }
    window.shopSidebarLeftToggle = function() {
        var shopSidebarLeft = $( 'body' ).find( '#shop-sidebar-left' ),
            shopSidebarLeftWidth = shopSidebarLeft.outerWidth();
        if ( $( '#shop-sidebar-left' ).length ) {
            $('.layered-nav-toggle').on('click', function (e) {
                e.preventDefault();
                if($(window).width() < 992){
                    $('#zoo-layer-navigation').addClass('active').find('.filter').addClass('active');
                    $('body').toggleClass('filter-active');
                    $('body .transparent-bg').on('click',function(e){
                        if($(window).width() < 992){
                            $('body').removeClass('filter-active');
                        }

                    });
                }
            });
        }
    }
    function _floatBar() {
        var self = this;
        var $main = $("main .zoo-main-content-area");
        var $header = $("header");
        var $floatBar = $(".vertical-menu-one-page");
        var headerHeight = $header.outerHeight() + $header.offset().top;
        var rightPos = 30;
        if ( $('body').hasClass('rtl')) {
            calcleft($floatBar,$main,rightPos,$header);
        }else {
            calcright($floatBar,$main,rightPos,$header);
        }
        $(window).resize(function (event) {
            event.preventDefault();
            if ( $('body').hasClass('rtl')) {
                calcleft($floatBar,$main,rightPos,$header);
            }
            else {
                calcright($floatBar,$main,rightPos,$header);
            }
        });
        $(window).scroll(function (event) {
            event.preventDefault();
            var $win = $(window);
            var newHeight = 0;
            if($('.sticky-wrapper.is-sticky').length > 0 && $(".box-market").length > 0){
                newHeight = $('.sticky-wrapper.is-sticky').height();
                var curWinTop = $win.scrollTop() + newHeight;
                var boxmarket = $(".box-market").offset().top - $('.smooth-section').height();

                var hT = $(".box-market").offset().top,
                    hH = $(".box-market").outerHeight(),
                    wH = $(window).height(),
                    wS = $(this).scrollTop();

                $floatBar.css({
                    top: newHeight + 30
                })

                if (curWinTop > boxmarket) {
                    $floatBar.css({
                        visibility: 'visible'
                    })
                }
                else{
                    $floatBar.css({
                        visibility: 'hidden'
                    })
                }
                if (  wS > (hT+hH-wH)) {
                    $floatBar.css({
                        visibility: 'hidden'
                    })
                }
            }
        });
    }
    $(document).ready(function() {
        $(".tooltip-section").click(function () {
            $(".tooltip-section").removeClass("active");
            $(this).addClass("active");   
        });
        if($(".button").hasClass("global-element")) {
            $(".button").parent().addClass("button-global");
        }
    });
    $(function(){
        var ev = new $.Event('classadded'),
            orig = $.fn.addClass;
        $.fn.addClass = function() {
            $(this).trigger(ev, arguments);
            return orig.apply(this, arguments);
        };
        $.fn.equalboxes = function(){
            var maxheight = 0,
                rowheight = 0,
                rowstart = 0,
                height = 0,
                boxes = [],
                top = 0,
                jel = null;

            //all equalheight (item will not align like a mess)
            this.each(function(){
                jel = $(this);
                height = jel.css({'height': '', 'min-height': ''}).removeClass('eq-first').height();

                if(height > maxheight){
                    maxheight = height;
                }

                jel.data('orgHeight', height);

            }).css('min-height', maxheight);

            //per row equal-height
            this.each(function() {
                jel = $(this);
                height = jel.data('orgHeight');
                top = jel.position().top;

                if (rowstart != top) {
                    boxes.length && $(boxes).css('min-height', rowheight + 1).eq(0).addClass('eq-first');

                    // set the variables for the new row
                    boxes.length = 0;
                    rowstart = jel.position().top;
                    rowheight = height;
                    boxes.push(this);

                } else {
                    boxes.push(this);
                    if(height > rowheight){
                        rowheight = height;
                    }
                }
            });

            boxes.length && $(boxes).css('min-height', rowheight + 1).eq(0).addClass('eq-first');

            return this;
        };
        $.fn.eqboxs = function(){

            //should be more than two elements
            if(this.length < 2){
                return this;
            }

            var elms = this,
                rzid = null,
                resize = function () {
                    elms.equalboxes();
                };

            $(window).load(function(){
                //trigger one
                elms.equalboxes();

                clearTimeout(rzid);
                rzid = setTimeout(resize, 2000); //just in case something new loaded
            }).on('resize.eqb', function(){
                clearTimeout(rzid);
                rzid = setTimeout(resize, 200);
            });

            //trigger one
            elms.equalboxes();

            return this;
        };
        $('.btn-video-play').on('click', function(){
            if ($(this).find('em').hasClass('fa-play')) {
                $('#video_player_id').get(0).play();
                $('.video-overlay').hide();
                $(this).find('em').removeClass('fa-play').addClass('fa-pause');
                $('.video-content').addClass('video-running');
            } else {
                $('#video_player_id').get(0).pause();
                $('.video-overlay').show();
                $(this).find('em').removeClass('fa-pause').addClass('fa-play');
                $('.video-content').removeClass('video-running');
            }
        });
        // equal height function
        $('.equal-height').children().eqboxs();
        $('.header.links').clone().appendTo('#store\\.links');
        $("#scroll-to-top").hide();
        $("#scroll-to-top").click(function () {
            $("body, html").animate({scrollTop: 0}, 500);
            return false;
        });
        $(document).on('click', '.layered-nav-toggle', function(){
            $('body').toggleClass('layered-horizontal__show');
        });
        // search header-layout-2
        $('.header-minimal-search i').on('click', function() {
            $('.page-header .full-sc-search').toggleClass('active');
            setTimeout(function(){
                $('.page-header .full-sc-search .block-content #search').focus()
            }, 1000);
        })
        $('.header-minimal-search i').on('click', function(e) {
            $('body').toggleClass('search-active');
            e.stopPropagation();
        })
        $('.page-header .full-sc-search .clever-icon-close').on('click', function() {
            $('.page-header .full-sc-search').removeClass('active');
            $('body').removeClass('search-active');
        })
        $(document).keyup(function(e){
            if(e.keyCode=='27'){
                $('.page-header .full-sc-search').removeClass('active');
                $('body').removeClass('search-active');
                $('body').removeClass('mobile-sidebar__open');
            }
        })
        $(document).on("click", function(e) {
            var container = $(".page-header .full-sc-search");
            if (!container.is(e.target) && container.has(e.target).length === 0)
            {
                $('.page-header .full-sc-search').removeClass('active');
                $('body').removeClass('search-active');
            }
        });
        $('.page-header .full-sc-search .clever-icon-close').on('click', function() {
            $('.page-header .full-sc-search').removeClass('active');
        })
        $('.page-header .full-sc-search .block-content #search_mini_form .input-text').blur(function() {
            $('.page-header .full-sc-search .block-search .block-content .minisearch .field.search .control').removeClass("focus");
        })
        .focus(function() {
            $('.page-header .full-sc-search .block-search .block-content .minisearch .field.search .control').addClass("focus")
        });
        // Icon Menu
        $(".icon-menu").on('click',function(e){
            $("body").toggleClass('mobile-sidebar__open');
            e.stopPropagation();
        });
        $(".icon-menu__canvas").on('click',function(e){
            $("body").toggleClass('desktop-canvas__open');
            e.stopPropagation();
        });
        $(".desktop-canvas .clever-mega-menu-item .sub-menu-toggle").hide()
        $(".desktop-canvas .slicknav_arrow").on('click',function(e){
            $(this).toggleClass('active');
            $(this).next('.sub-menu-toggle').slideToggle();

        });
        $(".off-canvas-close").on('click',function(e){
            $("body").removeClass('desktop-canvas__open');
        });
        $(document).on("click", function(e) {
            var container = $("body .mobile-sidebar");
            if (!container.is(e.target) && container.has(e.target).length === 0)
            {
                $('body').removeClass('mobile-sidebar__open');
            }
        });
        $(".sections__canvas--close").on('click',function(e){
            $('body').removeClass('mobile-sidebar__open');
            e.stopPropagation();
        });
        window.is_sticky = function(){
            $(window).scroll(function(e){

                if($(window).scrollTop() > 0){
                    $("#scroll-to-top").fadeIn('slow');
                }else{
                    if($("#scroll-to-top").is(':visible')){
                        $("#scroll-to-top").fadeOut('slow');
                    }
                }
                if ($('.minicart-wrapper').hasClass('active')){
                    $('.nav-title-cart').css('z-index','4');
                }
                else{
                    $('.nav-title-cart').css('z-index','48');
                }
            });
        };
        is_sticky();

        //Menu One Page Home 09
        if($('.vertical-menu-one-page').length > 0 ) {
            $('.vertical-menu-one-page').onePageNav({
                currentClass: 'active',
                changeHash: false,
                scrollSpeed: 750
            });
        }
        var heightimg = $( window ).height();

        // jQuery show/hide MY CART when Add to cart(11/08/2016)
        $(".minicart-wrapper").append( "<div class='minicart-wrapper-main'></div>" );
        $('.minicart-wrapper .minicart-wrapper-main').click(function(){
            $(this).parent().removeClass('active');
            $('.action.showcart').removeClass('active');
            $('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.mage-dropdown-dialog').hide();
        });
        $('#mini-cart .action.delete').click(function(){
            $('.action-primary.action-accept').click();
        });
        $('.page-footer .copyright span').wrap('<div class="container"></div>');
        $(window).resize(function(){

            var windowWidth = $(window).width();
            if(windowWidth <= 992){
                if($('.layered-nav-toggle').hasClass('active')) $('.layered-nav-toggle').removeClass('active');
                if($('#zoo-layer-navigation .zoo-sidebar-additional').length != 1) {
                    $('.zoo-sidebar-additional').appendTo('#zoo-layer-navigation .block-content .filter-options');
                }
            }else{
                $('.zoo-sidebar-additional').appendTo('.wrap-additional');
            }
        });
    });

    $(window).load(function(){
        if($(window).width() <= 767){
            // cookielaw
            $("#v-cookielaw").addClass("zoo-accordion-show");
            $("#v-cookielaw h3").on('click',function(){
                if($(this).parent().find('.v-message').is(":visible")){
                    $(this).parent().addClass("zoo-accordion-show");
                }else{
                    $(this).parent().removeClass("zoo-accordion-show");
                }
                $(this).parent().find('.v-message').toggle(400);
            });


            //product detail tab
            $('.product.items .title').click(function(){
                if ($(this).hasClass('active')) {
                    $(this).siblings('.content').filter(":visible").fadeOut( "slow" );
                    $('.product.items .title').removeClass("active");
                } else {
                    $('.product.items .title.active').siblings('.content').filter(":visible").fadeOut( "slow" );
                    $('.product.items .title').removeClass("active");
                    $(this).addClass('active').siblings('.content').fadeToggle( "slow");
                }
            });
        }
        if($(window).width() > 1200 && $('.color-section .box-product-grid').length > 0){
            // equal height home 09 market
            $('.color-section').each(function(){
                var tab_style2 = $(this).find('.box-product-grid').outerHeight();
                $(this).find('.slide-home01 img').height(tab_style2 )
            })
        }
        $(".zoo-accordion-footer").addClass("zoo-accordion-show");
        $(".zoo-accordion-footer").on('click',function(){
            if($(this).parent().find('.zoo-footer-bottom-content').is(":visible")){
                $(this).addClass("zoo-accordion-show");
            }else{
                $(this).removeClass("zoo-accordion-show");
            }
            $(this).parent().find('.zoo-footer-bottom-content').toggle(400);
        });
        // search dropdown
        $('.search-dropdown').click(function(event) {
            event.stopPropagation();
            $(this).children('.search-option-list').slideToggle();
            $('.search-option-list span').click(function(){
                var select = $(this).text();
                var vs = $(this).parent("li").val();
                $('.search-option-list li').removeClass('selected');
                $(this).parent("li").addClass('selected');
                $(this).closest('.search-dropdown').children('.search-select').text(select);
                $('#cat-search').val(vs);
            });
        });
        // add title product sticky in product detail
        var title_product = jQuery('.catalog-product-view .page-title-wrapper.product').html();
        jQuery('.box-price').prepend(title_product);

        $(document).click(function(){
            $('.search-dropdown .search-option-list').slideUp();
        });
        //vertical menu
        var totalHeight = 0;
        $(".zoo-main-content-area .clever-vertical-menu > ul").children().each(function(){
            totalHeight = totalHeight + $(this).outerHeight(true);
            $(this).children('.clever-mega-menu-sub').css('top', -totalHeight);
        });

        $(document).ready(function() {
            // promotion box
            jQuery.ajaxSetup({'cache':true});
            $(document).on("click", ".notification-dismiss", function(a) {
                var b = $(this).parent();
                return b.slideUp();
            })
        });

        // Enable Addtocart Sticky
        if(window.enable_sticky_addtocart == 1){
            $( document ).ready(function() {
                function addStickyAddToCart(){
                    $(window).scroll(function (event) {
                        var scrollTop = $(window).scrollTop();
                        if ($(".product-info-main .box-tocart").length) {
                            var topDistance = $('.product-info-main .box-tocart').offset().top;
                            if ( topDistance < scrollTop ) {
                                $(".nav-title-cart").addClass("active");
                                $("body").addClass("nav-title-cart__active");

                            } else {
                                $(".nav-title-cart").removeClass("active");
                                $("body").removeClass("nav-title-cart__active");
                            }
                        }
                        scrollTop = topDistance;
                    });
                }
                setTimeout(function(){
                    addStickyAddToCart();
                }, 1000);

            });
        }
        // Enable sticky menu
        if(window.enable_sticky_menu == 1){
            $(document).ready(function(){
                if($(window).width() >= 992){
                    var to_top=0,to_top_mobile=0;
                    $('.page-wrapper__header .sticker').each(function () {
                    var $this = $(this);
                    var this_to_top =$this.closest('.page-header .page-wrapper__header')[0]?to_top:to_top_mobile;
                    $this.data('to-top', this_to_top);
                    $this.sticky({zIndex: '10', topSpacing: this_to_top});
                    if (!!$this.data('sticky-height')) {
                        this_to_top += $this.data('sticky-height');
                    } else {
                        this_to_top += $this.height();
                    }
                    !!$this.closest('.page-header .page-wrapper__header')[0]?to_top=this_to_top:to_top_mobile=this_to_top;
                    });
                }
                else{
                    $("#header-sticky-mobile").sticky({ topSpacing: 0, getWidthFrom: ''});
                    $(".zoo-header-3 #zoo-sticky-header").sticky({ topSpacing: 0, getWidthFrom: ''});
                }
            });
        }

        $(document).ready(function() {
            var data_fullpage = $(".panel-layout .panel-grid").attr("data-fullpage");
            if(data_fullpage == "1") {
                $(".panel-layout .panel-grid").addClass("full-page");
            }
            // numberOfItems = $('.full-page .fp-section').length;
            $('.full-page').fullpage({
                verticalCentered: false,
                css3: true,
                navigation: false,
                navigationPosition: 'right',
                autoScrolling:  false ,
                scrollBar: false,
                lazyLoading: true,
                fitToSection: false
            });

            // Enable sticky info product
            if($(window).width() > 991){


                if ($('.product-info-main-sticky')[0]) {
                    $('.product-info-main-sticky').theiaStickySidebar({
                        additionalMarginTop: 100
                    });
                }
            }
            // Enable sticky Thumb Gallery
            if($('.gallery-sticky2-image-thumb-col').length > 0){
                $('.gallery-sticky2-image-thumb-col').theiaStickySidebar({
                    additionalMarginTop: 100
                });
            }
            (function(a) {
                'use strict';
                var b = function(a, b) {
                    return this.init(a, b)
                };
            }).apply(this, [jQuery]),
                function(a) {
                    var b = {
                        preloader: function() {
                            'use strict';

                            function c() {
                                if (a('body').addClass('page-loaded').addClass('preloader-animation--started'), e.length) {
                                    var c = b.debounce(function() {
                                        a(window).triggerHandler('resize'), a('body').removeClass('preloader-animation--not-started')
                                    }, 250);
                                    d.on('webkitAnimationEnd oanimationend msAnimationEnd animationend', function() {
                                        c()
                                    })
                                }
                                f.length && setTimeout(function() {
                                    d.fadeOut()
                                }, h), g.length && d.find('.curtain-back').on('webkitAnimationEnd oanimationend msAnimationEnd animationend', function() {
                                    d.fadeOut(), a('body').addClass('preloader-animation--done').removeClass('preloader-animation--started')
                                })
                            }
                            var d = a('.page-loader'),
                                e = a('.page-loader-style1'),
                                f = a('.page-loader-style2'),
                                g = a('.page-loader-style3'),
                                h = (d.children('.page-loader-inner'), parseInt(d.data('fade-delay'), 10) || 0),
                                i = a('.titlebar');
                            i.length ? i.imagesLoaded({
                                background: !0
                            }, function() {
                                setTimeout(function() {
                                    c()
                                }, 80)
                            }) : c(), window.onbeforeunload = function() {
                                a('body').addClass('preloader-animation--started page-unloading').removeClass('preloader-animation--not-started preloader-animation--done page-loaded')
                            }
                        }
                    };
                    a(document).ready(function() {
                        b.preloader()
                    });
                }(jQuery);
        });
        _floatBar();
        shopSidebarWithoutLeftRightToggle();
        shopSidebarRightToggle();
        shopSidebarLeftToggle();
    }(jQuery));
});