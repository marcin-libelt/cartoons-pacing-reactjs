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

    var address = {

        selectedAddressBox: null,
        selectCartonBox: null,
        refreshAddressBoxUrl: null,

        refreshAddressBox()
        {
            var self = this;

            var data = {
                form_key: FORM_KEY,
                address_id: window.selectedAddressId,
            }

            $.ajax({
                type: "POST",
                url: this.refreshAddressBoxUrl,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectedAddressId = response.id;
                self.selectedAddressBox.html(response.html);

                if (window.selectedAddressId) {
                    self.selectCartonBox.show();
                }
            });
        },

        selectAddress(buttonElement, url, modalId)
        {

            if (!window.selectedFactoryId) {
                alert({
                    content: $t('Please Select Factory before')
                });
                return;
            }

            var self = this;

            var data = {
                form_key: FORM_KEY,
                factory_id: window.selectedFactoryId,
            }

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectAddressModal = $('#' + modalId).modal({
                    closed: function () {
                        $(this).html('');
                        self.refreshAddressBox();
                    },
                    opened: function () {
                    },
                    modalClass: 'magento',
                    type: 'popup',
                    title: $t('Select Address'),
                    buttons: []
                });
                window.selectAddressModal.html(response.html);
                window.selectAddressModal.modal('openModal');
            });
        }
    }

    return {
        initSelectAddressButton(config, button)
        {
            var buttonElement = $(button);

            address.selectedAddressBox = $('#' + config.selected_address_box);
            address.selectCartonBox = $('#' + config.select_carton_box);
            address.refreshAddressBoxUrl = config.refresh_address_box_url;

            buttonElement.click(function () {
                address.selectAddress($(this), config.url, config.modal_id);
                return false;
            });
        }
    }
});
