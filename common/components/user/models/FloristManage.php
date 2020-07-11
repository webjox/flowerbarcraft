<?php

namespace common\components\user\models;

use common\components\site\models\SiteModel;
use yii\behaviors\AttributeBehavior;

/**
 * Class FloristManage
 * @package common\components\user\models
 */
class FloristManage extends UserManage
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
            'value' => self::GROUP_FLORIST,
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['username', 'password', 'name', 'email', 'site_id'],
            self::SCENARIO_UPDATE => ['password', 'status', 'name', 'email', 'site_id'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'name', 'email', 'site_id'], 'required', 'on' => self::SCENARIO_CREATE],
            ['status', 'default', 'value' => self::STATUS_ACTIVE, 'on' => self::SCENARIO_CREATE],
            [['status', 'name', 'email', 'site_id'], 'required', 'on' => [self::SCENARIO_UPDATE]],
            ['email', 'email'],
            [['status', 'site_id'], 'integer'],
            ['password', 'string', 'min' => 8,  'max' => 256],
            ['status', 'in', 'range' => array_keys(self::statusList())],
            [['username', 'email'], 'unique'],
            ['site_id', 'exist', 'targetClass' => SiteModel::class, 'targetAttribute' => ['site_id' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public static function getSitesList()
    {
        return SiteModel::find()->select(['name', 'id'])->where(['active' => true])->indexBy('id')->column();
    }
}
