<?php

namespace common\components\user\models;

use yii\db\ActiveRecord;

/**
 * Class UserTgChat
 * @package common\models
 * @property int $id [int(11)]
 * @property int $user_id [int(11)]
 * @property int $chat_id [int(11)]
 * @property string $tg_username [varchar(255)]
 */
class UserTgChat extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_tg_chat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chat_id', 'user_id'], 'integer'],
            [['tg_username'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tg_username' => 'Аккаунт',
        ];
    }
}
