/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
define([
    'jquery',
    'asnCreator_factory'
], function ($, factory) {

    return function (config, button) {
        return factory.initSelectFactoryButton(config, button);
    };
});
