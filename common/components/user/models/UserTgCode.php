<?php

namespace common\components\user\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserTgCode
 * @package common\models
 * @property int $id [int(11)]
 * @property int $user_id [int(11)]
 * @property string $code [varchar(255)]
 * @property bool $is_activated [tinyint(1)]
 * @property int $created_at [int(11)]
 */
class UserTgCode extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_tg_code}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_activated'], 'boolean'],
        ];
    }
}
