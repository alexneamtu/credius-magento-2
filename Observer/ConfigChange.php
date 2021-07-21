<?php

namespace Credius\PaymentGateway\Observer;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;


class ConfigChange implements ObserverInterface
{
    /**
     * @var string
     */
    private const REGISTRATION_URL = 'https://apigw.credius.ro/externalpartner/SetClientInfos';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        WriterInterface $configWriter,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    private function registerCallback($callbackUrl)
    {
        $response = $this->getResponse(['DataType' => 1, 'CallbackURL' => $callbackUrl]);
        if (200 !== $response['status']) {
            $this->messageManager->addErrorMessage($response['response']);
        }
    }

    private function registerStore($storeId, $storeCui)
    {
        $response = $this->getResponse(['DataType' => 2, 'StoreId' => $storeId, 'StoreCUI' => $storeCui]);
        if (200 !== $response['status']) {
            $match = preg_match('/CUI\-ul ' . $storeCui . ' a fost deja inregistrat. Id\-ul acestuia este: ' . $storeId . '/', $response['response'], $matches);
            if (!$match) {
                $this->messageManager->addErrorMessage($response['response']);
            }
        }
    }

    private function registerLocation(
        $storeId,
        $locationId,
        $locationName,
        $locationCountry,
        $locationDistrict,
        $locationCity,
        $locationStreet,
        $locationStreetNumber,
        $locationBuildingNumber,
        $locationStairNumber,
        $locationFloorNumber,
        $locationApartmentNumber
    ) {
        $response = $this->getResponse([
            'DataType' => 3,
            'StoreId' => $storeId,
            'LocationId' => $locationId,
            'LocationName' => $locationName,
            'CountryId' => $locationCountry,
            'DistrictId' => $locationDistrict,
            'CityId' => $locationCity,
            'StreetId' => $locationStreet,
            'StreetNumber' => $locationStreetNumber,
            'BuildingNumber' => $locationBuildingNumber,
            'StairNumber' => $locationStairNumber,
            'FloorNumber' => $locationFloorNumber,
            'ApartmentNumber' => $locationApartmentNumber,
        ]);
        if (200 !== $response['status']) {
            $match = preg_match('/Aceasta locatie a fost deja inregistrata/', $response['response'], $matches);
            if (!$match) {
                $this->messageManager->addErrorMessage($response['response']);
            }
        }
    }

    private function registerUser(
        $storeId,
        $locationId,
        $userId,
        $userCnp,
        $userFirstName,
        $userLastName,
        $userIdentityCard
    ) {
        $response = $this->getResponse([
            'DataType' => 4,
            'StoreId' => $storeId,
            'LocationId' => $locationId,
            'UserId' => $userId,
            'UserCNP' => $userCnp,
            'UserFirstName' => $userFirstName,
            'UserLastName' => $userLastName,
            'UserIdentityCard' => $userIdentityCard,
        ]);
        if (200 !== $response['status']) {
            $match = preg_match('/Userul a fost deja inregistrat/', $response['response'], $matches);
            if (!$match) {
                $this->messageManager->addErrorMessage($response['response']);
            }
        }
    }


    public function execute(EventObserver $observer): void
    {
        $params = $this->request->getParam('groups');
        
        $this->apiKey = $this->scopeConfig->getValue('payment/crediusmethod/api_settings/api_key');
        $callbackUrl = $this->scopeConfig->getValue('payment/crediusmethod/api_settings/callback_url');

        $storeId = $this->scopeConfig->getValue('payment/crediusmethod/store_settings/store_id');
        $storeCui = $this->scopeConfig->getValue('payment/crediusmethod/store_settings/store_cui');

        $locationId = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_id');
        $locationName = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_name');
        $locationCountry = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_country');
        $locationDistrict = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_district');
        $locationCity =$this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_city');
        $locationStreet = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_street');
        $locationStreetNumber = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_street_number');
        $locationBuildingNumber = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_building_number');
        $locationStairNumber = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_stair_number');
        $locationFloorNumber = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_floor_number');
        $locationApartmentNumber = $this->scopeConfig->getValue('payment/crediusmethod/location_settings/location_apartment_number'); 

        $userId = $this->scopeConfig->getValue('payment/crediusmethod/user_settings/user_id'); 
        $userCnp = $this->scopeConfig->getValue('payment/crediusmethod/user_settings/user_cnp'); 
        $userFirstName = $this->scopeConfig->getValue('payment/crediusmethod/user_settings/user_first_name'); 
        $userLastName = $this->scopeConfig->getValue('payment/crediusmethod/user_settings/user_last_name'); 
        $userIdentityCard = $this->scopeConfig->getValue('payment/crediusmethod/user_settings/user_identity_card'); 
    
        $this->registerCallback($callbackUrl);
        $this->registerStore($storeId, $storeCui);
        $this->registerLocation(
            $storeId,
            $locationId,
            $locationName,
            $locationCountry,
            $locationDistrict,
            $locationCity,
            $locationStreet,
            $locationStreetNumber,
            $locationBuildingNumber,
            $locationStairNumber,
            $locationFloorNumber,
            $locationApartmentNumber
        );
        $this->registerUser(
            $storeId,
            $locationId,
            $userId,
            $userCnp,
            $userFirstName,
            $userLastName,
            $userIdentityCard
        );
    }

    /**
     * @param $fields
     * @return array
     */
    private function getResponse($fields): array
    {
        $fields = json_encode($fields);
        $headers = [
            'Content-Type: application/json',
            'ApiKey: ' . $this->apiKey,
            'Content-length: ' . strlen($fields),
        ];
        error_log(print_r($headers, 1));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::REGISTRATION_URL);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $headers,
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return ['status' => $status, 'response' => $response];
    }
}
