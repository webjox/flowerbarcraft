<?php

namespace common\components\order\models;

use common\components\product\models\ProductOfferModel;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class OrderItemModel
 * @package common\components\order\models
 * @property int $id [int(11)]
 * @property int $crm_id [int(11)]
 * @property int $order_id [int(11)]
 * @property int $price [int(11)]
 * @property int $quantity [int(11)]
 * @property int $summ [int(11)]
 * @property int $offer_id [int(11)]
 * @property string $crm_offer_id [varchar(255)]
 * @property string $name [varchar(255)]
 *
 * @property ProductOfferModel $offer
 */
class OrderItemModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_item}}';
    }

    /**
     * @return ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(ProductOfferModel::class, ['id' => 'offer_id']);
    }
}
