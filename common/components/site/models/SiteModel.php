<?php

namespace common\components\site\models;

use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class SiteModel
 * @package common\components\site\models
 * @property int $id [int(11)]
 * @property string $name [varchar(255)]
 * @property string $code [varchar(255)]
 * @property bool $active [tinyint(1)]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 * @property int $parent_id [int(11)]
 * @property int $probability [int(11)]
 * @property string $timezone [varchar(255)]
 * @property bool $is_main [tinyint(1)]
 * @property bool $is_denial [tinyint(1)]
 *
 * @property-read SiteModel[] $children
 * @property-read User[] $users
 * @property-read array $tgChats
 * @property-read SiteModel $parent
 */
class SiteModel extends ActiveRecord
{
    const TIMEZONE_KALININGRAD = 'Europe/Kaliningrad';
    const TIMEZONE_MOSCOW = 'Europe/Moscow';
    const TIMEZONE_SAMARA = 'Europe/Samara';
    const TIMEZONE_YEKATERINBURG = 'Asia/Yekaterinburg';
    const TIMEZONE_OMSK = 'Asia/Omsk';
    const TIMEZONE_KRASNOYARSK = 'Asia/Krasnoyarsk';
    const TIMEZONE_IRKUTSK = 'Asia/Irkutsk';
    const TIMEZONE_YAKUTSK = 'Asia/Yakutsk';
    const TIMEZONE_VLADIVOSTOK = 'Asia/Vladivostok';
    const TIMEZONE_SREDNEKOLYMSK = 'Asia/Srednekolymsk';
    const TIMEZONE_KAMCHATKA = 'Asia/Kamchatka';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%site}}';
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
            'id' => 'ID',
            'name' => 'Название',
            'code' => 'Код',
            'active' => 'Активен',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
            'probability' => 'Пропорция распределения',
            'timezone' => 'Часовой пояс',
            'is_main' => 'Главный',
            'is_denial' => 'Отказной',
            'parent_id' => 'Родительский магазин',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return !empty($this->parent_id);
    }

    /**
     * @return ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['site_id' => 'id']);
    }

    /**
     * @return array
     */
    public function getTgChats()
    {
        $data = [];

        $users = $this->users;
        if (empty($users)) {
            return $data;
        }

        foreach ($users as $user) {
            $userChats = $user->tgChats;
            if (!empty($userChats)) {
                foreach ($userChats as $chat) {
                    if (!in_array($chat, $data)) {
                        $data[] = $chat;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return string[]
     */
    public static function TimezoneList()
    {
        return [
            self::TIMEZONE_KALININGRAD => 'Калининград (MSK-1)',
            self::TIMEZONE_MOSCOW => 'Москва',
            self::TIMEZONE_SAMARA => 'Самара (MSK+1)',
            self::TIMEZONE_YEKATERINBURG => 'Екатеринбург (MSK+2)',
            self::TIMEZONE_OMSK => 'Омск (MSK+3)',
            self::TIMEZONE_KRASNOYARSK => 'Красноярск (MSK+4)',
            self::TIMEZONE_IRKUTSK => 'Иркутск (MSK+5)',
            self::TIMEZONE_YAKUTSK => 'Якутск (MSK+6)',
            self::TIMEZONE_VLADIVOSTOK => 'Владивосток (MSK+7)',
            self::TIMEZONE_SREDNEKOLYMSK => 'Среднеколымск (MSK+8)',
            self::TIMEZONE_KAMCHATKA => 'Камчатка (MSK+9)',
        ];
    }
}
