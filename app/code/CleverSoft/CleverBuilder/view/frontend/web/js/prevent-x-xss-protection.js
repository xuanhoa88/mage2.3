/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    $.widget('mage.CleverBuilderPreventXXSS', {
        options: {
        },
        /*
         is to do some functions of control buttons
         */
        _create: function () {
            var seft = this;
            var $action = seft.element.data('url');
            seft.element.attr('action',$action);
            seft.element.removeAttr('data-url');
        }
    });
    return $.mage.CleverBuilderPreventXXSS;
});