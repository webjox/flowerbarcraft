<?php

namespace common\components\product\helpers;

use common\components\product\models\ProductModel;
use common\components\product\models\ProductOfferImageModel;
use common\components\product\models\ProductOfferModel;

/**
 * Class SaveHelper
 * @package common\components\product\helpers
 */
class SaveHelper
{
    /**
     * @param ProductModel $model
     * @param array $data
     * @return ProductModel|null
     */
    public static function saveProduct(ProductModel $model, array $data)
    {
        $model->setAttributes([
            'product_id' => $data['id'],
            'article' => ($data['article'] ?? null),
            'name' => ($data['name'] ?? null),
            'url' => ($data['url'] ?? null),
            'image_url' => ($data['imageUrl'] ?? null),
            'description' => ($data['description'] ?? null),
            'external_id' => ($data['externalId'] ?? null),
            'quantity' => ($data['quantity'] ?? null),
            'active' => ($data['active'] ?? null),
        ], false);

        if (!$model->save(false)) {
            return null;
        }

        return $model;
    }

    /**
     * @param ProductOfferModel $model
     * @param array $data
     * @param $productId
     * @return ProductOfferModel|null
     */
    public static function saveOffer(ProductOfferModel $model, array $data, $productId)
    {
        $model->setAttributes([
            'product_id' => $productId,
            'offer_id' => $data['id'],
            'article' => ($data['article'] ?? null),
            'name' => ($data['name'] ?? null),
            'price' => (isset($data['price']) ? (int)($data['price'] * 100) : 0),
            'external_id' => ($data['externalId'] ?? null),
            'xml_id' => ($data['xmlId'] ?? null),
            'weight' => ($data['weight'] ?? null),
        ], false);

        if (!$model->save(false)) {
            return null;
        }

        return $model;
    }

    /**
     * @param ProductOfferImageModel $model
     * @param $image
     * @param $offerId
     * @return ProductOfferImageModel|null
     */
    public static function saveOfferImage(ProductOfferImageModel $model, $image, $offerId)
    {
        $model->setAttributes([
            'offer_id' => $offerId,
            'image_url' => $image,
        ], false);

        if (!$model->save(false)) {
            return null;
        }

        return $model;
    }
}
