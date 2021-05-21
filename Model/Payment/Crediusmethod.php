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
    ) {
        $apiKey = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_settings/api_key',
            ScopeInterface::SCOPE_DEFAULT
        );
        $callbackUrl = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_settings/callback_url',
            ScopeInterface::SCOPE_DEFAULT
        );
        $storeId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/store_settings/store_id',
            ScopeInterface::SCOPE_DEFAULT
        );
        $storeCui = $this->_scopeConfig->getValue(
            'payment/crediusmethod/store_settings/store_cui',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_id',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_name',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationCountry = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_country',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationDistrict = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_district',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationCity = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_city',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationStreet = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_street',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationStreetNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_street_number',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationBuildingNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_building_number',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationStairNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_stair_number',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationFloorNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_floor_number',
            ScopeInterface::SCOPE_DEFAULT
        );
        $locationApartmanetNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_apartment_number',
            ScopeInterface::SCOPE_DEFAULT
        );
        $userId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_id',
            ScopeInterface::SCOPE_DEFAULT
        );
        $userCnp = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_cnp',
            ScopeInterface::SCOPE_DEFAULT
        );
        $userFirstName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_first_name',
            ScopeInterface::SCOPE_DEFAULT
        );
        $userLastName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_last_name',
            ScopeInterface::SCOPE_DEFAULT
        );
        $userIdentityCard = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_identity_card',
            ScopeInterface::SCOPE_DEFAULT
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
