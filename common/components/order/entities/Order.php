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
    public $delivery_address_city;
    public $delivery_address_street;
    public $delivery_address_building;
    public $delivery_address_house;
    public $delivery_address_housing;
    public $delivery_address_block;
    public $delivery_address_flat;
    public $delivery_address_floor;
    public $delivery_address_metro;
    public $delivery_address_notes;
    public $delivery_address_geo_lon;
    public $delivery_address_geo_lat;
    public $delivery_type;
    public $delivery_date;
    public $delivery_time;
    public $delivery_time_ordering;
    public $delivery_time_ordering_start;
    public $delivery_cost;
    public $initial_product_summ;
    public $summ;
    public $total_summ;
    public $prepay_sum;
    public $to_pay_summ;
//    public $user_id;
//    public $statusCrm;

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
        file_put_contents('data-all.json', $data);
        //   file_put_contents('data-status.json', $data['statusCode']);
        //   file_put_contents('data-type.json', $data['deliveryType']);
        $order->crm_id = !empty($data['id']) ? $data['id'] : null;
        $order->number = !empty($data['number']) ? $data['number'] : null;
        $order->external_id = !empty($data['externalId']) ? $data['externalId'] : null;
        $siteCode = !empty($data['setSite']) ? $data['setSite'] : (!empty($data['siteCode']) ? $data['siteCode'] : null);
        if ($siteCode) {
            $siteId = SiteModel::find()->select('id')->where(['code' => $siteCode])->scalar();
            $order->site_id = $siteId != false ? $siteId : null;
        }
        if (!empty($data['statusCode'])) {
            if ($data['statusCode'] == OrderModel::STATUS_CONFIRMED) {
                $order->setConfirmed();
            }
            $statusId = StatusModel::find()->select('id')->where(['code' => $data['statusCode']])->scalar();
            if ($siteCode) {
                $siteIsActive = SiteModel::find()->select('is_active_personal_area')->where(['code' => $siteCode])->scalar();
                if($siteIsActive) {
//                    if($statusId == 20){
//                        $order->statusCrm = 1;
//                    }
                    if ($statusId == 19) {
                        $order->status_id = 28;
                    } else {
                        $order->status_id = $statusId != false ? $statusId : null;
                    }
                }else{
                    $order->status_id = $statusId != false ? $statusId : null;
                }
            }
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
        $order->delivery_address_city = !empty($data['deliveryAddressCity']) ? $data['deliveryAddressCity'] : null;
        $order->delivery_address_street = !empty($data['deliveryAddressStreet']) ? $data['deliveryAddressStreet'] : null;
        $order->delivery_address_building = !empty($data['deliveryAddressBuilding']) ? $data['deliveryAddressBuilding'] : null;
        $order->delivery_address_house = !empty($data['deliveryAddressHouse']) ? $data['deliveryAddressHouse'] : null;
        $order->delivery_address_housing = !empty($data['deliveryAddressHousing']) ? $data['deliveryAddressHousing'] : null;
        $order->delivery_address_block = !empty($data['deliveryAddressBlock']) ? $data['deliveryAddressBlock'] : null;
        $order->delivery_address_flat = !empty($data['deliveryAddressFlat']) ? $data['deliveryAddressFlat'] : null;
        $order->delivery_address_floor = !empty($data['deliveryAddressFloor']) ? $data['deliveryAddressFloor'] : null;
        $order->delivery_address_metro = !empty($data['deliveryAddressMetro']) ? $data['deliveryAddressMetro'] : null;
        $order->delivery_address_notes = !empty($data['deliveryAddressNotes']) ? $data['deliveryAddressNotes'] : null;
        $order->delivery_type = !empty($data['deliveryType']) ? $data['deliveryType'] : null;
        $order->delivery_date = !empty($data['deliveryDate']) ? $data['deliveryDate'] : null;
        $order->delivery_time = !empty($data['deliveryTime']) ? $data['deliveryTime'] : null;
        $order->setDeliveryTimeOrdering($order->delivery_time);
        $order->setDeliveryTimeOrderingStart($order->delivery_time);
        $order->delivery_cost = !empty($data['deliveryCost']) ? (int)($data['deliveryCost'] * 100) : 0;
        $order->initial_product_summ = !empty($data['initialProductSumm']) ? (int)($data['initialProductSumm'] * 100) : 0;
        $order->summ = !empty($data['summ']) ? (int)($data['summ'] * 100) : 0;
        $order->total_summ = !empty($data['totalSumm']) ? (int)($data['totalSumm'] * 100) : 0;
        $order->prepay_sum = !empty($data['prepaySum']) ? (int)($data['prepaySum'] * 100) : 0;
        $order->to_pay_summ = !empty($data['toPaySumm']) ? (int)($data['toPaySumm'] * 100) : 0;
        $order->recipient_name = !empty($data['recipientName']) ? $data['recipientName'] : null;
        $order->recipient_phone = !empty($data['recipientPhone']) ? $data['recipientPhone'] : null;
        if($data['deliveryType']=="???????????????? ????????????????"){
            $address = $data['deliveryAddress'];
            $parameters = array(
                'apikey' => '10d0aae4-eb7c-4e41-91f3-1d6217a9b798',
                'geocode' => $address,
                'format' => 'json'
            );
            $response = file_get_contents('https://geocode-maps.yandex.ru/1.x/?'. http_build_query($parameters));
            $obj = json_decode($response, true);
            if($obj) {
                $cord = explode(' ', $obj['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos']);
                $order->delivery_address_geo_lon = $cord[0];
                $order->delivery_address_geo_lat = $cord[1];
            }else{
                //?????????????????? ?????? ?????????????????? ????????????????????, ???????? ?????????? ???????????? ??????????????????????
            }
        }
        if($siteId==34) {
            file_put_contents('data-all.json', $data);
            file_put_contents('data-status.json', $data['statusCode']);
            file_put_contents('data-type.json', $data['deliveryType']);
        }
        if (!empty($data['items'])) {
            $order->setItems($data['items']);
            if($siteId==34) file_put_contents('data-value.json', $data['items']);
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

            //  while(log10($value)<9){
            //      $value*=10;
            //  }
              file_put_contents('data-time.json', $value);
            if ($value > 2359) {
                $value = (int)($value % 10000);
            }
            $this->delivery_time_ordering = $value;
        }
    }

    public function setDeliveryTimeOrderingStart($value)
    {
        if (empty($value)) {
            $this->delivery_time_ordering_start = null;
        } else {
            $value = (int)preg_replace("/[^0-9]/", '', $value);

            //  while(log10($value)<9){
            //      $value*=10;
            //  }
            file_put_contents('data-time.json', $value);
            if ($value > 2359) {
                $value = (int)($value / 10000);
            }
            $this->delivery_time_ordering_start = $value;
        }
    }

    public function setItems($value)
    {
        $items = json_decode(html_entity_decode($value), true);

        file_put_contents('data-value.json', $value);
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
