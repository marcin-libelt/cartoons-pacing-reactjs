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
        refreshFactoryBoxUrl: null,
        cartonsBox: null,
        cartonListBox: null,

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
                    self.cartonsBox.show();
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
                        self.cartonListBox.html($t('None'));
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
            factory.refreshFactoryBoxUrl = config.refresh_factory_box_url;
            factory.cartonsBox = $('#' + config.cartons_box);
            factory.cartonListBox = $('#' + config.carton_list_box);

            buttonElement.click(function () {
                factory.selectFactory($(this), config.url, config.modal_id);
                return false;
            });

            buttonElement.removeAttr('disabled');
        }
    }
});
