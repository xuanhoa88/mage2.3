/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        integratesConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {},
        integratesInputPath = '.payment-method._active div.checkout-integrates input';

    return {
        /**
         * Validate checkout integrates
         *
         * @returns {Boolean}
         */
        validate: function () {
            var isValid = true;

            if (!integratesConfig.isEnabled) {
                return true;
            }

            $(integratesInputPath).each(function (index, element) {
                if (!$.validator.validateSingleElement(element, {
                    errorElement: 'div'
                })) {
                    isValid = false;
                }
            });

            return isValid;
        }
    };
});
