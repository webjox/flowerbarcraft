<?php

namespace common\components\order\models;

use common\components\dadata\Dadata;
use common\components\settings\models\StatusModel;
use common\components\yandexGoDelivery\YaDeliveryClient;
use crm\modules\order\models\Order;
use Exception;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class OrderDeliveryModel
 * @package common\components\order\models
 * @property int $id
 * @property int $order_id
 * @property string $external_id
 * @property string $status
 * @property int $price
 * @property bool $accepted
 * @property string $source_city
 * @property string $source_street
 * @property string $source_building
 * @property string $source_floor
 * @property string $source_flat
 * @property string $source_sender_name
 * @property string $source_sender_phone
 * @property string $source_comment
 * @property string $source_geo_lon
 * @property string $source_geo_lat
 * @property string $destination_city
 * @property string $destination_street
 * @property string $destination_building
 * @property string $destination_floor
 * @property string $destination_flat
 * @property string $destination_recipient_name
 * @property string $destination_recipient_phone
 * @property string $destination_comment
 * @property string $destination_geo_lon
 * @property string $destination_geo_lat
 * @property string $comment
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read OrderItemModel $orderItems
 * @property-read OrderModel $order
 */
class OrderDeliveryModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_delivery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'source_city', 'source_street', 'source_building', 'source_sender_name',
                    'source_sender_phone', 'destination_city', 'destination_street', 'destination_building',
                    'destination_recipient_name', 'destination_recipient_phone',
                ],
                'required'
            ],
            [
                [
                    'external_id', 'status', 'source_city', 'source_street', 'source_building',
                    'source_floor', 'source_flat', 'source_sender_name', 'source_sender_phone',
                    'source_comment', 'source_geo_lon', 'source_geo_lat', 'destination_city',
                    'destination_street', 'destination_building', 'destination_floor', 'destination_flat',
                    'destination_recipient_name', 'destination_recipient_phone', 'destination_comment',
                    'destination_geo_lon', 'destination_geo_lat', 'comment'
                ],
                'string'
            ],
            ['accepted', 'boolean'],
            [['order_id', 'price'], 'integer'],
            ['order_id', 'exist', 'targetClass' => OrderModel::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'source_city' => 'Город',
            'source_street' => 'Улица',
            'source_building' => 'Дом',
            'source_floor' => 'Этаж',
            'source_flat' => 'Квартира',
            'source_sender_name' => 'Имя отправителя',
            'source_sender_phone' => 'Телефон отправителя',
            'source_comment' => 'Комментарий для курьера от магазина',
            'destination_city' => 'Город',
            'destination_street' => 'Улица',
            'destination_building' => 'Дом',
            'destination_floor' => 'Этаж',
            'destination_flat' => 'Квартира',
            'destination_recipient_name' => 'Имя получателя',
            'destination_recipient_phone' => 'Телефон получателя',
            'destination_comment' => 'Комментарий для курьера от получателя',
            'comment' => 'Общий комментарий к заказу',
        ];
    }

    /**
     * @return bool
     */
    public function updateSourceCoordinates()
    {
        $coordinates = Dadata::getCoordinatesByAddress("{$this->source_city} {$this->source_street} {$this->source_building}");
        if ($coordinates) {
            $this->source_geo_lon = $coordinates['geo_lon'];
            $this->source_geo_lat = $coordinates['geo_lat'];
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function updateDestinationCoordinates()
    {
        $coordinates = Dadata::getCoordinatesByAddress("{$this->destination_city} {$this->destination_street} {$this->destination_building}");
        if ($coordinates) {
            $this->destination_geo_lon = $coordinates['geo_lon'];
            $this->destination_geo_lat = $coordinates['geo_lat'];
            return true;
        }
        return false;
    }

    /**
     * @return ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(OrderModel::class, ['id' => 'order_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItemModel::class, ['order_id' => 'order_id']);
    }

    /**
     * @return array
     */
    public function sendDeliveryRequest()
    {
        if (YII_DEBUG) {
            return [
                'statusCode' => 400,
                'content' => [
                    'message' => 'Нельзя отправлять реальный запрос в среде для разработки',
                ],
            ];
        }
        try {
            $items = [];
            if (!empty($this->orderItems)) {
                foreach ($this->orderItems as $orderItem) {
                    $items[] = [
                        'cost_currency' => 'RUB',
                        'cost_value' => (string)($orderItem->price / 100),
                        'droppof_point' => 2,
                        'pickup_point' => 1,
                        'quantity' => $orderItem->quantity,
                        'title' => $orderItem->name,
                        'weight' => 1,
                        'size' => [
                            'height' => 0.30,
                            'length' => 0.30,
                            'width' => 0.50,
                        ],
                    ];
                }
            }
            if (empty($items)) {
                return [
                    'statusCode' => 400,
                    'content' => [
                        'message' => 'Нет информации о товарах в заказе.',
                    ]
                ];
            }
            $points = [
                [
                    'address' => [
                        'building' => $this->source_building,
                        'city' => $this->source_city,
                        'country' => 'Российская Федерация',
                        'fullname' => implode(', ', [$this->source_city, $this->source_street, $this->source_building]),
                        'sflat' => $this->source_flat,
                        'sfloor' => $this->source_floor,
                        'shortname' => implode(', ', [$this->source_street, $this->source_building]),
                        'street' => $this->source_street,
                        'comment' => $this->source_comment,
                        'coordinates' => [floatval($this->source_geo_lon), floatval($this->source_geo_lat)],
                    ],
                    'contact' => [
                        'name' => $this->source_sender_name,
                        'phone' => $this->source_sender_phone,
                    ],
                    'point_id' => 1,
                    'type' => 'source',
                    'visit_order' => 1,
                ],
                [
                    'address' => [
                        'building' => $this->destination_building,
                        "city" => $this->destination_city,
                        "country" => "Российская Федерация",
                        "fullname" => implode(', ', [$this->destination_city, $this->destination_street, $this->destination_building]),
                        "sflat" => $this->destination_flat,
                        "sfloor" => $this->destination_floor,
                        "shortname" => implode(', ', [$this->destination_street, $this->destination_building]),
                        "street" => $this->destination_street,
                        'comment' => $this->destination_comment,
                        'coordinates' => [floatval($this->destination_geo_lon), floatval($this->destination_geo_lat)],
                    ],
                    'contact' => [
                        'name' => $this->destination_recipient_name,
                        'phone' => $this->destination_recipient_phone,
                    ],
                    'point_id' => 2,
                    'type' => 'destination',
                    'visit_order' => 2,
                ],
            ];

            $resp = YaDeliveryClient::sendDeliveryRequest([
                'comment' => $this->comment,
                'items' => $items,
                'points' => $points,
            ]);

            if ($resp['statusCode'] == 200 && !empty($resp['content']['id'])) {
                $this->external_id = $resp['content']['id'];
                $this->status = $resp['content']['status'];
            }

            return $resp;
        } catch (Exception $e) {

        }

        return [
            'statusCode' => 400,
            'content' => [
                'message' => 'Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.',
            ],
        ];
    }

    /**
     * @return array
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getDeliveryStatus()
    {
        $info = YaDeliveryClient::getStatus($this->external_id);

        $isChanged = false;
        if ($info['statusCode'] == 200) {
            if (!empty($info['content']['status']) && $info['content']['status'] != $this->status) {
                $this->status = $info['content']['status'];
                if ($info['content']['status'] == 'delivered_finish') {
                    $order = Order::findOne(['id' => $this->order_id]);
                    if ($order) {
                        $statusId = StatusModel::find()->select(['id'])->where(['code' => 'complete'])->scalar();
                        $order->status_id = $statusId;
                        $order->save(false);
                    }
                }
                $isChanged = true;
            }
            if (!empty($info['content']['pricing']['offer']['price']) && $this->price != $info['content']['pricing']['offer']['price']) {
                $price = (int)($info['content']['pricing']['offer']['price'] * 100);
                $this->price = $price;
                $isChanged = true;
            }
        }

        if ($isChanged) {
            $this->save(false);
        }

        return $info;
    }

    /**
     * @return array|bool[]
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function acceptDelivery()
    {
        $info = YaDeliveryClient::acceptDelivery($this->external_id);
        if ($info['statusCode'] == 200) {
            $this->accepted = true;
            if (!empty($info['content']['status'])) {
                $this->status = $info['content']['status'];
            }
            $this->save(false);
            return [
                'success' => true,
                'status' => $this->status,
            ];
        }

        return [
            'success' => false,
            'message' => $info['content']['message'] ?? null,
        ];
    }
}
