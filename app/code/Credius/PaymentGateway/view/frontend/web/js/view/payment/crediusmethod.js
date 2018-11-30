define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'crediusmethod',
                component: 'Credius_PaymentGateway/js/view/payment/method-renderer/crediusmethod-method'
            }
        );
        return Component.extend({});
    }
);