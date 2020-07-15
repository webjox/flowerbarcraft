<?php

namespace crm\modules\order\models;

use common\components\order\models\OrderModel;
use common\components\order\models\OrderUpdateQueueModel;
use common\components\retailcrm\RetailCrm;
use common\components\settings\models\StatusModel;
use Exception;
use Yii;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;

/**
 * Class Order
 * @package crm\modules\order\models
 */
class Order extends OrderModel
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['status_id', 'in', 'range' => array_keys(self::getAvailableStatuses())],
        ];
    }

    /**
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return StatusModel::find()->select(['name', 'id'])->where(['active' => true, 'available' => true])->indexBy('id')->column();
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'crm_id' => 'ID',
            'number' => 'Номер заказа',
            'total_summ' => 'Сумма заказа',
            'status' => 'Статус',
            'status_id' => 'Статус',
            'statusName' => 'Статус',
            'siteName' => 'Магазин',
            'external_id' => 'Внешний ID',
            'created_at' => 'Дата оформления',
            'customer' => 'ФИО заказчика',
            'customerPhones' => 'Тел. заказчика',
            'recipient_name' => 'ФИО получателя',
            'recipient_phone' => 'Тел. получателя',
            'customer_comment' => 'Комментарий клиента',
            'manager_comment' => 'Комментарий оператора',
            'delivery_address' => 'Адрес',
            'delivery_date' => 'Дата',
            'delivery_time' => 'Время',
            'deliveryCost' => 'Стоимость',
            'totalSumm' => 'Общая стоимость',
            'prepaySum' => 'Оплачено',
            'toPaySumm' => 'Сумма к оплате',
            'fileList' => 'Файлы',
        ];
    }

    /**
     * @return string
     */
    public function getFileList()
    {
        $data = [];
        $files = $this->files;
        if (!empty($files)) {
            foreach ($files as $file) {
                $data[] = Html::a($file->filename, Yii::getAlias("@web/files/{$file->crm_id}_{$file->filename}"), ['target' => '_blank']);
            }
        }
        if (empty($data)) {
            return '-';
        }
        return implode(', ', $data);
    }

    /**
     * @return int
     */
    public function getTotalSumm()
    {
        return $this->total_summ > 0 ? (int)($this->total_summ / 100) : 0;
    }

    /**
     * @return int
     */
    public function getPrepaySum()
    {
        return $this->prepay_sum > 0 ? (int)($this->prepay_sum / 100) : 0;
    }

    /**
     * @return int
     */
    public function getToPaySumm()
    {
        return $this->to_pay_summ > 0 ? (int)($this->to_pay_summ / 100) : 0;
    }

    /**
     * @return int
     */
    public function getDeliveryCost()
    {
        return $this->delivery_cost > 0 ? (int)($this->delivery_cost / 100) : 0;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return $this->status->name ?? null;
    }

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->site->name ?? null;
    }

    /**
     * @return string|null
     */
    public function getCustomer()
    {
        $data = [];
        if ($this->customer_last_name) {
            $data[] = $this->customer_last_name;
        }
        if ($this->customer_first_name) {
            $data[] = $this->customer_first_name;
        }
        if ($this->customer_patronymic) {
            $data[] = $this->customer_patronymic;
        }
        if (empty($data)) {
            return null;
        }
        return implode(' ', $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerPhones()
    {
        $data = [];
        if ($this->customer_phone && $this->customer_additional_phone) {
            $data[] = "{$this->customer_phone} ({$this->customer_additional_phone})";
        } elseif ($this->customer_phone) {
            $data[] = $this->customer_phone;
        } elseif ($this->customer_additional_phone) {
            $data[] = $this->customer_additional_phone;
        }
        if (empty($data)) {
            return null;
        }
        return implode(' ', $data);
    }

    /**
     * После изменения статуса заказа отправляем данные в retailCRM
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        try {
            Yii::info("Старт процесса: Изменение статуса у заказа #{$this->crm_id}", 'retailcrm');
            $crm = RetailCrm::getInstance();
            $resp = $crm->request->ordersEdit([
                'id' => $this->crm_id,
                'status' => $this->status->code,
            ], 'id', $this->site->code);
            if ($resp->isSuccessful()) {
                Yii::info("Завершение процесса: Изменение статуса у заказа #{$this->crm_id}\nСтатус: success", 'retailcrm');
            } else {
                Yii::info("Завершение процесса: Изменение статуса у заказа #{$this->crm_id}\nСтатус: fail", 'retailcrm');
                throw new BadRequestHttpException();
            }
        } catch (Exception $e) {
            Yii::info("Ошибка при изменении статуса у заказа #{$this->crm_id}", 'retailcrm');
            (new OrderUpdateQueueModel([
                'order_id' => $this->id,
                'status_id' => $this->status_id,
            ]))->save(false);
        }
    }
}