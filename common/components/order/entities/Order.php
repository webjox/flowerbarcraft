<?php

namespace common\components\order\entities;

use common\components\order\models\OrderModel;
use common\components\retailcrm\RetailCrm;
use common\components\settings\models\StatusModel;
use common\components\site\models\SiteModel;
use yii\base\InvalidConfigException;

/**
 * Class Order
 * @package common\components\order\entities
 */
class Order
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

    private $_isConfirmed = false;

    /**
     * @var Item[]
     */
    public $items = [];

    /**
     * @var Payment[]
     */
    public $payments = [];

    /**
     * @var File[]
     */
    public $files = [];

    /**
     * @param array $data
     * @return Order
     * @throws InvalidConfigException
     */
    public static function create(array $data)
    {
        $order = new self();
        $order->crm_id = !empty($data['id']) ? $data['id'] : null;
        $order->number = !empty($data['number']) ? $data['number'] : null;
        $order->external_id = !empty($data['externalId']) ? $data['externalId'] : null;
        if (!empty($data['siteCode'])) {
            $siteId = SiteModel::find()->select('id')->where(['code' => $data['siteCode']])->scalar();
            $order->site_id = $siteId != false ? $siteId : null;
        }
        if (!empty($data['statusCode'])) {
            if ($data['statusCode'] == OrderModel::STATUS_CONFIRMED) {
                $order->setConfirmed();
            }
            $statusId = StatusModel::find()->select('id')->where(['code' => $data['statusCode']])->scalar();
            $order->status_id = $statusId != false ? $statusId : null;
        }
        $order->created_at = !empty($data['createdAt']) ? $data['createdAt'] : null;
        $order->customer_last_name = !empty($data['lastName']) ? $data['lastName'] : null;
        $order->customer_first_name = !empty($data['firstName']) ? $data['firstName'] : null;
        $order->customer_patronymic = !empty($data['patronymic']) ? $data['patronymic'] : null;
        $order->customer_phone = !empty($data['phone']) ? $data['phone'] : null;
        $order->customer_additional_phone = !empty($data['additionalPhone']) ? $data['additionalPhone'] : null;
        $order->customer_comment = !empty($data['customerComment']) ? $data['customerComment'] : null;
        $order->manager_comment = !empty($data['managerComment']) ? $data['managerComment'] : null;
        $order->delivery_address = !empty($data['deliveryAddress']) ? $data['deliveryAddress'] : null;
        $order->delivery_date = !empty($data['deliveryDate']) ? $data['deliveryDate'] : null;
        $order->delivery_time = !empty($data['deliveryTime']) ? $data['deliveryTime'] : null;
        $order->setDeliveryTimeOrdering($order->delivery_time);
        $order->delivery_cost = !empty($data['deliveryCost']) ? (int)($data['deliveryCost'] * 100) : 0;
        $order->total_summ = !empty($data['totalSumm']) ? (int)($data['totalSumm'] * 100) : 0;
        $order->prepay_sum = !empty($data['prepaySum']) ? (int)($data['prepaySum'] * 100) : 0;
        $order->to_pay_summ = !empty($data['toPaySumm']) ? (int)($data['toPaySumm'] * 100) : 0;
        $order->recipient_name = !empty($data['recipientName']) ? $data['recipientName'] : null;
        $order->recipient_phone = !empty($data['recipientPhone']) ? $data['recipientPhone'] : null;
        if (!empty($data['items'])) {
            $order->setItems($data['items']);
        }
        if (!empty($data['payments'])) {
            $order->setPayments($data['payments']);
        }
        if ($order->crm_id && $files = RetailCrm::getFiles($order->crm_id)) {
            $order->setFiles($files);
        }

        return $order;
    }

    public function setDeliveryTimeOrdering($value)
    {
        if (empty($value)) {
            $this->delivery_time_ordering = null;
        } else {
            $value = (int)preg_replace("/[^0-9]/", '', $value);
            if ($value > 2359) {
                $value = (int)($value / 10000);
            }
            $this->delivery_time_ordering = $value;
        }
    }

    public function setItems($value)
    {
        $items = json_decode(html_entity_decode($value), true);

        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                if (is_array($item)) {
                    $this->items[] = Item::create($item);
                }
            }
        }
    }

    public function setPayments($value)
    {
        $payments = json_decode(html_entity_decode($value), true);

        if (!empty($payments) && is_array($payments)) {
            foreach ($payments as $payment) {
                if (is_array($payment)) {
                    $this->payments[] = Payment::create($payment);
                }
            }
        }
    }

    public function setFiles($files)
    {
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                if (is_array($file)) {
                    $this->files[] = File::create($file);
                }
            }
        }
    }

    public function setConfirmed()
    {
        $this->_isConfirmed = true;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->_isConfirmed;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return json_decode(json_encode($this), true);
    }
}
