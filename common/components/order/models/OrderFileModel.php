<?php

namespace common\components\order\models;

use yii\db\ActiveRecord;

/**
 * Class OrderFileModel
 * @package common\components\order\models
 * @property int $id [int(11)]
 * @property int $crm_id [int(11)]
 * @property int $order_id [int(11)]
 * @property string $filename [varchar(255)]
 * @property string $type [varchar(255)]
 * @property string $created_at [datetime]
 * @property int $size [int(11)]
 */
class OrderFileModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_file}}';
    }
}
