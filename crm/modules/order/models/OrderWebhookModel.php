<?php

namespace crm\modules\order\models;

use common\components\order\models\OrderModel;
use Throwable;
use yii\base\Model;
use yii\db\StaleObjectException;

/**
 * Class OrderWebhookModel
 * @package crm\modules\order\models
 */
class OrderWebhookModel extends Model
{
    public $crm_id;
    public $number;
    public $external_id;
    public $site_id;
    public $status_id;
    public $created_at;
    public $customer_last_name;
    public $customer_first_name;
    public $customer_patronymic;
    public $customer_phone;
    public $customer_additional_phone;
    public $recipient_name;
    public $recipient_phone;
    public $customer_comment;
    public $manager_comment;
    public $delivery_address;
    public $delivery_date;
    public $delivery_time;
    public $delivery_time_ordering;
    public $delivery_cost;
    public $total_summ;
    public $prepay_sum;
    public $to_pay_summ;

    private $order;
    /**
     * @var OrderItemWebhookModel[]
     */
    private $items;
    /**
     * @var OrderPaymentWebhookModel[]
     */
    private $payments;
    private $files;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                [
                    'number', 'external_id', 'site_id', 'status_id', 'created_at',
                    'customer_last_name', 'customer_first_name', 'customer_patronymic',
                    'customer_phone', 'customer_additional_phone', 'recipient_name', 'to_pay_summ',
                    'recipient_phone', 'customer_comment', 'manager_comment', 'delivery_address',
                    'delivery_date', 'delivery_time', 'delivery_cost', 'total_summ', 'prepay_sum',
                    'delivery_time_ordering',
                ],
                'safe'
            ],
            ['crm_id', 'unique', 'targetClass' => OrderModel::class, 'targetAttribute' => 'crm_id'],
        ];
    }

    /**
     * @param array $data
     * @param string $formName
     * @return bool
     */
    public function load($data, $formName = '')
    {
        $isLoad = parent::load($data, $formName);

        //items
        if ($isLoad && !empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $model = new OrderItemWebhookModel();
                if ($model->load($item)) {
                    $this->items[] = $model;
                } else {
                    $isLoad = false;
                }
            }
        }

        //payments
        if ($isLoad && !empty($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $item) {
                $model = new OrderPaymentWebhookModel();
                if ($model->load($item)) {
                    $this->payments[] = $model;
                } else {
                    $isLoad = false;
                }
            }
        }

        //files
        if ($isLoad && !empty($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $item) {
                $model = new OrderFileWebhookModel();
                if ($model->load($item)) {
                    $this->files[] = $model;
                } else {
                    $isLoad = false;
                }
            }
        }

        return $isLoad;
    }

    /**
     * @return bool
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function save()
    {
        $orderModel = OrderModel::findOne(['crm_id' => $this->crm_id]);
        if (!$orderModel) {
            $orderModel = new OrderModel();
        }
        $orderModel->setAttributes($this->attributes, false);
        $isSave = $orderModel->save(false);

        //items
        if ($isSave && !empty($this->items) && is_array($this->items)) {
            foreach ($this->items as $item) {
                $isSave = $isSave && $item->save($orderModel->id);
            }
        }

        //payments
        if ($isSave && !empty($this->payments) && is_array($this->payments)) {
            foreach ($this->payments as $item) {
                $isSave = $isSave && $item->save($orderModel->id);
            }
        }

        //files
        if ($isSave && !empty($this->files) && is_array($this->files)) {
            foreach ($this->files as $item) {
                $isSave = $isSave && $item->save($orderModel->id);
            }
        }

        if ($isSave) {
            $this->removeMissingItems($orderModel);
            $this->removeMissingPayments($orderModel);
            $this->removeMissingFiles($orderModel);
        }

        return $isSave;
    }

    /**
     * @param OrderModel $orderModel
     * @throws Throwable
     * @throws StaleObjectException
     */
    private function removeMissingItems(OrderModel $orderModel)
    {
        $savedItems = $orderModel->items;
        if ($savedItems) {
            foreach ($savedItems as $savedItem) {
                $exists = false;
                if (!empty($this->items)) {
                    foreach ($this->items as $item) {
                        if ($savedItem->crm_id == $item->crm_id) {
                            $exists = true;
                        }
                    }
                }
                if (!$exists) {
                    $savedItem->delete();
                }
            }
        }
    }

    /**
     * @param OrderModel $orderModel
     * @throws Throwable
     * @throws StaleObjectException
     */
    private function removeMissingPayments(OrderModel $orderModel)
    {
        $savedPayments = $orderModel->payments;
        if ($savedPayments) {
            foreach ($savedPayments as $savedPayment) {
                $exists = false;
                if (!empty($this->payments)) {
                    foreach ($this->payments as $payment) {
                        if ($savedPayment->crm_id == $payment->crm_id) {
                            $exists = true;
                        }
                    }
                }
                if (!$exists) {
                    $savedPayment->delete();
                }
            }
        }
    }

    /**
     * @param OrderModel $orderModel
     * @throws Throwable
     * @throws StaleObjectException
     */
    private function removeMissingFiles(OrderModel $orderModel)
    {
        $savedFiles = $orderModel->files;
        if ($savedFiles) {
            foreach ($savedFiles as $savedFile) {
                $exists = false;
                if (!empty($this->files)) {
                    foreach ($this->files as $file) {
                        if ($savedFile->crm_id == $file->crm_id) {
                            $exists = true;
                        }
                    }
                }
                if (!$exists) {
                    $savedFile->delete();
                }
            }
        }
    }
}
