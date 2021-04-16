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

    var factory = {

        selectedFactoryBox: null,
        selectAddressBox: null,
        refreshFactoryBoxUrl: null,

        refreshFactoryBox()
        {
            if (!this.refreshFactoryBoxUrl) {
                console.warn('Refresh factory Box Url not set!')
                return;
            }

            var self = this;

            var data = {
                form_key: FORM_KEY,
            }

            $.ajax({
                type: "POST",
                url: this.refreshFactoryBoxUrl,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectedFactoryCode = response.factory_code;
                if (window.selectedFactoryCode) {
                    self.selectAddressBox.show();
                }
            });
        },

        selectFactory(buttonElement, url, modalId)
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
                window.selectFactoryModal = $('#' + modalId).modal({
                    closed: function () {
                        $(this).html('');
                        self.refreshFactoryBox();
                    },
                    opened: function () {
                    },
                    modalClass: 'magento',
                    type: 'popup',
                    title: $t('Select Factory'),
                    buttons: []
                });
                window.selectFactoryModal.html(response.html);
                window.selectFactoryModal.modal('openModal');
            }).error(function(response) {
                if (response.statusText) {
                    var message = response.statusText;
                } else {
                    var message = $t('Undefined Error');
                }
                alert({
                    content: message
                });
            });
        }
    }

    return {
        initSelectFactoryButton(config, button)
        {
            var buttonElement = $(button);

            factory.selectedFactoryBox = $('#' + config.selected_factory_box);
            factory.selectAddressBox = $('#' + config.select_address_box);
            factory.refreshFactoryBoxUrl = config.refresh_factory_box_url;

            buttonElement.click(function () {
                factory.selectFactory($(this), config.url, config.modal_id);
                return false;
            });
        }
    }
});
