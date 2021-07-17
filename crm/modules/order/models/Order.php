<?php

namespace crm\modules\order\models;

use common\components\order\models\OrderModel;
use common\components\order\models\OrderUpdateQueueModel;
use common\components\retailcrm\RetailCrm;
use common\components\settings\models\StatusModel;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * Class Order
 * @package crm\modules\order\models
 *
 * @property-read int $totalSumm
 * @property-read int $toPaySumm
 * @property null|string $statusName
 * @property-read null|string $siteName
 * @property-read null|string $customerPhones
 * @property-read string $fileList
 * @property-read int $deliveryCost
 * @property-read null|string $customer
 * @property-read null|string $customerName
 * @property-read null|string $recipient
 * @property-read string $deliveryAddress
 * @property-read null|string $deliveryDate
 * @property-read int $itemsSum
 * @property-read string $deliveryAddressList
 * @property-read int $prepaySum
 */
class Order extends OrderModel
{
    /**
     * @inheritDoc
     */

    /**
     * @var UploadedFile[]
     */
    public $crm;
    public $comment;

    public function rules()
    {
        return [
            ['status_id', 'in', 'range' => array_keys(self::getAvailableStatuses())],
            ['crm','safe'],
            [['comment'],'string'],
        ];
    }

    /**
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return StatusModel::find()->select(['name', 'id'])->where(['active' => true, 'available' => true])->indexBy('id')->column();
    }



    public function upload(){
        if($this->validate()){
            foreach ($this->crm as $image){
                $image->saveAs('images/uploads/' . $this->randomFileName($image->extension));
            }
            return true;
        }else{
            return false;
        }

    }

    public static function SaveTempAttachments($attachments)
    {

        $allowedFiles=['jpg','jpeg','png'];
        if (!empty($attachments)) {
            if (count($attachments['files']['name']) > 0) {
                //Loop through each file
                for ($i = 0; $i < count($attachments['files']['name']); $i++) {
                    //Get the temp file path
                    $tmpFilePath = $attachments['files']['tmp_name'][$i];

                    //Make sure we have a filepath
                    if ($tmpFilePath != "") {
                        //save the filename
                        $shortname = $attachments['files']['name'][$i];
                        $size = $attachments['files']['size'][$i];
                        $ext = substr(strrchr($shortname, '.'), 1);
                        if(in_array($ext,$allowedFiles)){
                            //save the url and the file
                            $newFileName = $shortname;
                            //Upload the file into the temp dir
                            if (move_uploaded_file($tmpFilePath, 'images'. '/' . $newFileName)) {
                                $files[] =['fileName'=>$newFileName,'type'=>$ext,'size'=>(($size/1000)),'originalName'=>$shortname];
                            }
                        }
                    }
                }
            }

        }
        return $files;
    }

    private function randomFileName($extension = false)
    {
        $extension = $extension ? '.' . $extension : '';
        do {
            $name = md5(microtime() . rand(0, 1000));
            $file = $name . $extension;
        } while (file_exists($file));
        return $file;
    }

    public function getIdCrm(){
        return $this->crm_id;
    }


    public static function checkPermission($status,$user_id)
    {
        $model=StatusModel::find()->where(['active' => true, 'available' => true,'id'=>$status,'permission'=>true])->one();
        $nextStatus = StatusModel::find()->where(['active' => true, 'available' => true,'id'=>$model['nextStatus']])->one();

        if($model&&$nextStatus){
            if($model['name']=="В покраске "||$model['name']=="Собирается"){
                if($user_id==Yii::$app->user->identity->id){
                    return true;
                }else{
                    return false;
                }
            }
            return true;
        }
        else return false;
    }



    public static function getListStatus($status,$type){
        $model = StatusModel::find()->select(['name','id','nextStatus'])->where(['active' => true, 'available' => true,'id'=>$status,'permission'=>true])->indexBy('id')->one();
        if($status == 4 && $type =="Доставка курьером"|| $status == 13 && $type =="Самовывоз"){
            return StatusModel::find()->select(['name', 'id'])->where(['active' => true, 'available' => true,'id'=>2])->indexBy('id')->column();
        }else {
            if($status == 28){
                return StatusModel::find()->select(['name', 'id'])->where(['active' => true, 'available' => true, 'id' => [$model['nextStatus'],20]])->orderBy(['id'=>SORT_DESC])->indexBy('id')->column();
            }else {
                return StatusModel::find()->select(['name', 'id'])->where(['active' => true, 'available' => true, 'id' => $model['nextStatus']])->indexBy('id')->column();
            }
        }
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
            'customer' => 'Заказчик',
            'customerPhones' => 'Тел. заказчика',
            'recipient' => 'Получатель',
            'recipient_name' => 'ФИО получателя',
            'recipient_phone' => 'Тел. получателя',
            'customer_comment' => 'Комментарий клиента',
            'manager_comment' => 'Комментарий оператора',
            'delivery_address' => 'Адрес',
            'deliveryAddress' => 'Адрес',
            'deliveryAddressList' => 'Адрес',
            'delivery_date' => 'Дата',
            'delivery_time' => 'Время',
            'delivery_time_start' => 'Время Старт',
            'deliveryCost' => 'Стоимость',
            'delivery_type' => 'Тип',
            'totalSumm' => 'Общая стоимость',
            'prepaySum' => 'Оплачено',
            'toPaySumm' => 'Сумма к оплате',
            'fileList' => 'Файлы',
            'crm'=>'Загрузка фото',
            'comment'=>'Добавить комментарий',

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
            foreach ($files as $key => $file) {
                $data[] = Html::a('Файл ' . ($key + 1), Yii::getAlias("@web/files/{$file->crm_id}_{$file->filename}"), ['target' => '_blank']);
            }
        }
        if (empty($data)) {
            return '-';
        }
        return implode('<br>', $data);
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
    public function getRecipient()
    {
        $data = [];
        if ($this->recipient_name) {
            $data[] = $this->recipient_name;
        }
        if ($this->recipient_phone) {
            $data[] = $this->recipient_phone;
        }
        if (empty($data)) {
            return null;
        }
        return implode(', ', $data);
    }

    /**
     * @return string
     */
    public function getCustomer()
    {
        $data = [];
        if ($this->customerName) {
            $data[] = $this->customerName;
        }
        if ($this->customerPhones) {
            $data[] = $this->customerPhones;
        }
        if (empty($data)) {
            return null;
        }
        return implode(', ', $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerName()
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
     * @return string
     */
    public function getDeliveryAddress()
    {
        $data = [];
        if (!empty($this->delivery_address_city)) {
            $data[] = $this->delivery_address_city;
        }
        if (!empty($this->delivery_address_street)) {
            $data[] = $this->delivery_address_street;
        }
        if (!empty($this->delivery_address_building)) {
            $data[] = "д. {$this->delivery_address_building}";
        }
        if (!empty($this->delivery_address_house)) {
            $data[] = "стр. {$this->delivery_address_house}";
        }
        if (!empty($this->delivery_address_housing)) {
            $data[] = "корп. {$this->delivery_address_housing}";
        }
        if (!empty($this->delivery_address_block)) {
            $data[] = "под. {$this->delivery_address_block}";
        }
        if (!empty($this->delivery_address_flat)) {
            $data[] = "кв./офис {$this->delivery_address_flat}";
        }
        if (!empty($this->delivery_address_floor)) {
            $data[] = "эт. {$this->delivery_address_floor}";
        }
        if (!empty($this->delivery_address_metro)) {
            $data[] = "метро {$this->delivery_address_metro}";
        }
        if (!empty($this->delivery_address_notes)) {
            if (empty($data)) {
                $data[] = $this->delivery_address_notes;
            } else {
                $data[] = "({$this->delivery_address_notes})";
            }
        }

        if (empty($data) || empty($this->delivery_address_street)) {
            return $this->delivery_address ?: '-';
        }

        return implode(', ', $data);
    }

    /**
     * @return string
     */
    public function getDeliveryAddressList()
    {
        $data = [];
        $detailData = [];
        if (!empty($this->delivery_address_city)) {
            $data[] = $this->delivery_address_city;
        }
        if (!empty($this->delivery_address_street)) {
            $data[] = $this->delivery_address_street;
        }
        if (!empty($this->delivery_address_building)) {
            $detailData[] = "д. {$this->delivery_address_building}";
        }
        if (!empty($this->delivery_address_house)) {
            $detailData[] = "стр. {$this->delivery_address_house}";
        }
        if (!empty($this->delivery_address_housing)) {
            $detailData[] = "корп. {$this->delivery_address_housing}";
        }
        if (!empty($this->delivery_address_block)) {
            $detailData[] = "под. {$this->delivery_address_block}";
        }
        if (!empty($this->delivery_address_flat)) {
            $detailData[] = "кв./офис {$this->delivery_address_flat}";
        }
        if (!empty($this->delivery_address_floor)) {
            $detailData[] = "эт. {$this->delivery_address_floor}";
        }
        if (!empty($detailData)) {
            $data[] = implode(', ', $detailData);
        }
        if (!empty($this->delivery_address_metro)) {
            $data[] = "метро {$this->delivery_address_metro}";
        }
        if (!empty($this->delivery_address_notes)) {
            $data[] = "Комментарий: {$this->delivery_address_notes}";
        }

        if (empty($data) || empty($this->delivery_address_street)) {
            return $this->delivery_address ?: '-';
        }

        return implode(',<br>', $data);
    }

    /**
     * @return string|null
     * @throws InvalidConfigException
     */
    public function getDeliveryDate()
    {
        $data = [];
        if ($this->delivery_date) {
            $data[] = Yii::$app->formatter->asDate($this->delivery_date, 'php:d.m.Y');
        }
        if ($this->delivery_time) {
            $data[] = $this->delivery_time;
        }
        if (empty($data)) {
            return null;
        }
        return implode(' ', $data);
    }

    /**
     * @return int
     */
    public function getItemsSum()
    {
        $sum = 0;

        $items = $this->items;
        if (!empty($items)) {
            foreach ($items as $item) {
                $sum += $item->summ;
            }
        }

        return $sum;
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
            if($this->status->code=="vpokraske"){
                $resp = $crm->request->ordersEdit([
                    'id' => $this->crm_id,
                    'status' => "assembling",
                ], 'id', $this->site->code);
                file_put_contents('data-status-status.json',$this->site->code);
                if ($resp->isSuccessful()) {
                    Yii::info("Завершение процесса: Изменение статуса у заказа #{$this->crm_id}\nСтатус: success", 'retailcrm');
                } else {
                    Yii::info("Завершение процесса: Изменение статуса у заказа #{$this->crm_id},{$this->status->code}}\nСтатус: fail", 'retailcrm');
                    throw new BadRequestHttpException();
                }
            }
            if($this->status->code=="pokrashen"||$this->status->code=="sobiraetsya") {

            }else{
                $resp = $crm->request->ordersEdit([
                    'id' => $this->crm_id,
                    'status' => $this->status->code,
                ], 'id', $this->site->code);
                file_put_contents('data-status-status.json',$this->site->code);
                if ($resp->isSuccessful()) {
                    Yii::info("Завершение процесса: Изменение статуса у заказа #{$this->crm_id}\nСтатус: success", 'retailcrm');
                } else {
                    Yii::info("Завершение процесса: Изменение статуса у заказа #{$this->crm_id},{$this->status->code}}\nСтатус: fail", 'retailcrm');
                    throw new BadRequestHttpException();
            }
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
