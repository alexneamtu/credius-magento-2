define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, url, customerData, errorProcessor, fullScreenLoader) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Credius_PaymentGateway/payment/crediusmethod'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function (e) {
                var custom_controller_url = url.build('credius/start/index');
                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        const action = response.action;
                        delete response.action;
                        let html = '<form action="' + action + '" method="post" id="credius">';
                        for (const name in response){
                            if (response.hasOwnProperty(name)) {
                                const value = response[name];
                                if ( typeof value != 'object') {
                                    html += '  <input type="hidden" name="' + name + '" value="' + value + '" />';
                                } else {
                                    value.forEach(function(element, index) {
                                        for (const fieldKey in element) {
                                            if (element.hasOwnProperty(fieldKey)) {
                                                const fieldValue = element[fieldKey];
                                                html += ' <input type="hidden" name="' + name + '[' + index + '][' + fieldKey + ']" value="' + fieldValue + '"/>';
                                            }
                                        }
                                    });
                                }
                            }
                        }

                        html += '</form>';

                        $('#checkout-payment-method-load').after(html);

                        $('#credius').submit();
                    })
                    .fail(function (response) {
                        errorProcessor.process(response, this.messageContainer);
                    })
                    .always(function () {
                        fullScreenLoader.stopLoader();
                    });
            },
			getPaymentImage: function () {
				var custom_image = [
				    require.toUrl('Credius_PaymentGateway/images/credius.png'),
				];
				return custom_image;
			}			
        });
    }
);
