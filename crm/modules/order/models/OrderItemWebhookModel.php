<?php

namespace crm\modules\order\models;

use common\components\order\models\OrderItemModel;
use yii\base\Model;

/**
 * Class OrderItemWebhookModel
 * @package crm\modules\order\models
 */
class OrderItemWebhookModel extends Model
{
    public $crm_id;
    public $price;
    public $quantity;
    public $summ;
    public $offer_id;
    public $crm_offer_id;
    public $name;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['price', 'quantity', 'summ', 'offer_id', 'crm_offer_id', 'name'], 'safe'],
            ['crm_id', 'unique', 'targetClass' => OrderItemModel::class, 'targetAttribute' => 'crm_id'],
        ];
    }

    /**
     * @param array $data
     * @param string $formName
     * @return bool
     */
    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function save($orderId)
    {
        $itemModel = OrderItemModel::findOne(['order_id' => $orderId, 'crm_id' => $this->crm_id]);
        if (!$itemModel) {
            $itemModel = new OrderItemModel(['order_id' => $orderId]);
        }
        $itemModel->setAttributes($this->attributes, false);
        return $itemModel->save(false);
    }
}
