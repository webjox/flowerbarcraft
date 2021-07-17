<?php

namespace common\components\order\entities;

use common\components\product\models\ProductOfferModel;

/**
 * Class Item
 * @package common\components\order\entities
 */
class Item
{
    public $crm_id;
    public $price;
    public $initial_price;
    public $quantity;
    public $summ;
    public $discount_summ;
    public $offer_id;
    public $crm_offer_id;
    public $name;
    public $weight;
    public $imageUrl;
    public $manufacturer;

    /**
     * @param array $data
     * @return Item
     */
    public static function create(array $data)
    {
        $item = new self();
        $item->crm_id = !empty($data['id']) ? $data['id'] : null;
        $item->price = !empty($data['price']) ? (int)($data['price'] * 100) : 0;
        $item->initial_price = !empty($data['initial_price']) ? (int)($data['initial_price'] * 100) : 0;
        $item->quantity = !empty($data['quantity']) ? $data['quantity'] : 0;
        $item->summ = !empty($data['summ']) ? (int)($data['summ'] * 100) : 0;
        $item->discount_summ = !empty($data['discount_summ']) ? (int)($data['discount_summ'] * 100) : 0;
        if (!empty($data['offer_id'])) {
            $offerId = ProductOfferModel::find()->select('id')->where(['offer_id' => $data['offer_id']])->scalar();
            $item->offer_id = $offerId != false ? $offerId : null;
        }
        $item->crm_offer_id = !empty($data['offer_id']) ? $data['offer_id'] : null;
        $item->name = !empty($data['name']) ? $data['name'] : null;
        $item->weight = !empty($data['weight']) ? $data['weight'] : null;
        $item->imageUrl = !empty($data['imageUrl']) ? $data['imageUrl'] : null;
        $item->manufacturer = !empty($data['manufacturer']) ? $data['manufacturer'] : null;
        return $item;
    }
}
