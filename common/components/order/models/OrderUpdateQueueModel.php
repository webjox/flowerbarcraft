<?php

namespace common\components\order\models;

use common\components\settings\models\StatusModel;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class OrderUpdateQueueModel
 * @package common\components\order\models
 * @property int $id [int(11)]
 * @property int $order_id [int(11)]
 * @property int $status_id [int(11)]
 *
 * @property OrderModel $order
 * @property StatusModel $status
 */
class OrderUpdateQueueModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_update_queue}}';
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(StatusModel::class, ['id' => 'status_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(OrderModel::class, ['id' => 'order_id']);
    }
}
