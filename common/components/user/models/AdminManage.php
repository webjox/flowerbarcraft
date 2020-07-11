<?php

namespace common\components\user\models;

use yii\behaviors\AttributeBehavior;

/**
 * Class AdminManage
 * @package common\components\user\models
 */
class AdminManage extends UserManage
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => AttributeBehavior::class,
            'attributes' => [
                self::EVENT_BEFORE_INSERT => 'group',
            ],
            'value' => self::GROUP_ADMIN,
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['username', 'password', 'name', 'email'],
            self::SCENARIO_UPDATE => ['password', 'status', 'name', 'email'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'name', 'email'], 'required', 'on' => self::SCENARIO_CREATE],
            ['status', 'default', 'value' => self::STATUS_ACTIVE, 'on' => self::SCENARIO_CREATE],
            [['status', 'name', 'email'], 'required', 'on' => [self::SCENARIO_UPDATE]],
            ['email', 'email'],
            ['status', 'validateStatus'],
            [['status'], 'integer'],
            ['password', 'string', 'min' => 8,  'max' => 256],
            ['status', 'in', 'range' => array_keys(self::statusList())],
            [['username', 'email'], 'unique'],
        ];
    }

    /**
     * Валидация статуса пользователя
     */
    public function validateStatus()
    {
        if ($this->username == 'florist-admin' && $this->status == self::STATUS_DELETED) {
            $this->addError('status', 'Невозможно заблокировать администратора с логином florist-admin');
        }
    }
}
