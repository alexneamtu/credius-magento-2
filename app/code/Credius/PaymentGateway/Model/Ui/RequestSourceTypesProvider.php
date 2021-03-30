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

/**
 * Class RequestSourceTypesProvider
 */
final class RequestSourceTypesProvider implements ConfigProviderInterface
{
    const CODE = 'crediusmethod';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $requestSourceTypesResponse = file_get_contents('https://apigw.credius.ro/dev_dictionaries/GetRequestSourceTypes');
        $requestSourceTypes = json_decode($requestSourceTypesResponse);
        $result = [];
        foreach ($requestSourceTypes as $value) {
            $result[$value->Id] = $value->Name;
        }
        return [
            'payment' => [self::CODE => ['requestSourceTypes' => $result]]
        ];
    }
}
