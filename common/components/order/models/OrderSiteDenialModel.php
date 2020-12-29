<?php

namespace common\components\order\models;

use yii\db\ActiveRecord;

/**
 * Class OrderSiteDenialModel
 * @package common\components\order\models
 * @property int $id [int(11)]
 * @property int $order_id [int(11)]
 * @property int $site_id [int(11)]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 */
class OrderSiteDenialModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_site_denial}}';
    }
}
