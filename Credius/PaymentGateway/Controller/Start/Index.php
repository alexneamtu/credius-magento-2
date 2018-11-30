<?php
/**
 * Credius
 */
namespace Credius\PaymentGateway\Controller\Start;
use Magento\Store\Model\ScopeInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->storeManagerInterface = $storeManager;
        parent::__construct($context);

    }
    /**
     * Start checkout by Credius preparing the submit form data.
     */
    public function execute()
    {
        $partnerCode = $this->scopeConfig->getValue('payment/crediusmethod/partner_code', ScopeInterface::SCOPE_STORE);
        $apiKey = $this->scopeConfig->getValue('payment/crediusmethod/api_key', ScopeInterface::SCOPE_STORE);
        $publicKey = $this->scopeConfig->getValue('payment/crediusmethod/public_key', ScopeInterface::SCOPE_STORE);

        $base_url = $this->storeManagerInterface->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            true
        );


        $order = $this->checkoutSession->getLastRealOrder();

        $clientDetails = array(
            "api_key" => $apiKey,
            "client_firstname" => $order->getBillingAddress()->getFirstName(),
            "client_lastname" => $order->getBillingAddress()->getLastname(),
            "client_email" => $order->getCustomerEmail(),
            "client_phone" => $order->getBillingAddress()->getTelephone(),
            "order_id" => $order->getIncrementId(),
            "nonce" => time(),
            "secure_callback" => false,
            "callback_status_url" => $this->_url->getUrl("credius/webhook/receiver"),
        );

        openssl_public_encrypt(json_encode($clientDetails), $rawToken, $publicKey);

        $token = base64_encode($rawToken);

        $products = array();
        $items = $order->getAllItems();
        foreach ($items as $item) {
            $product = $this->productRepositoryFactory->create()->getById($item->getProductId());

            $products[] = array(
                'name' => $item->getName(),
                'quantity' => (int)$item->getQtyOrdered(),
                'value' => $item->getPrice(),
                'code' => $item->getSku(),
                'image' => $product->getData('small_image') ? $base_url . 'pub/media/catalog/product' . $product->getData('small_image') : ''
            );
        }

        if ($order->getShippingAmount()) {
            $products[] = array(
                'name' => 'Shipping',
                'quantity' => 1,
                'value' => $order->getShippingAmount(),
                'code' => 'shipping',
                'image' => ''
            );
        }

        $cartDetails = array(
            "action" => 'https://partnershopqa.credius.ro/' . $partnerCode,
            "token" => $token,
            "total_amount" => strval(floatval($order->getTotalDue())),
            "products" => $products,
            "callback_return_url_success" => $this->_url->getUrl('checkout/onepage/success'),
            "callback_return_url_rejected" => $this->_url->getUrl('checkout/cart')
        );

        $result = $this->resultJsonFactory->create();
        return $result->setData($cartDetails);
    }
}