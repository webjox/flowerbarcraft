<?php

namespace common\components\product\models;

use yii\db\ActiveRecord;

/**
 * Class ProductOfferImageModel
 * @package common\components\product\models
 * @property int $id [int(11)]
 * @property int $offer_id [int(11)]
 * @property string $image_url [varchar(255)]
 */
class ProductOfferImageModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_offer_image}}';
    }
}
