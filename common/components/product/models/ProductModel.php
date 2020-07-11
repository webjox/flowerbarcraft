<?php

namespace common\components\product\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class ProductModel
 * @package common\components\product\models
 * @property int $id [int(11)]
 * @property int $product_id [int(11)]
 * @property string $article [varchar(255)]
 * @property string $name [varchar(255)]
 * @property string $url [varchar(255)]
 * @property string $image_url [varchar(255)]
 * @property string $description
 * @property string $external_id [varchar(255)]
 * @property int $quantity [int(11)]
 * @property bool $active [tinyint(1)]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 *
 * @property ProductOfferModel[] $offers
 */
class ProductModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product}}';
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
    public function getOffers()
    {
        return $this->hasMany(ProductOfferModel::class, ['product_id' => 'id']);
    }
}
