<?php

namespace common\components\product\helpers;

/**
 * Class CompareHelper
 * @package common\components\product\helpers
 */
class CompareHelper
{
    /**
     * Сравнение товара, полученного по апи, с сохраненным в БД.
     * @param array $product
     * @param array $savedProduct
     * @return bool
     */
    public static function productsIsEqual(array $product, array $savedProduct)
    {
        $article = $product['article'] ?? null;
        if ($article != $savedProduct['article']) {
            return false;
        }

        $name = $product['name'] ?? null;
        if ($name != $savedProduct['name']) {
            return false;
        }

        $url = $product['url'] ?? null;
        if ($url != $savedProduct['url']) {
            return false;
        }

        $imageUrl = $product['imageUrl'] ?? null;
        if ($imageUrl != $savedProduct['image_url']) {
            return false;
        }

        $description = $product['description'] ?? null;
        if ($description != $savedProduct['description']) {
            return false;
        }

        $externalId = $product['externalId'] ?? null;
        if ($externalId != $savedProduct['external_id']) {
            return false;
        }

        $quantity = $product['quantity'] ?? null;
        if ($quantity != $savedProduct['quantity']) {
            return false;
        }

        $active = $product['active'] ?? null;
        if ($active != $savedProduct['active']) {
            return false;
        }

        return true;
    }

    /**
     * Сравнение торгового предложения, полученного по апи, с сохраненным в БД.
     * @param array $offer
     * @param array $savedOffer
     * @return bool
     */
    public static function offersIsEqual(array $offer, array $savedOffer)
    {
        $article = $offer['article'] ?? null;
        if ($article != $savedOffer['article']) {
            return false;
        }

        $name = $offer['name'] ?? null;
        if ($name != $savedOffer['name']) {
            return false;
        }

        $price = isset($offer['price']) ? (int)($offer['price'] * 100) : 0;
        if ($price != $savedOffer['price']) {
            return false;
        }

        $externalId = $offer['externalId'] ?? null;
        if ($externalId != $savedOffer['external_id']) {
            return false;
        }

        $externalId = $offer['xmlId'] ?? null;
        if ($externalId != $savedOffer['xml_id']) {
            return false;
        }

        return true;
    }
}
