<?php

namespace common\components\user\models;

use common\components\dadata\Dadata;
use yii\db\ActiveRecord;

/**
 * Class UserDelivery
 * @package common\components\user\models
 * @property int $id [int(11)]
 * @property int $user_id [int(11)]
 * @property bool $active [tinyint(1)]
 * @property string $city [varchar(255)]
 * @property string $comment [text]
 * @property string $street [varchar(255)]
 * @property string $building [varchar(255)]
 * @property string $floor [varchar(255)]
 * @property string $flat [varchar(255)]
 * @property string $sender_name [varchar(255)]
 * @property string $sender_phone [varchar(255)]
 * @property string $geo_lon [varchar(255)]
 * @property string $geo_lat [varchar(255)]
 */
class UserDelivery extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_delivery}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['city', 'street', 'building', 'floor', 'flat', 'sender_name', 'sender_phone'], 'string', 'max' => 255],
            ['user_id', 'exist', 'targetClass' => FloristManage::class, 'targetAttribute' => ['user_id' => 'id']],
            ['active', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'active' => 'Доступна Яндекс.Доставка',
            'city' => 'Город',
            'street' => 'Улица',
            'building' => 'Дом',
            'floor' => 'Этаж',
            'flat' => 'Квартира',
            'sender_name' => 'Имя отправителя',
            'sender_phone' => 'Телефон отправителя',
            'comment' => 'Комментарий для курьера',
        ];
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $addressIsChanged = false;
            if ($this->isNewRecord) {
                $addressIsChanged = true;
            } elseif (is_array($this->oldAttributes)) {
                $keys = ['city', 'street', 'building'];
                foreach ($keys as $key) {
                    if (isset($this->oldAttributes[$key], $this->{$key}) && $this->oldAttributes[$key] != $this->{$key}) {
                        $addressIsChanged = true;
                        break;
                    }
                }
            }
            if ($addressIsChanged) {
                $coordinates = Dadata::getCoordinatesByAddress("{$this->city} {$this->street} {$this->building}");
                if ($coordinates) {
                    $this->geo_lon = $coordinates['geo_lon'];
                    $this->geo_lat = $coordinates['geo_lat'];
                }
            }
            return true;
        }
        return false;
    }
}
