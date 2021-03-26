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

class Crediusmethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "crediusmethod";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $apiUrl = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $partnerCode = $this->_scopeConfig->getValue(
            'payment/crediusmethod/partner_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $apiKey = $this->_scopeConfig->getValue(
            'payment/crediusmethod/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $publicKey = $this->_scopeConfig->getValue(
            'payment/crediusmethod/public_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$apiUrl || !$partnerCode || !$apiKey || !$publicKey) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}
