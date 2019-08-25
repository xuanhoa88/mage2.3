define([
    "CleverSoft_LayeredNavigation/js/nprogress",
    "jquery",

    "jquery/ui"
], function(NProgress, $){
    ;'use strict';
    var MTFilter = {
        container: null,
        layer: null,
        name: null,
        mode: null,
        init: function(name, config){
            this.name = name;
            this.config = config;
            if (this.config.enable){
                if (this.config.bar){
                    NProgress.configure({
                        showSpinner: true
                    });
                }
                $(function(){
                    MTFilter.collect();
                });
            }
        },
        collect: function(){
            if(this.config.mainDOM) this.container = $(this.config.mainDOM).eq(0);
            else this.container = $('.zoo-main-content-area').eq(0);
            if(this.config.layerDOM) this.layer = $(this.config.layerDOM).eq(0);
            else this.layer = $('#zoo-layer-navigation');

            this.initPaging();
            this.initSorting();
            this.initModeSelection();
            this.initLinkFilter();
            this.initPriceFilter();
        },
        initSorting: function(){
            $('.toolbar-sorter').find('a').each(function(){
                $(this).on('click', function(ev){
                    ev.preventDefault();
                    var url = $(this).attr('href');
                    if(url[0] == '#')	url = url.substr(1);
                    MTFilter.sendRequest(url);
                    var obj = { Page: 'test', Url: url };
                    history.pushState(obj, obj.Page, obj.Url);
                });
            });
        },
        initPaging: function(){
            $('.pages').find('a').each(function(){
                $(this).on('click', function(ev){
                    ev.preventDefault();
                    MTFilter.sendRequest($(this).attr('href'));
                });
            });
        },
        initModeSelection: function(){
            jQuery('.toolbar-products .modes-mode').each(function(){
                $(this).on('click',function(ev){
                    ev.preventDefault();
                    var selectionMode, oldSelectionMode;
                    selectionMode = $(this).attr('id');
                    if(selectionMode == 'list') oldSelectionMode = 'grid';
                    else oldSelectionMode = 'list';
                    MTFilter.mode = selectionMode;
                    if($(this).hasClass('active')) return;
                    else {
                        $(this).addClass('active');

                        $('#'+oldSelectionMode).removeClass('active');
                        $('#zoo-product-listing').removeClass(oldSelectionMode).removeClass('products-' + oldSelectionMode).addClass(selectionMode).addClass('products-'+selectionMode);
                    }
                });
            });
        },
        setConfig: function(obj){
            Object.extend(this.config, obj);
        },
        initLinkFilter: function(){
            if ($('.toolbar-products:first').length){
                $('.toolbar-products:first').find('a').each(function(){
                    $(this).on('click', function(ev){
                        ev.preventDefault();
                        //MTFilter.sendRequest($(this).attr('href'));
                    });
                });
            }

            if (this.layer){
                this.layer.find('input[type="checkbox"]').each(function(){
                    var  parent = $(this).parents('.item');
                    $(this).on('click', function(ev){
                        MTFilter.sendRequest(parent.find('a').attr('href'));
                        var obj = { Page: 'test', Url: parent.find('a').attr('href') };
                        history.pushState(obj, obj.Page, obj.Url);
                        MTFilter.scrollUp();
                    });
                });

                this.layer.find('a').each(function(){
                    var parent = $(this).parents('.item');
                    if($(this).attr('href') == '#') return ;
                    $(this).on('click', function(ev){
                        parent.find('input[type="checkbox"]').prop('checked', !parent.find('input[type="checkbox"]').prop('checked'));
                        if($(this).parents('.swatch-attribute-options').length && !$(this).hasClass('selected')) $(this).addClass('selected')
                        else $(this).removeClass('selected');
                        ev.preventDefault();
                        MTFilter.sendRequest($(this).attr('href'));
                    });
                });

            }
        },
        initPriceFilter: function(){
            var option = $("#mt_layer_filter").data();

            $("#mt_layer_filter_display").html( "<span>" + MTFilter.renderLabel(option.template, option.from) + " </span><span class='pull-right'> " + MTFilter.renderLabel(option.template, option.to) + "<span>" );
            $('#mt_layer_filter').slider({
                range: true,
                min: option.min,
                max: option.max,
                values: [ option.from, (option.to)],
                slide: function( event, ui ) {
                    $("#mt_layer_filter_display").html( "<span>" + MTFilter.renderLabel(option.template, ui.values[ 0 ]) + " </span><span class='pull-right'> " + MTFilter.renderLabel(option.template, ui.values[ 1 ]) + "<span>" );
                },
                change: function( event, ui ) {
                    var href = option.url.replace('slider_from', ui.values[ 0 ]).replace('slider_to', ui.values[1]);
                    MTFilter.sendRequest(href);
                }
            });
        },
        scrollUp : function(){
            jQuery('html, body').animate({
                scrollTop: jQuery("#zoo-layer-navigation").offset().top
            }, 1000);
        },
        renderLabel:function(template, value) {
            return template.replace('{amount}', value);
        },
        getParams: function(){
            return {
                isAjax: true,
                form_key: jQuery('input[name="form_key"]').val(),
                product_list_mode : this.mode
            };
        },
        setAjaxLocation: function(url){

            if (this.getURLParameter(url,'limit') || this.getURLParameter(url,'product_list_order')){
                this.sendRequest(url);
            }else setLocation(url);
        },
        getURLParameter: function(sUrl, sParam){

            var sURLVariables = sUrl.split('&');

            for (var i = 0; i < sURLVariables.length; i++)

            {

                var sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] == sParam)

                {

                    return sParameterName[1];

                }
            }
        },
        sendRequest: function(url, success, error){
            if (this.config.enable){
                if (this.config.bar) NProgress.start();
                jQuery.ajax({
                        method: "POST",
                        url: url,
                        data : this.getParams(),
                        dataType : 'json'}
                ).done(function (response) {
                        if (MTFilter.config.bar) NProgress.done();
                        try{
                            var   main = response.main ? response.main.replace(/setLocation/g, this.name+'.setAjaxLocation') : null,
                                layer = response.layer || null;
                            if (main && MTFilter.container) MTFilter.container.html(main).trigger('contentUpdated');
                            if (layer && MTFilter.layer) MTFilter.layer.html(layer).trigger('contentUpdated');
                            //setGridItem($mt);
                            setTimeout(function(){
                                MTFilter.collect();
                                $(window).resize();
                            });

                            if (success) success(response);
                        }catch(e){
                            console.log(e.message);
                        }


                    });
            }else{
                setLocation(url);
            }
        }
    }
    window.Filter = MTFilter;
    return MTFilter;
});