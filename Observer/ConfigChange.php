<?php

namespace Credius\PaymentGateway\Observer;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class ConfigChange implements ObserverInterface
{
    /**
     * @var string
     */
    private const REGISTRATION_URL = 'https://apigw.credius.ro/dev_externalpartner/SetClientInfos';

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        WriterInterface $configWriter,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
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
        error_log(print_r([
            'DataType' => 4,
            'StoreId' => $storeId,
            'LocationId' => $locationId,
            'UserId' => $userId,
            'UserCNP' => $userCnp,
            'UserFirstName' => $userFirstName,
            'UseLastName' => $userLastName,
            'UserIdentityCard' => $userIdentityCard,
        ], 1));
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

        $this->apiKey = $params['crediusmethod']['groups']['api_settings']['fields']['api_key']['value'];
        $callbackUrl = $params['crediusmethod']['groups']['api_settings']['fields']['callback_url']['value'];

        $storeId = $params['crediusmethod']['groups']['store_settings']['fields']['store_id']['value'];
        $storeCui = $params['crediusmethod']['groups']['store_settings']['fields']['store_cui']['value'];

        $locationId = $params['crediusmethod']['groups']['location_settings']['fields']['location_id']['value'];
        $locationName = $params['crediusmethod']['groups']['location_settings']['fields']['location_name']['value'];
        $locationCountry = $params['crediusmethod']['groups']['location_settings']['fields']['location_country']['value'];
        $locationDistrict = $params['crediusmethod']['groups']['location_settings']['fields']['location_district']['value'];
        $locationCity = $params['crediusmethod']['groups']['location_settings']['fields']['location_city']['value'];
        $locationStreet = $params['crediusmethod']['groups']['location_settings']['fields']['location_street']['value'];
        $locationStreetNumber = $params['crediusmethod']['groups']['location_settings']['fields']['location_street_number']['value'];
        $locationBuildingNumber = $params['crediusmethod']['groups']['location_settings']['fields']['location_building_number']['value'];
        $locationStairNumber = $params['crediusmethod']['groups']['location_settings']['fields']['location_stair_number']['value'];
        $locationFloorNumber = $params['crediusmethod']['groups']['location_settings']['fields']['location_floor_number']['value'];
        $locationApartmentNumber = $params['crediusmethod']['groups']['location_settings']['fields']['location_apartment_number']['value'];

        $userId = $params['crediusmethod']['groups']['user_settings']['fields']['user_id']['value'];
        $userCnp = $params['crediusmethod']['groups']['user_settings']['fields']['user_cnp']['value'];
        $userFirstName = $params['crediusmethod']['groups']['user_settings']['fields']['user_first_name']['value'];
        $userLastName = $params['crediusmethod']['groups']['user_settings']['fields']['user_last_name']['value'];
        $userIdentityCard = $params['crediusmethod']['groups']['user_settings']['fields']['user_identity_card']['value'];

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
