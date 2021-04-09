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
            ScopeInterface::SCOPE_WEBSITE
        );
        $callbackUrl = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_settings/callback_url',
            ScopeInterface::SCOPE_WEBSITE
        );
        $storeId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/store_settings/store_id',
            ScopeInterface::SCOPE_WEBSITE
        );
        $storeCui = $this->_scopeConfig->getValue(
            'payment/crediusmethod/store_settings/store_cui',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_id',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_name',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationCountry = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_country',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationDistrict = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_district',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationCity = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_city',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationStreet = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_street',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationStreetNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_street_number',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationBuildingNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_building_number',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationStairNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_stair_number',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationFloorNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_floor_number',
            ScopeInterface::SCOPE_WEBSITE
        );
        $locationApartmanetNumber = $this->_scopeConfig->getValue(
            'payment/crediusmethod/location_settings/location_apartment_number',
            ScopeInterface::SCOPE_WEBSITE
        );
        $userId = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_id',
            ScopeInterface::SCOPE_WEBSITE
        );
        $userCnp = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_cnp',
            ScopeInterface::SCOPE_WEBSITE
        );
        $userFirstName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_first_name',
            ScopeInterface::SCOPE_WEBSITE
        );
        $userLastName = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_last_name',
            ScopeInterface::SCOPE_WEBSITE
        );
        $userIdentityCard = $this->_scopeConfig->getValue(
            'payment/crediusmethod/user_settings/user_identity_card',
            ScopeInterface::SCOPE_WEBSITE
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
