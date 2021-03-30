<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 2.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

namespace Credius\PaymentGateway\Controller\Webhook;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Registry;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

class Receiver extends Action implements CsrfAwareActionInterface
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

    /** @var  Order */
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

        if (!empty($response)) {
            $decodedResponse = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                parse_str($response, $decodedResponse);
            }

            if (
                !is_array($decodedResponse) ||
                !isset($decodedResponse['OrderId']) ||
                !isset($decodedResponse['StatusId'])
            ) {
                $this->logger->critical('Credius - Invalid notification format: ' . $response);
                echo "1";
                exit();
            }
        }

        $order = $this->getOrder($decodedResponse['OrderId']);
        if (!$order) {
            $this->logger->critical('Credius - Invalid notification with orderId: ' . $decodedResponse['OrderId']);
            echo "2";
            exit();
        }

        switch ($decodedResponse['StatusId']) {
            case 1:
                $history = $this->historyFactory->create();
                $history->setParentId($decodedResponse['OrderId'])->setComment('Credius credit request submitted.')
                    ->setEntityName('order')
                    ->setStatus(Order::STATE_PENDING_PAYMENT);
                $this->historyRepository->save($history);
                $this->order
                    ->setState(Order::STATE_PENDING_PAYMENT, true)
                    ->setStatus(Order::STATE_PENDING_PAYMENT, true);
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
                $history->setParentId($decodedResponse['OrderId'])->setComment('Credius credit request approved.')
                    ->setEntityName('order')
                    ->setStatus(Order::STATE_PROCESSING);
                $this->historyRepository->save($history);
                $this->order
                    ->setState(Order::STATE_PROCESSING, true)
                    ->setStatus(Order::STATE_PROCESSING, true);
                $this->orderRepository->save($this->order);
                break;
            case 3:
                $history = $this->historyFactory->create();
                $history->setParentId($decodedResponse['OrderId'])->setComment('Credius credit request denied.')
                    ->setEntityName('order')
                    ->setStatus(Order::STATE_CANCELED);
                $this->historyRepository->save($history);
                $this->order->cancel();
                $this->order
                    ->setState(Order::STATE_CANCELED, true)
                    ->setStatus(Order::STATE_CANCELED, true);
                $this->orderRepository->save($this->order);
                break;
            case 4:
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
                $history->setParentId($decodedResponse['OrderId'])->setComment('Credius credit request completed.')
                    ->setEntityName('order')
                    ->setStatus(Order::STATE_COMPLETE);
                $this->historyRepository->save($history);
                $this->order
                    ->setState(Order::STATE_COMPLETE, true)
                    ->setStatus(Order::STATE_COMPLETE, true);
                $this->orderRepository->save($this->order);
                break;
            case 5:
                $history = $this->historyFactory->create();
                $history->setParentId($decodedResponse['OrderId'])->setComment('Credius credit request canceled by the client.')
                    ->setEntityName('order')
                    ->setStatus(Order::STATE_CANCELED);
                $this->historyRepository->save($history);
                $this->order->cancel();
                $this->order
                    ->setState(Order::STATE_CANCELED, true)
                    ->setStatus(Order::STATE_CANCELED, true);
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

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
