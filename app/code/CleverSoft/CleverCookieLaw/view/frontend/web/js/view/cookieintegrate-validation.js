/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'CleverSoft_CleverCookieLaw/js/model/cookieintegrate-validator'
], function (Component, additionalValidators, cookieintegrateValidator) {
    'use strict';

    additionalValidators.registerValidator(cookieintegrateValidator);

    return Component.extend({});
});
