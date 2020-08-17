<?php

namespace common\components\product\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class ProductOfferModel
 * @package common\components\product\models
 * @property int $id [int(11)]
 * @property int $product_id [int(11)]
 * @property int $offer_id [int(11)]
 * @property string $article [varchar(255)]
 * @property string $name [varchar(255)]
 * @property int $price [int(11)]
 * @property int $weight [int(11)]
 * @property string $external_id [varchar(255)]
 * @property string $xml_id [varchar(255)]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 *
 * @property ProductOfferImageModel[] $images
 */
class ProductOfferModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_offer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(ProductOfferImageModel::class, ['offer_id' => 'id']);
    }
}
