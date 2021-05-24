<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 2.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

namespace Credius\PaymentGateway\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;

class Crediusmethod extends AbstractMethod
{
    protected $_code = "crediusmethod";
    protected $_isOffline = true;

    public function isAvailable(
        CartInterface $quote = null
    )
    {
        $apiKey = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_settings/api_key'
        );
        $callbackUrl = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_settings/callback_url'
        );
        $storeId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/store_settings/store_id'
        );
        $storeCui = $this->_scopeConfig->getValue(
            'payment/crediusmethod/store_settings/store_cui'
        );
        $locationId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_id'
        );
        $locationName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_name'
        );
        $locationCountry = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_country'
        );
        $locationDistrict = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_district'
        );
        $locationCity = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_city'
        );
        $locationStreet = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_street'
        );
        $locationStreetNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_street_number'
        );
        $locationBuildingNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_building_number'
        );
        $locationStairNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_stair_number'
        );
        $locationFloorNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_floor_number'
        );
        $locationApartmanetNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_apartment_number'
        );
        $userId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_id'
        );
        $userCnp = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_cnp'
        );
        $userFirstName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_first_name'
        );
        $userLastName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_last_name'
        );
        $userIdentityCard = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_identity_card'
        );


        if (
            !$apiKey ||
            !$callbackUrl ||
            !$storeId ||
            !$storeCui ||
            !$locationId ||
            !$locationName ||
            !$locationCountry ||
            !$locationDistrict ||
            !$locationCity ||
            !$locationStreet ||
            !$locationStreetNumber ||
            !$locationBuildingNumber ||
            !$locationStairNumber ||
            !$locationFloorNumber ||
            !$locationApartmanetNumber ||
            !$userId ||
            !$userCnp ||
            !$userFirstName ||
            !$userLastName ||
            !$userIdentityCard
        ) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}
