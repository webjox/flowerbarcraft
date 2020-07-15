<?php

namespace common\components\order\models;

use yii\db\ActiveRecord;

/**
 * Class OrderPaymentModel
 * @package common\components\order\models
 * @property int $id [int(11)]
 * @property int $crm_id [int(11)]
 * @property int $order_id [int(11)]
 * @property string $status [varchar(255)]
 * @property string $type [varchar(255)]
 * @property int $amount [int(11)]
 * @property string $paid_at [datetime]
 * @property string $comment
 */
class OrderPaymentModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_payment}}';
    }
}
