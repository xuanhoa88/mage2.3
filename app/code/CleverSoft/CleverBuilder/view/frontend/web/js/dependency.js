/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    $.widget('mage.CleverBuilderDependency',{
        options: {
        },
        /*
         is to do some functions of control buttons
         */
        _create: function(){
            var seft = this;
            var $element = seft.element;
            var $cid = $element.data('id');
            var $fieldNames = $element.closest('.cs-content.panel-dialog').find('input[name="field-name-'+$cid+'"]').val();
            $fieldNames = $fieldNames.split(',');
            if($fieldNames.length > 0) {
                $.each( $fieldNames, function( i, val ) {
                    var $fieldName = 'widgets['+$cid+']['+val+']';
                    var $depends = $('[name="'+$fieldName+'"]').attr('data-depends');
                    var $indepence = $('[name="'+$fieldName+'"]').attr('data-indepence');
                    if($depends) {
                        $depends =  $.parseJSON ($depends);
                    }
                    if($depends){
                        //for(v$key in $depends) {
                        var display = false;
                        $.each($depends, function(name, val){
                            var $val_depend =  $('[name="widgets['+$cid+']['+name+']"]').val();
                            //put event into $fieldName
                            $('[name="widgets['+$cid+']['+name+']"]').on('change',function(){
                                var current_val = $(this).val();
                                $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field[data-dependon-'+name+']').hide();
                                $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field[data-dependon-'+name+'="'+current_val+'"]').show();
                            });
                            if ($val_depend == val) display = true;
                            else display = false;
                        }) ;

                        if(display) $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field').show();
                        else $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field').hide();
                        //}
                    }
                    if($indepence) {
                        $indepence =  $.parseJSON ($indepence);
                    }
                    if($indepence){
                        //for(v$key in $indepence) {
                        var display = true;
                        $.each($indepence, function(name, val){
                            var $val_indepence =  $('[name="widgets['+$cid+']['+name+']"]').val();
                            //put event into $fieldName
                            $('[name="widgets['+$cid+']['+name+']"]').on('change',function(){
                                var current_valindepence = $(this).val();
                                $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field[data-indepenceon-'+name+']').show();
                                $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field[data-indepenceon-'+name+'="'+current_valindepence+'"]').hide();
                            });
                            if ($val_indepence == val) display = false;
                            else display = true;
                        }) ;

                        if(display) $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field').show();
                        else $('[name="'+$fieldName+'"]').closest('.cleversoft-widget-field').hide();
                        //}
                    }
                });
            }
        }
    });
    return $.mage.CleverBuilderDependency;
});