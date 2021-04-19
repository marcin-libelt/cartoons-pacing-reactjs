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
        newCartonPopup(buttonElement, url, modalId)
        {
            var data = {
                form_key: FORM_KEY,
            }

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                window.addCartonModal = $('#' + modalId).modal({
                    closed: function () {
                    },
                    opened: function () {
                    },
                    modalClass: 'magento',
                    type: 'popup',
                    title: $t('New Carton'),
                    buttons: []
                });
                window.addCartonModal.html(response.html);
                window.addCartonModal.modal('openModal');
            });
        }
    }

    return {
        initAddCartonButton(config, button)
        {
            var buttonElement = $(button);

            buttonElement.click(function () {
                carton.newCartonPopup($(this), config.url, config.modal_id);
                return false;
            });
        }
    }
});
