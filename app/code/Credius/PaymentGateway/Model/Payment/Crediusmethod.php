<?php
/**
 * A Magento 2 module named Credius/PaymentGateway
 * Copyright (C) 2017 Credius
 *
 * This file included in Credius/PaymentGateway is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
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
