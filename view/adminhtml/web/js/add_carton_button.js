/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
define([
    'jquery',
    'asnCreator_carton'
], function ($, carton) {

    return function (config, button) {
        return carton.initAddCartonButton(config, button);
    };
});
