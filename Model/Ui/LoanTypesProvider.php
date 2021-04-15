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
 * Class LoanTypesProvider
 */
class LoanTypesProvider implements ConfigProviderInterface
{
    const CODE = 'crediusmethod';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $loanTypesResponse = file_get_contents('https://apigw.credius.ro/dictionaries/GetLoanTypes');
        $loanTypes = json_decode($loanTypesResponse);
        $result = [];
        foreach ($loanTypes as $value) {
            $result[$value->Id] = $value->Name;
        }
        return [
            'payment' => [self::CODE => ['loanTypes' => $result]]
        ];
    }
}
