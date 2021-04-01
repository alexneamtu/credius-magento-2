<?php

namespace Credius\PaymentGateway\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;

abstract class CrediusConfigProvider implements ConfigProviderInterface
{
    const CODE = 'crediusmethod';
}
