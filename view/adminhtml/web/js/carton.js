/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
define([
    'jquery',
    "Magento_Ui/js/modal/modal",
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($, modal, $t, alert) {
    'use strict';

    var carton = {
        newCartonPopup(config)
        {
            this.selectAddressPopup(config);
        },

        selectAddressPopup(config)
        {
            var data = {
                form_key: FORM_KEY,
            }

            var self = this;
            var url = config.select_address_url;
            var modalId = config.select_address_modal_id;

            window.selectedAddressId = null;

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectCartonAddressModal = $('#' + modalId).modal({
                    closed: function () {
                        if (window.selectedAddressId) {
                            self.selectProductsPopup(config);
                        }
                    },
                    opened: function () {
                    },
                    modalClass: 'magento',
                    type: 'popup',
                    title: $t('Select Address'),
                    buttons: []
                });
                window.selectCartonAddressModal.html(response.html);
                window.selectCartonAddressModal.modal('openModal');
            });
        },

        selectProductsPopup(config)
        {
            var data = {
                form_key: FORM_KEY,
                factory_id: window.selectedFactoryId,
                address_id: window.selectedAddressId,
            }

            var self = this;
            var url = config.select_items_url;
            var modalId = config.select_items_modal_id;

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectProductsAddressModal = $('#' + modalId).modal({
                    closed: function () {
                    },
                    opened: function () {
                    },
                    modalClass: 'magento',
                    type: 'popup',
                    title: $t('Select Products'),
                    buttons: []
                });
                window.selectProductsAddressModal.html(response.html);
                window.selectProductsAddressModal.modal('openModal');
            });
        }
    }

    return {
        initAddCartonButton(config, button)
        {
            var buttonElement = $(button);

            buttonElement.click(function () {
                carton.newCartonPopup(config);
                return false;
            });
        }
    }
});
