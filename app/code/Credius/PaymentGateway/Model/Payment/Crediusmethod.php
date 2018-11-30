<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 1.0.0
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
        if (!$partnerCode || !$apiKey || !$publicKey) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}
