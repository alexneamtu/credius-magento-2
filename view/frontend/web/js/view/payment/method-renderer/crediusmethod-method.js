define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/totals',
        'ko'
    ],
    function (
        $,
        Component,
        url,
        customerData,
        errorProcessor,
        fullScreenLoader,
        totals,
        ko
    ) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Credius_PaymentGateway/payment/crediusmethod',
                paymentTitles: 'Credius PAY',
                // applicantTypes: '',
                // loanTypes: '',
                // requestTypes: '',
                // requestSourceTypes: ''
            },
            isDisplayMessage: ko.observable(false),
            initialize: function () {
                this._super();
                var self = this;

                if (totals.getSegment('grand_total').value < 200 || totals.getSegment('grand_total').value > 20000) {
                    self.isDisplayMessage(true);
                }

                return this;
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function () {
                var custom_controller_url = url.build('credius/start/index');
                var selectFields = [
                    'RequestTypeId',
                    'ApplicantTypeId',
                    // 'RequestSourceId',
                    'LoanTypeId'
                ];
                var selectFieldsValues = {
                    ApplicantTypeId: this.getApplicantType(),
                    RequestTypeId: this.getRequestType(),
                    LoanTypeId: this.getLoanType(),
                };
                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        const action = response.action;
                        delete response.action;
                        let html = '<form action="' + action + '" method="post" id="credius">';
                        for (const name in response){
                            if (response.hasOwnProperty(name)) {
                                const value = selectFields.includes(name) ? selectFieldsValues[name] : response[name];
                                if ( typeof value != 'object') {
                                    html += '  <input type="hidden" name="' + name + '" value="' + value + '" />';
                                } else {
                                    if (Array.isArray(value)) {
                                        value.forEach(function (element, index) {
                                            for (const fieldKey in element) {
                                                if (element.hasOwnProperty(fieldKey)) {
                                                    const fieldValue = selectFields.includes(fieldKey) ? selectFieldsValues[fieldKey] : element[fieldKey];
                                                    html += ' <input type="hidden" name="' + name + '[' + index + '][' + fieldKey + ']" value="' + fieldValue + '"/>';
                                                }
                                            }
                                        });
                                    } else {
                                        for (const fieldKey in value) {
                                            if (value.hasOwnProperty(fieldKey)) {
                                                const fieldValue = selectFields.includes(fieldKey) ? selectFieldsValues[fieldKey] : value[fieldKey];
                                                html += ' <input type="hidden" name="' + name + '[' + fieldKey + ']" value="' + fieldValue + '"/>';
                                            }
                                        }
                                    }
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
			},
            getPaymentTitle: function() {
                return window.checkoutConfig.payment.crediusmethod.paymentTitles;
            },
            getCode: function() {
                return 'crediusmethod';
            },
            // getLoanType: function() {
            //     return this.loanTypes();
            // },
            // getRequestType: function() {
            //     return this.requestTypes();
            // },
            // getApplicantType: function() {
            //     return this.applicantTypes();
            // },
            initObservable: function () {
                this._super()
                    .observe([
                        // 'applicantTypes',
                        // 'loanTypes',
                        // 'requestTypes',
                        // 'requestSourceTypes',
                        'paymentTitles'
                    ]);

                return this;
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        // 'applicantTypes': this.applicantTypes(),
                        // 'loanTypes': this.loanTypes(),
                        // 'requestTypes': this.requestTypes(),
                        // 'requestSourceTypes': this.requestSourceTypes(),
                        'paymentTitles': this.paymentTitles(),
                    }
                };
            },
            // getApplicantTypes: function() {
            //     return _.map(window.checkoutConfig.payment.crediusmethod.applicantTypes, function(value, key) {
            //         return {
            //             'value': key,
            //             'applicant_type': value
            //         }
            //     });
            // },
            // getLoanTypes: function() {
            //     return _.map(window.checkoutConfig.payment.crediusmethod.loanTypes, function(value, key) {
            //         return {
            //             'value': key,
            //             'loan_type': value
            //         }
            //     });
            // },
            // getRequestTypes: function() {
            //     return _.map(window.checkoutConfig.payment.crediusmethod.requestTypes, function(value, key) {
            //         return {
            //             'value': key,
            //             'request_type': value
            //         }
            //     });
            // },
            // getRequestSourceTypes: function() {
            //     return _.map(window.checkoutConfig.payment.crediusmethod.requestSourceTypes, function(value, key) {
            //         return {
            //             'value': key,
            //             'request_source_type': value
            //         }
            //     });
            // },
        });
    }
);
