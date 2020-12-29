<?php

namespace console\controllers;

use common\components\order\models\OrderUpdateQueueModel;
use common\components\product\helpers\CompareHelper;
use common\components\product\helpers\SaveHelper;
use common\components\product\models\ProductModel;
use common\components\product\models\ProductOfferImageModel;
use common\components\product\models\ProductOfferModel;
use common\components\retailcrm\RetailCrm;
use common\components\settings\models\StatusModel;
use common\components\site\models\SiteModel;
use Exception;
use Throwable;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Class RetailCrmController
 * @package console\controllers
 */
class RetailCrmController extends Controller
{
    /**
     * Синхронизация магазинов
     * @return int
     */
    public function actionSites()
    {
        try {
            Yii::info("Старт процесса: Синхронизация магазинов", 'retailcrm');
            $sitesList = RetailCrm::getSites();
            if (empty($sitesList)) {
                Yii::info("Ошибка при выполнении процесса: Синхронизация магазинов\nНе удалось получить данные из RetailCRM", 'retailcrm');
                return ExitCode::OK;
            }

            $addingData = [];
            $retailCrmSiteCodeList = [];
            $addCount = 0;
            $ignoreCount = 0;
            $updateCount = 0;
            $siteCodeList = SiteModel::find()->select(['name', 'active', 'code'])->indexBy('code')->asArray()->all();
            $siteCodeListActive = SiteModel::find()->select(['name', 'code'])->where(['active' => true])->indexBy('code')->column();

            foreach ($sitesList as $site) {
                $retailCrmSiteCodeList[] = $site['code'];
                if (key_exists($site['code'], $siteCodeList)) {
                    if ($site['name'] != $siteCodeList[$site['code']]['name'] || !$siteCodeList[$site['code']]['active']) {
                        Yii::$app->db->createCommand()->update('site', [
                            'name' => $site['name'],
                            'active' => true,
                            'updated_at' => time(),
                        ], 'code = :code', [':code' => $site['code']])->execute();
                        $updateCount++;
                    } else {
                        $ignoreCount++;
                    }
                } else {
                    $addingData[] = [
                        'name' => $site['name'],
                        'code' => $site['code'],
                        'active' => true,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    $addCount++;
                }
            }

            if (count($addingData) > 0) {
                Yii::$app
                    ->db
                    ->createCommand()
                    ->batchInsert('site', ['name', 'code', 'active', 'created_at', 'updated_at'], $addingData)
                    ->execute();
            }

            foreach ($siteCodeListActive as $code => $name) {
                if (!in_array($code, $retailCrmSiteCodeList)) {
                    $disabledSiteId = SiteModel::find()->select('id')->where(['code' => $code])->scalar();
                    Yii::$app->db->createCommand()->update('site', [
                        'is_main' => false,
                        'is_denial' => false,
                        'timezone' => null,
                        'probability' => null,
                        'parent_id' => null,
                        'active' => false,
                        'updated_at' => time()
                    ], 'code = :code', [':code' => $code])->execute();
                    if ($disabledSiteId) {
                        Yii::$app->db->createCommand()->update('site', [
                            'timezone' => null,
                            'probability' => null,
                            'parent_id' => null,
                            'updated_at' => time()
                        ], 'parent_id = :id', [':id' => $disabledSiteId])->execute();
                    }
                    $updateCount++;
                }
            }

            Yii::info("Завершение процесса: Синхронизация магазинов\nКоличество добавленных записей: {$addCount}\nКоличество обновленных записей: {$updateCount}\nКоличество проигнорированных записей: {$ignoreCount}\n", 'retailcrm');
        } catch (Exception $e) {
            Yii::info("Ошибка при выполнении процесса: Синхронизация магазинов\n{$e->getMessage()}", 'retailcrm');
        }

        return ExitCode::OK;
    }

    /**
     * Синхронизация товаров
     */
    public function actionProducts()
    {
        try {
            Yii::info("Старт процесса: Синхронизация товаров", 'retailcrm');

            $products = RetailCrm::getProducts();
            if (empty($products)) {
                Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось получить данные из RetailCRM", 'retailcrm');
                return ExitCode::OK;
            }

            $addCount = 0;
            $ignoreCount = 0;
            $updateCount = 0;

            $productsData = ProductModel::find()->asArray()->indexBy('product_id')->all();

            $savedOffers = ProductOfferModel::find()->asArray()->all();
            $offersData = [];
            if ($savedOffers) {
                foreach ($savedOffers as $savedOffer) {
                    $offersData[$savedOffer['product_id']][$savedOffer['offer_id']] = $savedOffer;
                }
            }

            $savedOffersImgData = ProductOfferImageModel::find()->asArray()->all();
            $offersImgData = [];
            if ($savedOffersImgData) {
                foreach ($savedOffersImgData as $img) {
                    $offersImgData[$img['offer_id']][] = $img['image_url'];
                }
            }

            foreach ($products as $product) {
                if (key_exists($product['id'], $productsData)) { // товар уже есть в БД
                    $isUpdated = false;
                    $savedProduct = $productsData[$product['id']];

                    if (!CompareHelper::productsIsEqual($product, $savedProduct)) { // в товаре есть изменения
                        $productModel = ProductModel::findOne(['id' => $savedProduct['id']]);
                        if (!$productModel || !SaveHelper::saveProduct($productModel, $product)) {
                            Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить изменения в товаре с id: {$product['id']}", 'retailcrm');
                            continue;
                        }
                        $isUpdated = true;
                    }

                    // синхронизация торговых предложений
                    if (!empty($product['offers'])) {
                        $savedOffers = isset($offersData[$savedProduct['id']]) ? $offersData[$savedProduct['id']] : [];
                        foreach ($product['offers'] as $offer) {
                            if (empty($offer['id'])) {
                                continue;
                            }
                            $savedOffer = isset($savedOffers[$offer['id']]) ? $savedOffers[$offer['id']] : null;
                            $offerModel = null;
                            if (!$savedOffer) { // торгового предложения еще нет в БД
                                $offerModel = SaveHelper::saveOffer(new ProductOfferModel(), $offer, $savedProduct['id']);
                                if (!$offerModel) {
                                    Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить торговое предложение с id: {$offer['id']}", 'retailcrm');
                                    continue;
                                }
                                $isUpdated = true;
                            } elseif (!CompareHelper::offersIsEqual($offer, $savedOffer)) { // торговое предложение есть в БД и в crm по нему есть изменения
                                $offerModel = ProductOfferModel::findOne(['id' => $savedOffer['id']]);
                                if (!$offerModel || !SaveHelper::saveOffer($offerModel, $offer, $savedProduct['id'])) {
                                    Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить изменения в торговом предложении с id: {$offer['id']}", 'retailcrm');
                                    continue;
                                }
                                $isUpdated = true;
                            }

                            // синхронизация изображений
                            if (!empty($offer['images'])) {
                                $offerId = !empty($offerModel->id) ? $offerModel->id : ($savedOffer['id'] ?? null);
                                if (!$offerId) {
                                    Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось синхронизировать изображения к торговому предложению с id: {$offer['id']}", 'retailcrm');
                                    continue;
                                }
                                foreach ($offer['images'] as $image) {
                                    if (!isset($offersImgData[$offerId]) || !in_array($image, $offersImgData[$offerId])) {
                                        if (!SaveHelper::saveOfferImage(new ProductOfferImageModel(), $image, $offerId)) {
                                            Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить изображение к торговому предложению с id: {$offer['id']}", 'retailcrm');
                                            continue;
                                        }
                                        $isUpdated = true;
                                    }
                                }
                            }
                        }
                    }

                    if ($isUpdated) {
                        $updateCount++;
                    } else {
                        $ignoreCount++;
                    }
                } else { // товар еще не добавлен в БД
                    if (empty($product['id'])) {
                        $ignoreCount++;
                        continue;
                    }

                    $productModel = SaveHelper::saveProduct(new ProductModel(), $product);
                    if ($productModel) {
                        if (!empty($product['offers'])) {
                            foreach ($product['offers'] as $offer) {
                                if (empty($offer['id'])) {
                                    continue;
                                }

                                $offerModel = SaveHelper::saveOffer(new ProductOfferModel(), $offer, $productModel->id);
                                if ($offerModel) {
                                    if (!empty($offer['images'])) {
                                        foreach ($offer['images'] as $image) {
                                            $offerImgModel = new ProductOfferImageModel([
                                                'offer_id' => $offerModel->id,
                                                'image_url' => $image,
                                            ]);

                                            if (!$offerImgModel->save(false)) {
                                                Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить изображение к торговому предложению с id: {$offer['id']}", 'retailcrm');
                                            }
                                        }
                                    }
                                } else {
                                    Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить торговое предложение с id: {$offer['id']}", 'retailcrm');
                                }
                            }
                        }

                        $addCount++;
                    } else {
                        Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\nНе удалось сохранить товар с id: {$product['id']}", 'retailcrm');
                    }
                }
            }

            Yii::info("Завершение процесса: Синхронизация товаров\nКоличество добавленных записей: {$addCount}\nКоличество обновленных записей: {$updateCount}\nКоличество проигнорированных записей: {$ignoreCount}\n", 'retailcrm');
        } catch (Exception $e) {
            Yii::info("Ошибка при выполнении процесса: Синхронизация товаров\n{$e->getMessage()}", 'retailcrm');
        }

        return ExitCode::OK;
    }

    /**
     * Синхронизация статусов
     * @return int
     */
    public function actionStatuses()
    {
        try {
            Yii::info("Старт процесса: Синхронизация статусов", 'retailcrm');
            $statuses = RetailCrm::getStatuses();
            if (empty($statuses)) {
                Yii::info("Ошибка при выполнении процесса: Синхронизация статусов\nНе удалось получить данные из RetailCRM", 'retailcrm');
                return ExitCode::OK;
            }

            $addingData = [];
            $retailCrmStatusCodes = [];
            $addCount = 0;
            $ignoreCount = 0;
            $updateCount = 0;
            $statusList = StatusModel::find()->indexBy('code')->asArray()->all();

            foreach ($statuses as $status) {
                $retailCrmStatusCodes[] = $status['code'];
                if (key_exists($status['code'], $statusList)) { // статус уже есть в БД
                    $savedStatus = $statusList[$status['code']];
                    // если есть изменения
                    if ($status['name'] != $savedStatus['name'] || $status['active'] != $savedStatus['active'] || $status['ordering'] != $savedStatus['ordering']) {
                        $data = [
                            'name' => $status['name'],
                            'active' => $status['active'],
                            'ordering' => $status['ordering'],
                        ];
                        if (!$status['active']) {
                            $data['available'] = false;
                        }
                        Yii::$app->db->createCommand()->update('status', $data, 'code = :code', [':code' => $status['code']])->execute();
                        $updateCount++;
                    } else {
                        $ignoreCount++;
                    }
                } else { // статуса еще нет в БД
                    $addingData[] = [
                        'code' => $status['code'],
                        'name' => $status['name'],
                        'ordering' => $status['ordering'],
                        'active' => $status['active'],
                        'available' => false,
                    ];
                    $addCount++;
                }
            }

            if (count($addingData) > 0) {
                Yii::$app
                    ->db
                    ->createCommand()
                    ->batchInsert('status', ['code', 'name', 'ordering', 'active', 'available'], $addingData)
                    ->execute();
            }

            // если статус сохранен, но в retailCRM такого нет
            foreach ($statusList as $item) {
                if (!in_array($item['code'], $retailCrmStatusCodes)) {
                    Yii::$app->db->createCommand()->update('status', [
                        'active' => false,
                        'available' => false,
                    ], 'code = :code', [':code' => $item['code']])->execute();
                    $updateCount++;
                }
            }

            Yii::info("Завершение процесса: Синхронизация статусов\nКоличество добавленных записей: {$addCount}\nКоличество обновленных записей: {$updateCount}\nКоличество проигнорированных записей: {$ignoreCount}\n", 'retailcrm');
        } catch (Exception $e) {
            Yii::info("Ошибка при выполнении процесса: Синхронизация статусов\n{$e->getMessage()}", 'retailcrm');
        }

        return ExitCode::OK;
    }

    /**
     * Очередь заказов на изменение статуса
     * @throws Throwable
     */
    public function actionOrderQueue()
    {
        try {
            $queue = OrderUpdateQueueModel::find()->all();
            $crm = RetailCrm::getInstance();
            if (!empty($queue)) {
                foreach ($queue as $item) {
                    /* @var $item OrderUpdateQueueModel */
                    $order = $item->order ?? null;
                    $site = $order->site->code ?? null;
                    $status = $item->status->code ?? null;
                    if ($order && $site && $status) {
                        Yii::info("Старт процесса: Изменение статуса у заказа #{$order->crm_id}", 'retailcrm');
                        $resp = $crm->request->ordersEdit([
                            'id' => $order->crm_id,
                            'status' => $status,
                        ], 'id', $site);
                        if ($resp->isSuccessful()) {
                            $item->delete();
                            Yii::info("Завершение процесса: Изменение статуса у заказа #{$order->crm_id}\nСтатус: success", 'retailcrm');
                        } else {
                            Yii::info("Завершение процесса: Изменение статуса у заказа #{$order->crm_id}\nСтатус: fail", 'retailcrm');
                        }
                        sleep(1);
                    }
                }
            }
        } catch (Exception $e) {
            Yii::info("Ошибка при выполнении процесса: Изменение статуса у заказа\n{$e->getMessage()}", 'retailcrm');
        }
    }
}
