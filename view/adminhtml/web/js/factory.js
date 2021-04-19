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
        selectCartonBox: null,
        selectedAddressBox: null,

        refreshFactoryBox()
        {
            var self = this;
            var currentFactoryId = window.selectedFactoryId;

            var data = {
                form_key: FORM_KEY,
                factory_id: currentFactoryId,
            }

            $.ajax({
                type: "POST",
                url: this.refreshFactoryBoxUrl,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.selectedFactoryId = response.id;
                self.selectedFactoryBox.html(response.html);

                if (window.selectedFactoryId) {
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
                        self.selectedAddressBox.html($t('Not selected'));
                        self.selectCartonBox.hide();
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
            });
        }
    }

    return {
        initSelectFactoryButton(config, button)
        {
            var buttonElement = $(button);

            factory.selectedFactoryBox = $('#' + config.selected_factory_box);
            factory.selectAddressBox = $('#' + config.select_address_box);
            factory.selectedAddressBox = $('#' + config.selected_address_box);
            factory.selectCartonBox = $('#' + config.select_carton_box);
            factory.refreshFactoryBoxUrl = config.refresh_factory_box_url;

            buttonElement.click(function () {
                factory.selectFactory($(this), config.url, config.modal_id);
                return false;
            });

            buttonElement.removeAttr('disabled');
        }
    }
});
