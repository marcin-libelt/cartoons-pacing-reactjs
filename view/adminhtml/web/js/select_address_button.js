/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
define([
    'jquery',
    'asnCreator_address'
], function ($, address) {

    return function (config, button) {
        return address.initSelectAddressButton(config, button);
    };
});
