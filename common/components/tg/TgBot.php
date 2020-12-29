<?php

namespace common\components\tg;

use TelegramBot\Api\BotApi;
use Yii;

/**
 * Class TgBot
 * @package common\components\tg
 */
class TgBot
{
    /**
     * @return BotApi
     */
    public static function instance()
    {
        return new BotApi(Yii::$app->params['tg']['token'] ?? null);
    }
}
