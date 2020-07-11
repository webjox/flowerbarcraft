<?php

namespace console\controllers;

use common\components\product\helpers\CompareHelper;
use common\components\product\helpers\SaveHelper;
use common\components\product\models\ProductModel;
use common\components\product\models\ProductOfferImageModel;
use common\components\product\models\ProductOfferModel;
use common\components\retailcrm\RetailCrm;
use common\components\site\models\SiteModel;
use Exception;
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
     */
    public function actionSites()
    {
        try {
            Yii::info("Старт процесса: Синхронизация магазинов", 'retailcrm');
            $sitesList = RetailCrm::getSites();
            if (empty($sitesList)) {
                Yii::info("Ошибка при выполнении процесса: Синхронизация магазинов\nНе удалось получить данные из RetailCRM", 'retailcrm');
                return;
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
                    Yii::$app->db->createCommand()->update('site', [
                        'active' => false,
                        'updated_at' => time()
                    ], 'code = :code', [':code' => $code])->execute();
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
                return;
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
}
