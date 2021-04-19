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
        newCartonPopup(url, modalId)
        {
            this.selectAddressPopup(url, modalId);
        },

        selectAddressPopup(url, modalId)
        {
            var data = {
                form_key: FORM_KEY,
            }

            var self = this;

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectCartonAddressModal = $('#' + modalId).modal({
                    closed: function () {
                        self.selectProductsPopup(url, modalId);
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

        selectProductsPopup(url, modalId)
        {
            var data = {
                form_key: FORM_KEY,
            }

            var self = this;

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
                carton.newCartonPopup(config.url, config.modal_id);
                return false;
            });
        }
    }
});
