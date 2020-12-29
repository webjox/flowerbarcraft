<?php

namespace crm\modules\notifications\models;

use common\components\user\models\UserTgCode;
use common\models\User;
use Yii;
use yii\base\Exception;

/**
 * Class UserTgCodes
 * @package crm\modules\notifications\models
 */
class UserTgCodes extends UserTgCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['is_activated'], 'boolean'],
            [['code'], 'string'],
            [['user_id'], 'validateUser'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateUser($attribute, $params)
    {
        $user = User::findOne($this->$attribute);
        if (!$user) {
            $this->addError($attribute, 'Пользователя не существует.');
        }
    }

    /**
     * @param $userId
     * @return string|null
     * @throws Exception
     */
    public static function generateCode($userId)
    {
        $model = new self();
        $model->user_id = $userId;
        $model->code = Yii::$app->security->generateRandomString(8);
        $model->is_activated = false;
        if ($model->save()) {
            return $model->code;
        }
        return null;
    }
}
