<?php

namespace crm\modules\order\models;

use Yii;

/**
 * This is the model class for table "order_image".
 *
 * @property int $id
 * @property string $order_id
 * @property string $filename

 */
class OrderImage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'filename'], 'required'],
            [['filename'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'order_id' => 'Order id',
            'filename' => 'Filename',
        ];
    }
}
