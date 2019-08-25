/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    $.widget('mage.CleverBuilderSliderUi', {
        options: {
            min: '',
            max: '',
            refresh: true,
            elementId: '',
            value:''
        },
        /*
         is to do some functions of control buttons
         */
        _create: function () {
            var seft = this;
            seft.element.slider({
                range: "min",
                min: seft.options.min,
                max: seft.options.max,
                value: seft.options.value,
                stop: function( event, ui ) {
                    $('#'+seft.options.elementId).val( ui.value );
                    $('#'+seft.options.elementId).trigger('change',[seft]);
                }
            });

            /*
             * set value for the text box
             */
            $('#'+seft.options.elementId).val( seft.element.slider( "value" ) );
            /*
             * add event change
             */
            $('#'+seft.options.elementId).on('change',function(e){
                var $element = this;
                $(this).data('oldValue',seft.element.slider('value'));
                seft.element.slider({value:$(this).val()});
                // if(seft.options.refresh) {
                //     $(document).trigger('changeSliderUi');
                // }
            });

            $('#'+seft.options.elementId).keydown(function(event){
                var $element = this;
                if(event.keyCode == 38) {
                    if(parseInt($(this).val()) + 1 > seft.options.max) return;
                    $(this).val(parseInt($(this).val()) + 1);
                }
                else if(event.keyCode == 40) {
                    if(parseInt($(this).val()) - 1 < seft.options.min) return;
                    $(this).val(parseInt($(this).val()) - 1);
                } else return;
                $(this).trigger('change',[$element]);
            });

        }
    });
    return $.mage.CleverBuilderSliderUi;
});