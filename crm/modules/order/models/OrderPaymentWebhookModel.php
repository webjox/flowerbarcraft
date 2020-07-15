<?php

namespace crm\modules\order\models;

use common\components\order\models\OrderPaymentModel;
use yii\base\Model;

/**
 * Class OrderPaymentWebhookModel
 * @package crm\modules\order\models
 */
class OrderPaymentWebhookModel extends Model
{
    public $crm_id;
    public $status;
    public $type;
    public $amount;
    public $paid_at;
    public $comment;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['status', 'type', 'amount', 'paid_at', 'comment'], 'safe'],
            ['crm_id', 'unique', 'targetClass' => OrderPaymentModel::class, 'targetAttribute' => 'crm_id'],
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
        $paymentModel = OrderPaymentModel::findOne(['order_id' => $orderId, 'crm_id' => $this->crm_id]);
        if (!$paymentModel) {
            $paymentModel = new OrderPaymentModel(['order_id' => $orderId]);
        }
        $paymentModel->setAttributes($this->attributes, false);
        return $paymentModel->save(false);
    }
}
