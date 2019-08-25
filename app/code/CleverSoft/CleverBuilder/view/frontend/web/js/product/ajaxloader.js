define([
    "jquery","jquery/ui","mage/dataPost",'domReady!','catalogAddToCart', 'cleverProductMage'
],function ($) {
    $.widget('clever.productloader', {
        options: {
            ajaxUrl: null,
            data: null
        },

        _isVisible: function(){

            var height = this.element.outerHeight();
            var width = this.element.outerWidth();
     
            if(!width || !height){
                return false;
            }
            
            var win = $(window);
     
            var viewport = {
                top : win.scrollTop(),
                left : win.scrollLeft()
            };
            viewport.right = viewport.left + win.width();
            viewport.bottom = viewport.top + win.height();
     
            var bounds = this.element.offset();
            bounds.right = bounds.left + width;
            bounds.bottom = bounds.top + height;
            
            var showing = {
              top : viewport.bottom - bounds.top,
              left: viewport.right - bounds.left,
              bottom: bounds.bottom - viewport.top,
              right: bounds.right - viewport.left
            };
                
            return this.element.is(':visible') && (this.element.get(0).offsetWidth > 0) & (this.element.get(0).offsetHeight > 0) & (showing.top > 0 && showing.left > 0 && showing.right > 0 && showing.bottom > 0);
        },
        _create: function() {
            var self = this;
            if(self._isVisible()){
                self._ajaxLoad();
            } else {
                var interval = setInterval(function(){
                    if(self._isVisible()) {
                        clearInterval(interval);
                        self._ajaxLoad();
                    }
                },500);
            }
        },
        _ajaxLoad: function() {
            var self = this;
            var elClass = self.element.attr('class').replace(/\ /g, '.').replace(/^[\.]+|[\.]+$/g, "");
            var config = this.options;
            $.ajax({
                url: config.ajaxUrl,
                type: "GET",
                data: config.data,
                cache: true,
                success: function(res){
                    if(res) {
                        self.element.html(res);
                        var interval = setInterval(function(){
                            if($('iframe').contents().find('.'+elClass).length) {
                                $('iframe').contents().find('.'+elClass).html(res);
                                $('iframe').contents().find('.'+elClass).trigger('contentUpdated');
                                if ($('iframe').contents().find('.zoo-product-carousel')) {
                                    $('iframe').contents().find('.zoo-product-carousel').cleverProductMage({carousel:{"enable":1,"dots":config.data.carousel.dots ? true : false,"loop":config.data.carousel.loop ? true : false,"navText":config.data.carousel.navText}});
                                }
                                clearInterval(interval);
                            }
                        },500);
                    }
                    
                    $(".tocompare").dataPost();
                    self.element.trigger('contentUpdated');
                    $("[data-role=tocart-form], .form.map.checkout").catalogAddToCart({});
                },
                error: function(){
                    self.element.html('<p>An error has occurred</p>');
                }
            });
        }
    });
    return $.clever.productloader;
});
