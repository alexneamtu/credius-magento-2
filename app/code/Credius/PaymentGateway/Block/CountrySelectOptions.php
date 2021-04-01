<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 2.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

namespace Credius\PaymentGateway\Block;


use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

class CountrySelectOptions extends Field
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * CountrySelectOptions constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $countryValue = $element->getData('value');

        $districtKey = 'payment/crediusmethod/location_settings/location_district';
        $districtValue = $this->_scopeConfig->getValue($districtKey);

        $cityKey = 'payment/crediusmethod/location_settings/location_city';
        $cityValue = $this->_scopeConfig->getValue($cityKey);

        $streetKey = 'payment/crediusmethod/location_settings/location_street';
        $streetValue = $this->_scopeConfig->getValue($streetKey);

        $name = "groups[crediusmethod][groups][location_settings][fields][location_country][value]";
        $countryId = "payment_us_crediusmethod_location_settings_location_country";
        $districtId = "payment_us_crediusmethod_location_settings_location_district";
        $cityId = "payment_us_crediusmethod_location_settings_location_city";
        $streetId = "payment_us_crediusmethod_location_settings_location_street";

        $html = $this->getLayout()
            ->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName($name)
            ->setId($countryId)
            ->setValue($countryValue)
            ->setOptions([])
            ->setExtraParams('data-validate="{\'validate-select\':true}"')
            ->getHtml();

        $html .= '<script type="text/javascript">
           require(["jquery"], function ($) {
                $(document).ready(function () {
                    var countryElement = $("#' . $countryId . '");
                    var districtElement = $("#' . $districtId . '");
                    var cityElement = $("#' . $cityId . '");
                    var streetElement = $("#' . $streetId . '");

                    var countryValue = "' . $countryValue . '";
                    var districtValue = "' . $districtValue . '";
                    var cityValue = "' . $cityValue . '";
                    var streetValue = "' . $streetValue . '";

                    function loadSelectOptions(source, selectElement, selectValue, triggerChange) {
                        var options = $.map(
                            source,
                            function (element) {
                                return $("<option/>").attr("value", element.Id).text(element.Name);
                            },
                        );
                        var select = selectElement.empty().append(options);
                        select.val(selectValue);
                        if (triggerChange) {
                            selectElement.trigger("change");
                        }
                    }

                    function getDictionaryValues(url, element, value, triggerChange) {
                        $.get( url , function( results ) {
                            loadSelectOptions(results, element, value, triggerChange);
                        });
                    }

                    function init() {
                        getDictionaryValues(
                            "https://apigw.credius.ro/dev_dictionaries/GetCountries",
                            countryElement,
                            countryValue,
                            false,
                            loadSelectOptions,
                        );

                        if (countryValue){
                            getDictionaryValues(
                                "https://apigw.credius.ro/dev_dictionaries/GetDistricts/" + countryValue,
                                districtElement,
                                districtValue,
                                false,
                                loadSelectOptions,
                            );
                        }

                        if (districtValue){
                            getDictionaryValues(
                                "https://apigw.credius.ro/dev_dictionaries/GetLocalities/" + districtValue,
                                cityElement,
                                cityValue,
                                false,
                                loadSelectOptions,
                            );
                        }

                        if (cityValue){
                            getDictionaryValues(
                                "https://apigw.credius.ro/dev_dictionaries/GetStreets/" + cityValue,
                                streetElement,
                                streetValue,
                                false,
                                loadSelectOptions,
                            );
                        }
                    }

                    init();

                    countryElement.change(function() {
                        var selectedValue = $(this).val();
                        if (selectedValue) {
                            getDictionaryValues(
                                "https://apigw.credius.ro/dev_dictionaries/GetDistricts/" + selectedValue,
                                districtElement,
                                selectedValue,
                                true,
                                loadSelectOptions,
                            );
                        } else {
                            districtElement.empty().trigger("change");
                        }
                    });

                    districtElement.change(function() {
                        var selectedValue = $(this).val();
                        if (selectedValue) {
                            getDictionaryValues(
                                "https://apigw.credius.ro/dev_dictionaries/GetLocalities/" + selectedValue,
                                cityElement,
                                selectedValue,
                                true,
                                loadSelectOptions,
                            );
                        } else {
                            cityElement.empty().trigger("change");
                        }
                    });

                    cityElement.change(function() {
                        var selectedValue = $(this).val();
                        if (selectedValue) {
                            getDictionaryValues(
                                "https://apigw.credius.ro/dev_dictionaries/GetStreets/" + selectedValue,
                                streetElement,
                                selectedValue,
                                true,
                                loadSelectOptions,
                            );
                        } else {
                            streetElement.empty().trigger("change");
                        }
                    });
                });
            });
        </script>';

        return $html;
    }
}
