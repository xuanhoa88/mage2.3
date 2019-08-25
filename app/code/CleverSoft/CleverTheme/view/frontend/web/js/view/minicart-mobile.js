/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart-mobile\']');
    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
    });
    /**
     * @return {Boolean}
     */
    function initSidebar() {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart-mobile',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'showcart-mobile': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper-mobile',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item',
                'increase': ':button.increase',
                'reduced': ':button.reduced'

            },
            'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the shopping cart?')
        });
    }

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        cart: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');
                this.cart = customerData.get('cart');

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
            }, this);
            $('[data-block="minicart-mobile"]').on('contentLoading', function (event) {
                addToCartCalls++;
                self.isLoading(true);
                $('#minicart-content-wrapper-mobile').addClass('ajax-items-loading');
                $('[data-block="minicart-mobile"]').on('contentUpdated', function ()  {
                    $('#minicart-content-wrapper-mobile').removeClass('ajax-items-loading');
                });
            });

            if (cartData()['website_id'] !== window.checkout.websiteId) {
                customerData.reload(['cart'], false);
            }
            self.closeSidebar();
            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * Close mini shopping cart.
         */
        closeMinicart: function () {
            $('[data-block="minicart-mobile"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
        },

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var minicart = $('[data-block="minicart-mobile"]');
            minicart.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
            if ($('.product-info-main .product-spend-shiping').length && this.cart().neededTotalAmount) {
                $('.product-info-main .product-spend-shiping span').html("Spend  <span class='moneys'><span class='money'>"+this.cart().neededTotalAmount+"</span></span> to get Free Shipping");
            } else if ($('.product-info-main .product-spend-shiping') && this.cart().currentPercent == 100) {
                $('.product-info-main .product-spend-shiping span').html("Congratulations! You've got free shipping!");
            }
        },

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        },

        checkCalculateShipAvailability: function () {
            return this.cart().ruleFreeShip;
        },

        getNeededTotal: function () {
            return this.cart().neededTotal;
        },

        getCurrentPercent: function () {
            return this.cart().currentPercent+'%';
        },

        checkFinalCalculateShip: function () {
            return this.cart().currentPercent == 100;
        }
    });
});
