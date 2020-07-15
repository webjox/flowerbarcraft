<?php

namespace common\components\retailcrm;

use common\components\settings\models\SettingsModel;
use common\components\settings\services\Settings;
use RetailCrm\ApiClient;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class RetailCrm
 * @package common\components\retailcrm
 */
class RetailCrm
{
    /**
     * @return ApiClient
     * @throws InvalidConfigException
     */
    public static function getInstance()
    {
        /* @var $settings Settings */
        $settings = Yii::$app->get('settings');
        $url = $settings->get(SettingsModel::PARAM_CRM_URL);
        $key = $settings->get(SettingsModel::PARAM_CRM_API_KEY);

        return new ApiClient($url, $key);
    }

    /**
     * Получение списка файлов по id заказа в retailCRM
     * @param $crmOrderId
     * @return array|null
     * @throws InvalidConfigException
     */
    public static function getFiles($crmOrderId)
    {
        $client = self::getInstance();
        $response = $client->request->filesList(['orderIds' => [$crmOrderId]]);
        if ($response->getStatusCode() != 200 || !$response->isSuccessful()) {
            return null;
        }

        if (empty($response->getResponse()['files'])) {
            return [];
        }

        return $response->getResponse()['files'];
    }

    /**
     * Получение списка магазинов
     * @return array|null
     */
    public static function getSites()
    {
        $client = self::getInstance();
        $response = $client->request->sitesList();
        if ($response->getStatusCode() != 200 || !$response->isSuccessful() || empty($response->getResponse()['sites'])) {
            return null;
        }

        return $response->getResponse()['sites'];
    }

    /**
     * Получение списка статусов
     * @return array|null
     */
    public static function getStatuses()
    {
        $client = self::getInstance();
        $response = $client->request->statusesList();
        if ($response->getStatusCode() != 200 || !$response->isSuccessful() || empty($response->getResponse()['statuses'])) {
            return null;
        }

        return $response->getResponse()['statuses'];
    }

    /**
     * Получение списка товаров
     * @return array|null
     */
    public static function getProducts()
    {
        $client = self::getInstance();
        $filter = [];
        $currentPage = 1;
        $limit = 100;
        $products = [];

        do {
            $response = $client->request->storeProducts($filter, $currentPage, $limit);
            if ($response->getStatusCode() != 200 || !$response->isSuccessful() || empty($response->getResponse()['products'])) {
                return null;
            }
            $pageCount = $response->getResponse()['pagination']['totalPageCount'] ?? 1;
            $products = array_merge($products, $response->getResponse()['products']);
            $currentPage++;
            sleep(1);
        } while ($currentPage <= $pageCount);

        return $products;
    }
}
