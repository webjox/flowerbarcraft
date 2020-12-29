<?php

namespace common\components\order\models;

use common\components\retailcrm\RetailCrm;
use common\components\settings\models\StatusModel;
use common\components\site\models\SiteModel;
use common\components\tg\TgBot;
use DateInterval;
use DateTime;
use DateTimeZone;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
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
 * @property int $site_received_at [int(11)]
 * @property bool $is_accepted [tinyint(1)]
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
 * @property string $delivery_type [varchar(255)]
 * @property string $delivery_date [date]
 * @property string $delivery_time [varchar(255)]
 * @property int $delivery_time_ordering [int(11)]
 * @property int $delivery_cost [int(11)]
 * @property int $summ [int(11)]
 * @property int $initial_product_summ [int(11)]
 * @property int $total_summ [int(11)]
 * @property int $prepay_sum [int(11)]
 * @property int $to_pay_summ [int(11)]
 * @property string $token [varchar(255)]
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
    const STATUS_PARTNER_CONFIRMED = 'prinat';

    const TIME_OPEN_HOUR = 9;
    const TIME_CLOSE_HOUR = 21;

    const MIN_EXPIRED_INTERVAL = 'PT30M';
    const MAX_EXPIRED_INTERVAL = 'PT1H';

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

    /**
     * @return bool
     */
    public function isAcceptingExpired()
    {
        $site = $this->site;
        if ($site) {
            if ($site->is_denial) {
                return false;
            } elseif ($site->is_main) { // если заказ в буферном магазине, то считаем его просроченным для принятия, чтобы сразу переназначить
                return true;
            }
        } else {
            return false;
        }

        if ($this->is_accepted) {
            return false;
        } else {
            // логика определения просроченности
            $currentDate = new DateTime();
            $receivedDate = (new DateTime())->setTimestamp($this->site_received_at);

            if ($site->timezone) {
                $currentDate->setTimezone(new DateTimeZone($site->timezone));
                $receivedDate->setTimezone(new DateTimeZone($site->timezone));
            }

            $receivedHour = $receivedDate->format('G');
            if ($receivedHour < self::TIME_OPEN_HOUR && $receivedHour > self::TIME_CLOSE_HOUR) {
                $receivedDate->setTime(self::TIME_OPEN_HOUR, 0);
            }

            $deliveryDate = null;
            if ($this->delivery_date) {
                $time = null;
                if ($this->delivery_time) {
                    $time = trim(substr($this->delivery_time, 2, 5));
                } else {
                    $time = self::TIME_CLOSE_HOUR . ':00';
                }
                $deliveryDate = DateTime::createFromFormat('Y-m-d H:i', ($this->delivery_date . ' ' . $time), new DateTimeZone($site->timezone ?: SiteModel::TIMEZONE_MOSCOW));
            }

            if (!$deliveryDate || ($receivedDate < $deliveryDate && $receivedDate->diff($deliveryDate)->d >= 1)) {
                $receivedDate->add(new DateInterval(self::MAX_EXPIRED_INTERVAL));
            } else {
                $receivedDate->add(new DateInterval(self::MIN_EXPIRED_INTERVAL));
            }

            if ($receivedDate < $currentDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function accept()
    {
        $this->is_accepted = true;
        $this->status_id = StatusModel::find()->select('id')->where(['code' => self::STATUS_PARTNER_CONFIRMED])->scalar();
        return $this->save(false);
    }

    /**
     * Переназначение магазина
     * @return array|SiteModel|mixed|ActiveRecord|null
     * @throws InvalidConfigException
     */
    public function reassign()
    {
        $currentSite = $this->site;

        if (!$currentSite || $currentSite->is_denial) {
            return null;
        }

        $parent = null;
        if ($currentSite->hasParent()) {
            $parent = $currentSite->parent;
        } elseif ($currentSite->hasChildren()) {
            $parent = $currentSite;
        } else {
            return null;
        }

        $children = $parent->children;
        if (empty($children)) {
            return null;
        }

        $parts = [];
        foreach ($children as $child) {
            if ($child->id == $currentSite->id) {
                continue;
            }
            if (OrderSiteDenialModel::find()->where(['order_id' => $this->id, 'site_id' => $child->id])->exists()) {
                continue;
            }
            for ($i = 0; $i < $child->probability; $i++) {
                $parts[] = $child;
            }
        }

        // если нет ни одного магазина для назначения, то перенаправляем на отказной магазин
        if (empty($parts)) {
            $denialSite = SiteModel::find()->where(['active' => true, 'is_denial' => true])->one();
            if (!$denialSite) {
                return null;
            }
            if ($this->changeSite($denialSite, self::STATUS_CONFIRMED)) {
                OrderSiteDenialModel::deleteAll(['order_id' => $this->id]);
                return $denialSite;
            }
            return null;
        }

        // получаем случайный магазин с нужной вероятностью
        $newSite = $parts[array_rand($parts)];
        if ($this->changeSite($newSite, self::STATUS_CONFIRMED)) {
            if (!$currentSite->is_main) {
                (new OrderSiteDenialModel([
                    'order_id' => $this->id,
                    'site_id' => $currentSite->id,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]))->save(false);
            }
            $this->sendTgNotification($newSite);
            return $newSite;
        }

        return null;
    }

    /**
     * @param SiteModel $site
     */
    private function sendTgNotification($site)
    {
        try {
            $bot = TgBot::instance();
            $chats = $site->tgChats;
            if (empty($chats)) {
                return;
            }
            if ($site->hasParent()) {
                $keyboard = new InlineKeyboardMarkup([
                    [
                        [
                            'text' => 'Посмотреть',
                            'url' => Yii::$app->params['siteUrl'] . '/order/view/' . $this->id,
                        ]
                    ],
                    [
                        [
                            'text' => 'Принять',
                            'callback_data' => json_encode(['id' => $this->id, 'action' => 'accept', 'token' => $this->token])
                        ],
                        [
                            'text' => 'Отклонить',
                            'callback_data' => json_encode(['id' => $this->id, 'action' => 'reject', 'token' => $this->token])
                        ]
                    ],
                ]);
            } else {
                $keyboard = new InlineKeyboardMarkup([
                    [
                        [
                            'text' => 'Посмотреть',
                            'url' => Yii::$app->params['siteUrl'] . '/order/view/' . $this->id,
                        ]
                    ],
                ]);
            }
            foreach ($chats as $chat) {
                $bot->sendMessage($chat, "Получен новый заказ №{$this->crm_id}", null, false, null, $keyboard);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @param $newSite
     * @param $code
     * @return bool
     * @throws InvalidConfigException
     * @throws Exception
     */
    private function changeSite($newSite, $code)
    {
        $crm = RetailCrm::getInstance();
        $resp = $crm->request->ordersEdit([
            'id' => $this->crm_id,
            'status' => $code,
            'customFields' => ['set_site' => $newSite->code],
        ], 'id', $this->site->code);

        if ($resp->isSuccessful()) {
            $this->site_received_at = time();
            $this->site_id = $newSite->id;
            $this->token = Yii::$app->security->generateRandomString(15);
            $this->save(false);
            return true;
        }

        return false;
    }
}
