<?php

namespace common\components\order\models;

use common\components\settings\models\StatusModel;
use common\components\site\models\SiteModel;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class OrderModel
 * @package common\components\order\models
 * @property int $id [int(11)]
 * @property int $crm_id [int(11)]
 * @property string $number [varchar(255)]
 * @property string $external_id [varchar(255)]
 * @property int $site_id [int(11)]
 * @property int $status_id [int(11)]
 * @property string $created_at [datetime]
 * @property string $customer_last_name [varchar(255)]
 * @property string $customer_first_name [varchar(255)]
 * @property string $customer_patronymic [varchar(255)]
 * @property string $customer_phone [varchar(255)]
 * @property string $customer_additional_phone [varchar(255)]
 * @property string $recipient_name [varchar(255)]
 * @property string $recipient_phone [varchar(255)]
 * @property string $customer_comment
 * @property string $manager_comment
 * @property string $delivery_address
 * @property string $delivery_address_city [varchar(255)]
 * @property string $delivery_address_street [varchar(255)]
 * @property string $delivery_address_building [varchar(255)]
 * @property string $delivery_address_house [varchar(255)]
 * @property string $delivery_address_housing [varchar(255)]
 * @property string $delivery_address_block [varchar(255)]
 * @property string $delivery_address_flat [varchar(255)]
 * @property string $delivery_address_floor [varchar(255)]
 * @property string $delivery_address_metro [varchar(255)]
 * @property string $delivery_address_notes
 * @property string $delivery_date [date]
 * @property string $delivery_time [varchar(255)]
 * @property int $delivery_time_ordering [int(11)]
 * @property int $delivery_cost [int(11)]
 * @property int $total_summ [int(11)]
 * @property int $prepay_sum [int(11)]
 * @property int $to_pay_summ [int(11)]
 *
 * @property OrderItemModel[] $items
 * @property OrderFileModel[] $files
 * @property OrderPaymentModel[] $payments
 * @property StatusModel $status
 * @property SiteModel $site
 */
class OrderModel extends ActiveRecord
{
    const STATUS_CONFIRMED = 'confirmed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @return ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OrderItemModel::class, ['order_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(OrderPaymentModel::class, ['order_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(OrderFileModel::class, ['order_id' => 'id']);
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
    public function getSite()
    {
        return $this->hasOne(SiteModel::class, ['id' => 'site_id']);
    }
}
