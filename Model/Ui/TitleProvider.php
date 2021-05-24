<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 2.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

namespace Credius\PaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ApplicantTypesProvider
 */
class TitleProvider implements ConfigProviderInterface
{
    const CODE = 'crediusmethod';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Index constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $title = $this->scopeConfig->getValue('payment/crediusmethod/general_settings/title');

        return [
            'payment' => [self::CODE => ['paymentTitles' => $title]]
        ];
    }
}
