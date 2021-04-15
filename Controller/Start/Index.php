<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 2.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

namespace Credius\PaymentGateway\Controller\Start;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index implements ActionInterface
{
    /**
     * @var string
     */
    private const INITIATE_URL = 'https://collector.credius.ro';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var UrlInterface
     */
    private $_url;

    /**
     * Index constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param JsonFactory $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->storeManagerInterface = $storeManager;
        $this->_url = $context->getUrl();
    }
    /**
     * Start checkout by Credius preparing the submit form data.
     */
    public function execute(): Json
    {
        $apiKey = $this->scopeConfig->getValue('payment/crediusmethod/api_settings/api_key', ScopeInterface::SCOPE_WEBSITE);
        $storeId = $this->scopeConfig->getValue('payment/crediusmethod/store_settings/store_id', ScopeInterface::SCOPE_WEBSITE);
        $locationId = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_id', ScopeInterface::SCOPE_WEBSITE);
        $userId = $this->scopeConfig->getValue('payment/crediusmethod/user_settings/user_id', ScopeInterface::SCOPE_WEBSITE);

        $order = $this->checkoutSession->getLastRealOrder();

        $products = [];
        $items = $order->getAllItems();
        foreach ($items as $item) {
            $products[] = [
                'name' => $item->getName(),
                'code' => $item->getSku(),
                'quantity' => (int)$item->getQtyOrdered(),
                'value' => $item->getPrice(),
            ];
        }

        if ($order->getShippingAmount()) {
            $products[] = [
                'name' => 'Shipping',
                'quantity' => 1,
                'value' => $order->getShippingAmount(),
                'code' => 'shipping',
            ];
        }

        $cartDetails = [
            'action' => rtrim(self::INITIATE_URL, '/'),
            'ApiKey' => $apiKey,
            'RequestTypeId' => 1,
            'ApplicantTypeId' => 1,
            'RequestSourceId' => 11,
            'OrderID' => $order->getIncrementId(),
            'StoreId' => $storeId,
            'LocationId' => $locationId,
            'UserId' => $userId,
            'RequestData' => [
                'FirstName' => $order->getBillingAddress()->getFirstName(),
                'LastName' => $order->getBillingAddress()->getLastName(),
//                'CNP' => '1830803070014', // TODO Check this
                'ClientPhoneNumber' => $order->getBillingAddress()->getTelephone(),
                'ClientEmail' => $order->getCustomerEmail(),
                'LoanTypeId' => 1,
            ],
            'RequestGoods' => $products,
            'callback_return_url' => $this->_url->getUrl('checkout/onepage/success'),
        ];

        $result = $this->resultJsonFactory->create();
        return $result->setData($cartDetails);
    }
}
