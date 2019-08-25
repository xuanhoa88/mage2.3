/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent'
], function (ko, $, Component) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        integrateManualMode = 1,
        integratesConfig = checkoutConfig ? checkoutConfig.checkoutIntegrates : {};
        
    return Component.extend({
        defaults: {
            template: 'CleverSoft_CleverCookieLaw/checkout/checkout-integrate'
        },
        isVisible: integratesConfig.isEnabled,
        integrates: integratesConfig.integrates,

        /**
         * build a unique id for the term checkbox
         *
         * @param {Object} context - the ko context
         * @param {Number} integrateId
         */
        getCheckboxId: function (context, integrateId) {
            var paymentMethodName = '',
                paymentMethodRenderer = context.$parents[1];

            // corresponding payment method fetched from parent context
            if (paymentMethodRenderer) {
                // item looks like this: {title: "Check / Money order", method: "checkmo"}
                paymentMethodName = paymentMethodRenderer.item ?
                  paymentMethodRenderer.item.method : '';
            }

            return 'integrate_' + paymentMethodName + '_' + integrateId;
        }
    });
});
