define([
   "jquery",
   "jquery/ui",
   'domReady!'
], function($){
   "use strict";

    var iframe = window.frameElement;
    if (iframe){
        iframe.contentWindow = document;//normalization: some browsers don't set the contentDocument, only the contentWindow
        // if (iframe.contentWindow == null) {
        //     iframe.contentWindow = document;
        // }
        var parent = window.parent;
        $(parent.document).ready(function(){//wait for parent to make sure it has jQuery ready
            var parent$ = parent.jQuery;

            parent$(iframe).trigger("iframeloading");

            $(function(){
                parent$(iframe).trigger("iframeready");
            });

            $(window).load(function(){//kind of unnecessary, but here for completion
                parent$(iframe).trigger("iframeloaded");
            });

            $(window).unload(function(e){//not possible to prevent default
                parent$(iframe).trigger("iframeunloaded");
            });

            $(window).on("beforeunload",function(){
                parent$(iframe).trigger("iframebeforeunload");
            });
        });
    }

    /**
     * Scroll this window over a specific element. Called by the main live editor.
     * @param el
     */
    function liveEditorScrollTo( el ){
        var $ = jQuery,
            $el = $( el ),
            rect = $el[0].getBoundingClientRect();

        if( rect.top <= 0 || rect.bottom >= $(window).height() ) {
            var newScrollTop = 0;

            if( rect.top < 0 || $el.height() >= $( window ).height() * 0.8 ) {
                // Scroll up to the element
                newScrollTop = $( window ).scrollTop() + rect.top - 150;
            } else if( rect.bottom > $(window).height() ) {
                // Scroll down to the element
                newScrollTop = $( window ).scrollTop() + ( rect.bottom -  $(window).height() ) + 150;
            }

            $( window )
                .clearQueue()
                .animate({
                    scrollTop: newScrollTop
                }, 450 );
        }
    }
});