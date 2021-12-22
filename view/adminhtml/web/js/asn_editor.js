/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
define([
    'jquery',
    'react-app'
], function ($, ReactApp) {
    'use strict';

    var editor = {
        init(config) {
            var data = {
                form_key: FORM_KEY,
                factory_id: config.factory_id
            }
            $.ajax({
                type: "GET",
                url: config.factory_get_items_url,
                dataType: 'json',
                showLoader: true,
                data: data
            }).done(function (response) {
                require(['react-app'], function (ReactApp) {
                    ReactApp.init('react-category-root', {
                        data: response,
                        factory_id: config.factory_id,
                        post_url: config.factory_post_cartons_url,
                        autosave_url: config.factory_post_cartons_autosave_url,
                        form_key: FORM_KEY,
                        jquery: $,
                        asn_id: config.asn_id
                    });
                });
            })
        },
    }

    return function (config) {
        return editor.init(config);
    };
});
