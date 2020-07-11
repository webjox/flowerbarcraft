<?php

namespace common\components\user\models;

use common\models\User;
use Yii;
use yii\base\Exception;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserManage
 * @package common\components\user\models
 */
class UserManage extends User
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    protected $_password;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key',
                ],
                'value' => function ($event) {
                    return Yii::$app->security->generateRandomString();
                },
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @param $password
     * @throws Exception
     */
    public function setPassword($password)
    {
        $this->_password = $password;
        if ($this->_password) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->_password);
        }
    }

    /**
     * @return array
     */
    public static function groupList()
    {
        return [
            self::GROUP_ADMIN => 'Администратор',
            self::GROUP_FLORIST => 'Флорист',
        ];
    }

    /**
     * @param $groupId
     * @return string|null
     */
    public static function getGroupName($groupId)
    {
        $list = self::groupList();
        return $list[$groupId] ?? null;
    }

    /**
     * @return array
     */
    public static function statusList()
    {
        return [
            self::STATUS_ACTIVE => 'Активен',
            self::STATUS_DELETED => 'Заблокирован',
        ];
    }

    /**
     * @param $statusId
     * @return string|null
     */
    public static function getStatusName($statusId)
    {
        $list = self::statusList();
        return $list[$statusId] ?? null;
    }
}
