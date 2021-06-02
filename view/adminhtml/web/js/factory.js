/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
define([
    'jquery',
    "Magento_Ui/js/modal/modal",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'react-app'
], function ($, modal, $t, alert, ReactApp) {
    'use strict';

    var factory = {

        selectedFactoryBox: null,
        refreshFactoryBoxUrl: null,
        cartonsBox: null,

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

                    var data = {
                        form_key: FORM_KEY,
                        factory_id: response.id,
                    }
                    $.ajax({
                        type: "GET",
                        url: self.factoryGetItemsUrl,
                        dataType: 'json',
                        showLoader: true,
                        data: data
                    }).done(function (response){
                        require(['react-app'], function (ReactApp) {
                            ReactApp.init('react-category-root', {
                                data: response,
                                factory_id: currentFactoryId,
                                post_url: self.factoryPostCartonsUrl,
                                form_key: FORM_KEY
                            });
                        });
                    })

                }
            });
        },

        selectFactory(url, modalId)
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
            });
        }
    }

    return {
        initSelectFactoryButton(config, button)
        {
            var buttonElement = $(button);

            factory.selectedFactoryBox = $('#' + config.selected_factory_box);
            factory.refreshFactoryBoxUrl = config.refresh_factory_box_url;
            factory.factoryGetItemsUrl = config.factory_get_items_url;
            factory.factoryPostCartonsUrl = config.factory_post_cartons_url;
            factory.cartonsBox = $('#' + config.cartons_box);

            buttonElement.click(function () {
                factory.selectFactory(config.url, config.modal_id);
                return false;
            });

            buttonElement.removeAttr('disabled');
        }
    }
});
