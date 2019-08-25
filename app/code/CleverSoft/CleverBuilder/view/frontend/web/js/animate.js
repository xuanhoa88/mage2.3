define([
    "jquery","jquery/ui",'domReady!','catalogAddToCart',
],function ($) {
    $.widget('clever.elementanimate', {

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
                self._animteLoad();
            } else {
                var interval = setInterval(function(){
                    if(self._isVisible()) {
                        clearInterval(interval);
                        self._animteLoad();
                    }
                },500);
            }
        },
        _animteLoad: function() {
            var self = this;
            var elClass = self.element.attr('class').replace(/\ /g, '.').replace(/^[\.]+|[\.]+$/g, "");

            self.element.attr('data-animated','true');
            $(function(){
                $("iframe").on("iframeloading iframeready iframeloaded iframebeforeunload iframeunloaded", function(e){
                    if($('iframe').contents().find('.'+elClass).length) {
                        $('iframe').contents().find('.'+elClass).attr('data-animated','true');
                    }
                });
            });
            
        }
    });
    return $.clever.elementanimate;
});

