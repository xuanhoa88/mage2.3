define([
    "jquery","jquery/ui",'domReady!','catalogAddToCart',
],function ($) {
    $.widget('clever.elementloader', {
        options: {
            ajaxUrl: null,
            data: null
        },

        _isVisible: function(){
            return (this.element.get(0).offsetWidth > 0) & (this.element.get(0).offsetHeight > 0) & (this.element.is(':visible'));
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
            var config = this.options;
            $.ajax({
                url: config.ajaxUrl,
                type: "POST",
                data: config.data,
                cache: false,
                success: function(res){
                    if(res) {
                        self.element.html(res);
                    }
                    
                    self.element.trigger('contentUpdated');
                },
                error: function(){
                    self.element.html('<p>An error has occurred</p>');
                }
            });
        }
    });
    return $.clever.elementloader;
});
