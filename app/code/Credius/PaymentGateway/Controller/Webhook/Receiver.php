<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 1.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

namespace Credius\PaymentGateway\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Receiver extends Action
{
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $file;


    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /** @var  \Magento\Sales\Model\Order */
    private $order;


    public function __construct(
        Context $context,
        OrderRepositoryInterface $order,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $jsonResultFactory,
        LoggerInterface $logger,
        File $file,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Registry $registry,
        HistoryFactory $historyFactory,
        OrderStatusHistoryRepositoryInterface $historyRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $order;
        $this->scopeConfig = $scopeConfig;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->logger = $logger;
        $this->file = $file;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->registry = $registry;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|null
     * @throws LocalizedException
     */
    public function execute()
    {

        $response = $this->file->read('php://input');
        error_log($response);

        if (!empty($response)) {
            $decoded_response = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                parse_str($response, $decoded_response);
            }

            if (
                !is_array($decoded_response) ||
                !isset($decoded_response['partner_id']) ||
                !isset($decoded_response['order_id']) ||
                !isset($decoded_response['status_id'])
            ) {
                $this->logger->critical('Credius - Invalid notification format: ' . $response);
                echo "1";
                exit();
            }

        }

        $order = $this->getOrder($decoded_response['order_id']);
        if (!$order) {
            $this->logger->critical('Credius - Invalid notification with orderId: ' . $decoded_response['order_id']);
            echo "2";
            exit();
        }


        switch ($decoded_response['status_id']) {
            case 1:
                $history = $this->historyFactory->create();
                $history->setParentId($decoded_response['order_id'])->setComment('Credius credit request submitted.')
                    ->setEntityName('order')
                    ->setStatus(\Magento\Sales\Model\Order::STATE_HOLDED);
                $this->historyRepository->save($history);
                $this->order->hold();
                $this->orderRepository->save($this->order);
                break;
            case 2:
                $payment = $this->order->getPayment();
                $payment->setShouldCloseParentTransaction(true);
                $payment->setIsTransactionClosed(true);
                $this->orderRepository->save($this->order);

                $invoice = $this->invoiceService->prepareInvoice($this->order);
                $invoice->register();
                $this->invoiceRepository->save($invoice);
                $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->invoiceSender->send($invoice);

                $this->orderRepository->save($this->order);
                $history = $this->historyFactory->create();
                $history->setParentId($decoded_response['order_id'])->setComment('Credius credit request approved.')
                    ->setEntityName('order')
                    ->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
                $this->historyRepository->save($history);
                $this->order
                    ->setState(\Magento\Sales\Model\Order::STATE_COMPLETE, true)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE, true);
                $this->orderRepository->save($this->order);
                break;
            case 3:
                $history = $this->historyFactory->create();
                $history->setParentId($decoded_response['order_id'])->setComment('Credius credit request denied.')
                    ->setEntityName('order')
                    ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                $this->historyRepository->save($history);
                $this->order->cancel();
                $this->order
                    ->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $this->orderRepository->save($this->order);
                break;
            case 4:
                $history = $this->historyFactory->create();
                $history->setParentId($decoded_response['order_id'])->setComment('Credius credit request canceled.')
                    ->setEntityName('order')
                    ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                $this->historyRepository->save($history);
                $this->order->cancel();
                $this->order
                    ->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $this->orderRepository->save($this->order);
                break;
        }

        $this->getResponse()->setStatusHeader(200);
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->jsonResultFactory->create();
        return $result;
    }


    /**
     * @param $event
     * @return \Magento\Sales\Api\Data\OrderInterface|mixed|null
     */
    private function getOrder($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $orderId, 'eq')->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $this->order = $order = reset($orderList) ? reset($orderList) : null;
        return $order;
    }
}
