/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

define([
    'jquery',
    'jquery/ui',
    'domReady',
    'jQueryLibMin',
    'CleverSoft_CleverBuilder/js/product/jquery.jcountdown.min',
    'CleverSoft_CleverBuilder/js/product/jquery.bxslider'
], function($, ui,domReady) {
    'use strict';

    $.widget('mage.cleverProductMage', {
        options: {
            VIDEO_MUTE_BTN_CLASS: 'videoMuteBtn fa',
            VIDEO_MUTE_BTN_CLASS_ON: 'fa-volume-up',
            VIDEO_MUTE_BTN_CLASS_OFF: 'fa-volume-off',
            VIDEO_HW_RATIO: 0.5625,
            VENDOR_PREFIXES: 'webkit|Moz'.split('|'),
            animation: '',
            parallax: null,
            carousel: null,
            carouselv: null,
            collectionUrl: null,
            carouselConfig: {},
            countdown: null,
            countdownConfig: {  dayText: '',
                hourText: '',
                minText: '',
                secText: '',
                daySingularText: '',
                hourSingularText: '',
                minSingularText: '',
                secSingularText: ''
            },
            FORM_KEY:null,
            countdownTemplate: ''
        },

        _create: function () {
            var def = $.Deferred();
            var seft = this;
            seft.id = seft.element.attr('id');
            seft.container = seft.element;
            seft.checkCSS3Support();
            seft.initTab();
            seft.initCountdown();

            if (seft.options.carousel && seft.options.carousel.enable) {
                seft.initCarousel(function (el) {
                    seft.initCarouselAnimation(el, true);
                    seft.initBackground();
                });
            } else {
                seft.initAnimation();
                seft.initBackground();
            }
            if (seft.options.carouselv && seft.options.carouselv.enable) {
                seft.initCarouselVertical('#'+seft.id);
            }
            return seft.options.lazyload ? def.promise() : def.done( seft.doEqualHeightTrigger(seft.options.equalHeight, '#'+seft.id) ).promise();
        },

        initCarouselVertical: function(id) {
            var seft = this;
            $(id+" .product-item-info img.img-lazyload").css('height',seft.options.carouselv.imgWidth);
            $(id+" .top-rated.home05 .block.widget.zoo-block-products-list.grid .block-content").css("overflow", "visible");
            require(["CleverSoft_CleverBuilder/js/product/jquery.bxslider"], (function () {
                    var slider = $(id+' ol.vertical-carousel').bxSlider({
                        mode: seft.options.carouselv.mode,
                        minSlides: seft.options.carouselv.minSlides,
                        maxSlides: seft.options.carouselv.maxSlides,
                        auto: seft.options.carouselv.auto,
                        pause: seft.options.carouselv.pause,
                        startSlide: seft.options.carouselv.startSlide,
                        autoHover: seft.options.carouselv.autoHover,
                        nextText: seft.options.carouselv.nextText,
                        prevText: seft.options.carouselv.prevText,
                        pager: seft.options.carouselv.pager
                    });
                })
            );
        },

        initCountdown: function () {
            var seft = this;
            if (!seft.options.countdown) return;
            if (!seft.options.countdown.enable) return;
            seft.bindCountdownEvent();
        },

        bindCountdownEvent: function () {
            var seft = this;
            seft.container.find('.product-date').each(function () {
                var item = $(this);
                var date = item.attr('data-date');
                if (date) {
                    var config = {date: date};
                    $.extend(config, seft.options.countdown);
                    $.extend(config, seft.options.countdownConfig);
                    if (seft.options.countdownTemplate) {
                        config.template = seft.options.countdownTemplate;
                    }
                    $(item).countdown(config);
                }
            });
        },

        initAnimation: function () {
            var seft = this;
            if (!seft.options.animation) return;
            if (!seft.options.animation.enable) return;

            var animClass = seft.options.animation.animationName,
                animDelay = seft.options.animation.animationDelay;

            seft.container.find(seft.options.animation.itemSelector || '.item').each(function (i) {
                var item = $(this);
                item.addClass(animClass + ' wow');
                item.css({visibility: 'hidden'});
                item.attr('data-wow-delay', (animDelay * i) + 'ms');
            });

            if (!window['WOW']) {
                $.getScript(seft.options.animation.engineSrc, function () {
                    seft.bindWOWEvent();
                });
            } else {
                seft.bindWOWEvent();
            }
        },

        bindWOWEvent: function () {
            new WOW({
                'animateClass': 'active'
            }).init();
        },

        initCarouselAnimation: function (el, isInit) {
            if (!this.options.animation) return;
            if (!this.options.animation.enable) return;

            var animClass = this.options.animation.animationName,
                visibleItems = [];

            el.find('.owl-item').each(function (i) {
                var $item = $(this);
                $item.addClass(animClass);

                if ($item.hasClass('active')) {
                    $item.removeClass('active');
                    $item.css({visibility: 'hidden'});
                    visibleItems.push($item);
                }
            });

            if (isInit) this.bindAnimateEvent(visibleItems);
            else this.animateElements(visibleItems);
        },

        bindAnimateEvent: function (visibleItems) {
            var seft = this;
            $.each('DOMContentLoaded|load|resize|scroll'.split('|'),function (event,val) {
                $(window).on(val, function () {
                    if (!seft.animated && seft.isElementInViewport(1 / 2)) {
                        seft.animated = true;
                        seft.animateElements(visibleItems);
                    }
                });
            });
        },

        animateElements: function (items) {
            var seft = this;
            var animDelay = seft.options.animation.animationDelay || 300;

            items.each(function (i) {
                var item = $(this);
                var value = i == 0 ? 0 : animDelay * i;
                seft.setVendorCss(item, 'animationDelay', value + 'ms');
                item.addClass('active');
                item.css({visibility: 'visible'});
            });

            setTimeout(function () {
                items.each(function () {
                    seft.setVendorCss($(this), 'animationDelay', 'initial');
                });
            }, (items.length + 1) * animDelay);
        },

        setVendorCss: function (el, property, value) {
            var cssObj = {property: value};

            this.VENDOR_PREFIXES.each(function (i,prefix) {
                cssObj[prefix + property.charAt(0).toUpperCase() + property.substr(1)] = value;
            });

            el.css(cssObj);
        },

        checkCSS3Support: function () {
            var translate3D = 'translate3d(0px, 0px, 0px)',
                divElm = document.createElement('div'),
                matches;

            divElm.style.cssText =
                '-moz-transform:' + translate3D +
                ';-ms-transform:' + translate3D +
                ';-o-transform:' + translate3D +
                ';-webkit-transform:' + translate3D +
                ';transform:' + translate3D;

            matches = divElm.style.cssText.match(/translate3d\(0px, 0px, 0px\)/g);

            this.support3d = matches !== null && matches.length >= 1;
        },

        initTab: function(){
            var seft = this;
            if (!seft.options.collectionUrl) return;

            seft.container.find('.widget-tabs a').each(function(){
                var tab = $(this);
                var tab_content = seft.container.find(tab.attr('href')).first();
                if (!tab_content) return;

                if (tab_content.find('ol').length > 0 && tab_content.find('ol').first()){
                    tab_content.has_content = true;
                }

                $(tab).on('click', function(e){
                    e.stopPropagation();
                    seft.activeTab($(tab), tab_content);
                    if (tab_content.has_content) {
                        if (typeof window.gridLayout !== 'undefined') {
                            window.gridLayout($(this).attr('data-id'));
                            return;
                        }

                        if (typeof window.gridLayout2 !== 'undefined') {
                            window.gridLayout2($(this).attr('data-id'));
                            return;
                        }

                    }

                    var type = $(this).attr('data-type'),
                        layout = $(this).attr('data-layout'),
                        value = $(this).attr('data-value'),
                        limit = $(this).attr('data-limit'),
                        id = $(this).attr('data-id'),
                        divId = $(this).attr('href'),
                        column_ajax = $(this).attr('data-column_ajax'),
                        row = $(this).attr('data-row'),
                        cpid = $(this).attr('data-cpid'),
                        cid = $(this).attr('data-cid'),
                        template = $(this).attr('data-template'),
                        image_width = $(this).attr('data-image_width'),
                        image_height = $(this).attr('data-image_height'),
                        lazyload = $(this).attr('data-lazyload'),
                        height_image = $(this).attr('data-height_image'),
                        product_grid_style = $(this).attr('data-product_grid_style'),
                        countdown = $(this).attr('data-countdown'),
                        enable_countdown = $(this).attr('data-enable_countdown'),
                        countdown_label = $(this).attr('data-countdown_label'),
                        countdown_position = $(this).attr('data-countdown_position'),
                        enable_progress_bar = $(this).attr('data-enable_progress_bar'),
                        carousel = $(this).attr('data-carousel'),
                        carouselv = $(this).attr('data-carouselv'),
                        display_price = $(this).attr('data-display_price'),
                        display_name_single_line = $(this).attr('data-display_name_single_line'),
                        display_productname = $(this).attr('data-display_productname'),
                        display_swatch_attributes = $(this).attr('data-display_swatch_attributes'),
                        display_addtocompare = $(this).attr('data-display_addtocompare'),
                        display_addtowishlist = $(this).attr('data-display_addtowishlist'),
                        display_addtocart = $(this).attr('data-display_addtocart'),
                        display_rating = $(this).attr('data-display_rating'),
                        form_key = seft.FORM_KEY || null;

                    $.ajax({
                        url : seft.options.collectionUrl,
                        method: 'post',
                        data: {
                            type: type,
                            layout: layout,
                            value: value,
                            limit: limit,
                            carousel: carousel,
                            carouselv: carouselv,
                            column_ajax: column_ajax,
                            id:id,
                            form_key: form_key,
                            cpid: cpid,
                            display_rating: display_rating,
                            display_addtocart: display_addtocart,
                            display_addtowishlist: display_addtowishlist,
                            display_addtocompare: display_addtocompare,
                            display_swatch_attributes: display_swatch_attributes,
                            display_productname: display_productname,
                            product_grid_style: product_grid_style,
                            display_name_single_line: display_name_single_line,
                            display_price: display_price,
                            cid: cid,
                            row: row,
                            image_width: image_width,
                            height_image: height_image,
                            countdown: countdown,
                            enable_countdown: enable_countdown,
                            countdown_label : countdown_label,
                            countdown_position : countdown_position,
                            enable_progress_bar : enable_progress_bar,
                            image_height: image_height,
                            lazyload: lazyload,
                            template: template
                        },
                        success: function(transport){
                            tab_content.has_content = true;
                            tab_content.html(transport);
                            $(".zoo-tooltip").tooltip();
                            if (typeof window.gridLayout !== 'undefined') {
                                window.gridLayout(id);
                            }
                            if (typeof window.gridLayout2 !== 'undefined') {
                                window.gridLayout2(id);
                            }
                            seft.initCarousel(function(el){
                                seft.initCarouselAnimation(el, false);
                                tab_content.css({
                                    height: 'auto'
                                });
                            });

                            seft.initCountdown();

                            var imag = $(tab_content).find('.clazyload');

                            if(imag.length > 0) {
                                imag.lazyload({
                                    data_attribute: "src",
                                    failure_limit : 10,
                                    load: seft.doEqualHeightTrigger(seft.options.equalHeight,divId)
                                });

                            }

                            if(window.quickViewModal())window.quickViewModal();

                            if (seft.options.collectionCallback){
                                seft.options.collectionCallback();
                            }

                            if (seft.options.carouselv && seft.options.carouselv.enable) {
                                seft.initCarouselVertical(divId);
                            }
                        }
                    });
                });
            });
        },

        activeTab: function(tab, content){
            var seft = this;
            if (!tab || !content) return;

            seft.container.find('.widget-tabs .active').removeClass('active');
            tab.parent().addClass('active');

            if (!content.has_content){
                var prev = seft.container.find('.tab-pane.active').first();
                if (prev){
                    content.css({
                        height: prev.height() + 'px'
                    });

                    prev.removeClass('active');

                    var spinner = $('<div/>', {'class': 'widget-spinner'});
                    spinner.css({width: '100%', height: '100%'});

                    if (this.options.spinnerClass){
                        spinner.addClass(seft.options.spinnerClass);
                    }

                    if (this.options.spinnerImg){
                        $(content).html(seft.options.spinnerImg);
                    }
                }
            }else{
                seft.container.find('.tab-pane.active').removeClass('active');
            }
            content.addClass('active');
        },

        initCarousel: function (callback) {
            var seft = this;
            if (!seft.container) return;
            if (!seft.options.carousel) return;
            if (!seft.options.carousel.enable) return;

            if (callback) {
                seft.options.carousel.onInitialized = callback;
            }
            seft.initCarouselElement(seft.options.carousel);
        },

        initCarouselElement: function (config) {
            var lazy = this;
            if (lazy.options.carouselConfig) {
                config = $.extend(config, lazy.options.carouselConfig);
            }

            this.container.find('.owl-carousel').each(function () {
                var div = $(this);
                $(div).owlCarousel(config);

                if(config.autoPlay){
                    $(div).trigger('play.owl.autoplay',(config.autoplayTimeout ? config.autoplayTimeout : (parseInt(config.autoPlay) > 0 ? config.autoPlay : 1000)));
                }
                if(lazy.options.lazyload) {
                    lazy.doLazyloadProduct(div);
                }

            });
        },

        doEqualHeightTrigger: function(enable,parent){
            if(enable) {
                $(parent).find('img').waitForImages({
                    finished: function() {
                        // ...
                    },
                    each: function() {
                        // ...
                    },
                    waitForAll: true
                }).done(function() {
                    $(this).closest(parent).attr('data-mage-init', JSON.stringify({'equalHeight': {'target': ' .product-item-info'}}));
                    $(this).closest(parent).trigger('contentUpdated');
                })
            }
        },

        doLazyloadProduct: function(div) {
            var self = this;
            $(div).on('changed.owl.carousel', function(event) {
                setTimeout(function(){
                    $(div).find(".owl-item.active .clazyload").lazyload({
                        effect: "fadeIn",
                        data_attribute: "src",
                        failure_limit : 10,
                        load: self.doEqualHeightTrigger(self.options.equalHeight,div)
                    });
                }, 100);
            });
        },

        initBackground: function () {
            if (!this.options.parallax && !this.options.kenburns) return;

            if (this.options.parallax && this.options.parallax.enable) this.initParallax();
            if (this.options.kenburns && this.options.kenburns.enable) this.initKenburns();
        },

        initKenburns: function () {
            var seft = this;
            if (!$.fn.Kenburns) {
                $.getScript(this.options.kenburns.engineSrc, function () {
                    seft.initKenburnsElement(seft.options.kenburns);
                });
            } else {
                seft.initKenburnsElement(seft.options.kenburns);
            }
        },

        initKenburnsElement: function (config) {
            this.initOverlay('kenburns');

            this.container.css({
                position: 'relative',
                overflow: 'hidden'
            });

            var kbWrapper = $('<div/>', {'class': 'widget-kenburns'});
            kbWrapper.css({
                position: 'absolute',
                width: '100%',
                height: '100%',
                zIndex: 0
            });
            this.container.prepend(kbWrapper);

            $(kbWrapper).Kenburns({
                images: config.images,
                scale: 1,
                duration: 6000,
                fadeSpeed: 800
            });
        },

        initOverlay: function (type) {
            var seft = this;
            if (type == 'paralax' && seft.options.parallax.overlay == 'none') return;
            if (type == 'kenburns' && seft.options.kenburns.overlay == 'none') return;

            seft.container.prepend($('<div/>', {
                    'class': 'widget-overlay'
                }).css({
                    position: 'absolute',
                    left: 0,
                    top: 0,
                    width: '100%',
                    height: '100%',
                    backgroundColor: seft.options.parallax.overlay,
                    backgroundRepeat: 'repeat',
                    opacity: seft.options.parallax.opacity
                })
            );
        },

        initParallax: function () {
            if (!this.options.parallax && !this.options.kenburns) return;
            if (!this.options.parallax.enable && !this.options.kenburns.enable ) return;

            this.initOverlay('paralax');

            switch (this.options.parallax.type) {
                case 'video':
                    this.initBackgoundVideo(this.options.parallax.video || {});
                    break;
                case 'image':
                    this.initBackgroundImage(this.options.parallax.image || {});
                    break;
                case 'file':
                    this.initBackgroundVideoFile(this.options.parallax.file || {});
                    break;
            }
        },

        initBackgroundImage: function (config) {
            var seft = this;
            if (!config.src) return;

            var img = $('<div/>', {
                class: 'scrollbale'
            }).css({
                position: 'absolute',
                width: '100%',
                top: 0,
                backgroundImage:"url('"+config.src+"')",
                backgroundPosition:'center center',
                backgroundSize:'cover',
                left: 0,
                zIndex: 0
            });

            var newImg = $('<img/>', {
                src: config.src
            });

            seft.container.css({
                position: 'relative',
                overflow: 'hidden'
            }).prepend(img);

            newImg.on('load', function () {
                img.height(this.naturalHeight);
                seft.bindParallaxEvent(img);
            });
        },

        isElementInViewport: function (percent) {
            percent = percent || 1;

            var rect = this.container[0].getBoundingClientRect(),
                window_height = window.innerHeight || document.documentElement.clientHeight;

            if (rect.top > 0) return rect.top < window_height - rect.height * percent;
            else return rect.bottom > rect.height * percent;
        },

        isElementPartialInViewport: function () {
            var rect = this.container[0].getBoundingClientRect();

            return (rect.bottom > 0 && rect.bottom <= rect.height) ||
                (rect.top > 0 && rect.top <= (window.innerHeight || document.documentElement.clientHeight));
        },

        initBackgroundVideoFile: function (config) {
            var video = $('<video/>', {
                preload: 'preload',
                loop: 'loop',
                autoplay: 'autoplay',
                muted: !config.volume,
                poster: config.poster
            });

            video.css({
                width: '100%',
                height: 'auto',
                position: 'absolute',
                zIndex: 0,
                top: '0px',
                left: '-1px'
            });

            config['mp4'] && video.prepend($('<source/>', {src: config['mp4'], type: 'video/mp4'}));
            config['webm'] && video.prepend($('<source/>', {src: config['webm'], type: 'video/webm'}));

            this.container.css({
                position: 'relative',
                overflow: 'hidden'
            }).prepend(video);

            this.bindParallaxEvent(video);
            this.updateSize(video);
        },

        updateSize: function (elm) {
            var mediaAspect = 16 / 9;
            if (this.options.parallax.video.layout == 'fullscreen') {
                var windowW = $(window).width();
                var windowH = $(window).height();

                var windowAspect = windowW / windowH;
                if (windowAspect < mediaAspect) {
                    // taller
                    this.container.css({
                        width: (windowH * mediaAspect) + 'px',
                        height: windowH + 'px'
                    });
                    elm.css({
                        top: '0px',
                        left: '-' + (windowH * mediaAspect - windowW) / 2 + 'px',
                        height: windowH + 'px'
                    });
                } else {
                    // wider
                    this.container.css({
                        width: (windowW) + 'px',
                        height: (windowW / mediaAspect) + 'px'
                    });

                    elm.css({
                        top: '-' + (windowW / mediaAspect - windowH) / 2 + '0px',
                        left: '0px',
                        height: windowW / mediaAspect + 'px'
                    });
                }

            }

            if (this.options.parallax.video.layout == 'fullwidth') {
                var windowW = $(window).width();
                var windowH = this.options.parallax.video.height;

                var windowAspect = windowW / windowH;
                if (windowAspect < mediaAspect) {
                    // taller
                    this.container.css({
                        width: (windowH * mediaAspect) + 'px',
                        height: windowH + 'px'
                    });

                    elm.css({
                        top: '0px',
                        left: '-' + (windowH * mediaAspect - windowW) / 2 + 'px',
                        height: windowH + 'px'
                    });

                } else {
                    // wider
                    this.container.css({
                        width: windowW + 'px',
                        height: windowH + 'px'
                    });

                    elm.css({
                        top: '-' + (windowW / mediaAspect - windowH) / 2 + '0px',
                        left: '0px',
                        height: windowW / mediaAspect + 'px'
                    });
                }
            }

            if (this.options.parallax.video.layout == 'custom') {
                var windowW = this.options.parallax.video.width;
                var windowH = this.options.parallax.video.height;

                var windowAspect = windowW / windowH;
                if (windowAspect < mediaAspect) {
                    // taller
                    this.container.css({
                        width: windowW + 'px',
                        height: windowH + 'px'
                    });

                    elm.css({
                        top: '0px',
                        left: -(windowH * mediaAspect - windowW) / 2 + 'px',
                        height: windowH + 'px'
                    });

                } else {
                    // wider
                    this.container.css({
                        width: windowW + 'px',
                        height: windowH + 'px'
                    });

                    elm.css({
                        top: '0px',
                        left: -(windowH * mediaAspect - windowW) / 2 + 'px',
                        height: windowH + 'px'
                    });
                }
            }
        },

        onParallaxEvent: function (elm) {
            if (this.isElementPartialInViewport()) {
                var windowHeight = window.innerHeight || document.documentElement.clientHeight,
                    containerRect = this.container[0].getBoundingClientRect(),
                    maxWindowScroll = windowHeight + containerRect.height,
                    maxElementScroll = elm.height() - containerRect.height,
                    scrollRatio = maxElementScroll / maxWindowScroll,
                    amount = Math.floor((containerRect.top - windowHeight) * scrollRatio);

                if (this.support3d) {
                    elm.css({
                        '-webkit-transform': 'translate3d(0px,' + amount + 'px,0px)',
                        '-moz-transform': 'translate3d(0px,' + amount + 'px,0px)',
                        '-ms-transform': 'translate3d(0px,' + amount + 'px,0px)',
                        '-o-transform': 'translate3d(0px,' + amount + 'px,0px)',
                        'transform': 'translate3d(0px,' + amount + 'px,0px)'
                    });
                } else {
                    elm.css({
                        marginTop: amount + 'px'
                    });
                }
            }
        },

        bindParallaxEvent: function (elm) {
            var seft = this;
            $.each('DOMContentLoaded|load|resize|scroll|video:load'.split('|'),function (i,event) {
                $(window).on(event, function () {
                    seft.onParallaxEvent(elm);
                    seft.updateSize(elm);
                });
            });
        },

        initBackgoundVideo: function (config) {
            if (config.src.indexOf('youtube.com') > 0) {
                this.initBackgoundVideoYoutube(config);
            } else if (config.src.indexOf('vimeo.com') > 0) {
                this.initBackgoundVideoVimeo(config);
            }

            $(window).on('resize', function () {
                var d = this.calculateVideoSize(this.container),
                    iframe = parent.find('iframe').first();

                if (iframe) {
                    iframe.css({
                        height: d.height + 'px',
                        top: (-1 * d.top) + 'px'
                    });
                }
            });
        },

        getVideoIdVimeo: function (url) {
            return url ? url.replace(/[^0-9]+/g, '') : null;
        },

        getVideoIdYoutube: function (url) {
            var videoStr = url.split('v=')[1];
            return videoStr ? (videoStr.indexOf('&') > 0 ? videoStr.substring(0, videoStr.indexOf('&')) : videoStr) : null;
        },

        onYoutubePlayerReady: function (config) {
            var seft = this;
            if (!seft.player || !seft.player.mute) {
                return setTimeout(function () {
                    seft.onYoutubePlayerReady(config);
                }, 500);
            }

            if (!config.volume) {
                seft.player.mute();
            }

            seft.bindParallaxEvent(seft.player.a);
            $(window).trigger('video:load');

            return seft.player;
        },

        onYoutubeAPIReady: function (videoId, config) {
            var seft = this;
            if (!window['YT']['Player']) {
                return setTimeout(function () {
                    seft.onYoutubeAPIReady(videoId, config);
                }, 500);
            }

            var d = seft.calculateVideoSize(this.container),
                playerId = seft.id + '-player',
                playerDiv = $('<div/>', {id: playerId}),
                muteBtn = seft.addVideoMuteButton(seft.container, !config.volume);

            muteBtn.on('click', function (ev) {
                if (!seft.player) return;

                if (seft.player.isMuted()) {
                    seft.player.unMute();
                    btn.addClass(seft.VIDEO_MUTE_BTN_CLASS_ON);
                    btn.removeClass(seft.VIDEO_MUTE_BTN_CLASS_OFF);
                } else {
                    $(this).addClass(seft.VIDEO_MUTE_BTN_CLASS_OFF);
                    $(this).removeClass(seft.VIDEO_MUTE_BTN_CLASS_ON);
                    seft.player.mute();
                }
            });

            playerDiv.css({
                position: 'absolute',
                zIndex: 0,
                top: 0
            });

            this.container.css({
                position: 'relative',
                overflow: 'hidden'
            });

            var overlay = $(seft.attr('id') + '-overlay');
            if (overlay) {
                overlay.before( playerDiv);
            } else {
                seft.container.prepend(playerDiv);
            }

            return seft.player = new YT.Player(playerId, {
                height: d.height,
                width: '100%',
                videoId: videoId,
                playerVars: {
                    autoplay: 1,
                    autohide: 1,
                    controls: 0,
                    loop: 1,
                    showinfo: 1,
                    wmode: 'transparent',
                    html5: 1,
                    rel: 0,
                    playlist: videoId
                },
                events: {
                    'onReady': seft.onYoutubePlayerReady.call(this, config)
                }
            });
        },

        initBackgoundVideoYoutube: function (config) {
            var seft = this;
            var videoId = seft.getVideoIdYoutube(config.src);
            if (!videoId) return;
            if (!window['YT']) {
                $.getScript('//www.youtube.com/iframe_api', function () {
                    seft.onYoutubeAPIReady(videoId, config);
                });
            } else {
                seft.onYoutubeAPIReady(videoId, config);
            }
        },

        initBackgoundVideoVimeo: function (config) {
            var seft = this;
            var videoId = seft.getVideoIdVimeo(config.src);
            if (!videoId) return;

            var videoSrc = '//player.vimeo.com/video/' + videoId + '?title=0&byline=0&portrait=0&autoplay=1&loop=1&api=1',
                muteBtn = seft.addVideoMuteButton(this.container, !config.volume),
                d = seft.calculateVideoSize(this.container),
                iframe = $('<iframe/>', {
                    id: seft.attr('id') + '-player',
                    src: videoSrc,
                    frameborder: 0,
                    allowfullscreen: 1,
                    height: d.height + 'px',
                    width: '100%'
                }).css({
                    position: 'absolute',
                    left: 0,
                    zIndex: 0,
                    top: 0
                });

            seft.container.css({
                position: 'relative',
                overflow: 'hidden'
            });

            var overlay = $('#'+seft.id + '-overlay');
            if (overlay) {
                overlay.before(iframe);
            } else {
                seft.container.prepend(iframe);
            }

            seft.player = iframe;

            muteBtn.on('click', function (ev) {
                if (!seft.player) return;

                var  value;

                if (seft.player.muted) {
                    value = 0;
                    seft.player.muted = 1;
                    $(this).addClass(seft.VIDEO_MUTE_BTN_CLASS_OFF);
                    $(this).removeClass(seft.VIDEO_MUTE_BTN_CLASS_ON);
                } else {
                    value = 1;
                    seft.player.muted = 0;
                    $(this).addClass(seft.VIDEO_MUTE_BTN_CLASS_ON);
                    $(this).removeClass(seft.VIDEO_MUTE_BTN_CLASS_OFF);
                }

                seft.postmessage(seft.player, 'setVolume', value);
            });

            $(window).on('message', function (e) {
                if (!seft.player) return;

                var data = JSON.parse(e.data);

                switch (data.event) {
                    case 'ready':
                        seft.postmessage(seft.player, 'addEventListener', 'play');
                        break;
                    case 'play':
                        if (!config.volume) {
                            seft.postmessage(this.player, 'setVolume', 0);
                            seft.player.muted = 1;
                        } else seft.player.muted = 0;
                        $(window).trigger('video:load');
                        break;
                }
            });

            seft.bindParallaxEvent(iframe);
        },

        calculateVideoSize: function (container) {
            var $container = $(container),
                height = parseInt($container.width() * this.VIDEO_HW_RATIO),
                top = height > $container.height() ? parseInt((height - $container.height()) / 2) : 0;

            return {height: height, top: top};
        },

        addVideoMuteButton: function (container, muted) {
            var $container = $(container);
            if (!$container) return null;

            var btn = $('<i/>', {
                'class': this.VIDEO_MUTE_BTN_CLASS,
                href: 'javascript:void(0)'
            });

            if (muted) btn.addClass(this.VIDEO_MUTE_BTN_CLASS_OFF);
            else btn.addClass(this.VIDEO_MUTE_BTN_CLASS_ON);

            $container.append(btn);

            return btn;
        },

        postmessage: function (player, method, value) {
            var data = {method: method};
            if (value != null) data.value = value;
            player.contentWindow.postMessage(JSON.stringify(data), player.src.split('?')[0]);
        }
    });
    return $.mage.cleverProductMage;
});