<?php

namespace crm\controllers;

use common\components\order\models\OrderModel;
use common\components\tg\TgBot;
use common\components\user\models\UserTgChat;
use common\components\user\models\UserTgCode;
use common\models\User;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class TgController
 * @package crm\controllers
 */
class TgController extends Controller
{
    public $enableCsrfValidation = false;

    private $token = 'jsdifou9u4iorhf89yhcusdf89w83dyihwed';

    /**
     * @param $token
     * @return string
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function actionCallback($token)
    {
        if ($this->token !== $token) {
            throw new NotFoundHttpException();
        }

        $bot = TgBot::instance();

        $post = json_decode(Yii::$app->request->getRawBody(), true);

        // управление заказами
        if (!empty($post['callback_query'])) {
            return $this->controlOrder($post['callback_query'], $bot);
        }

        $chatId = !empty($post['message']['chat']['id']) ? $post['message']['chat']['id'] : null;
        $nameData = [];
        if (!empty($post['message']['chat']['first_name'])) {
            $nameData[] = $post['message']['chat']['first_name'];
        }
        if (!empty($post['message']['chat']['last_name'])) {
            $nameData[] = $post['message']['chat']['last_name'];
        }
        if (!empty($post['message']['chat']['username'])) {
            $nameData[] = "({$post['message']['chat']['username']})";
        }
        $name = implode(' ', $nameData);
        $msg = !empty($post['message']['text']) ? $post['message']['text'] : null;

        if (empty($chatId)) {
            return 'ok';
        }

        if ($chatModel = UserTgChat::findOne(['chat_id' => $chatId])) {
            $bot->sendMessage($chatModel->chat_id, 'Ваш аккаунт уже привязан! Вы можете его отвязать через личный кабинет.');
            return 'ok';
        }

        if ($msg === '/start') {
            $bot->sendMessage($chatId, 'Введите код для активации уведомлений.');
            return 'ok';
        }

        $msg = str_replace('/start ', '', $msg);
        $codeModel = UserTgCode::findOne(['code' => $msg]);
        if (!$codeModel) {
            $bot->sendMessage($chatId, 'Введенный код не распознан. Если ошибка повторится, попробуйте сгенерировать новый код в личном кабинете.');
            return 'ok';
        } elseif ($codeModel->is_activated) {
            $bot->sendMessage($chatId, 'Этот код уже был активирован. Если вы хотите привязать еще один telegram аккаунт, то сгенерируйте новый код в личном кабинете.');
            return 'ok';
        } elseif ($codeModel->created_at + 3600 < time()) {
            $bot->sendMessage($chatId, 'Код просрочен. Пожалуйста, сгенерируйте новый код в личном кабинете.');
            return 'ok';
        }

        $newChatModel = new UserTgChat();
        $newChatModel->chat_id = $chatId;
        $newChatModel->user_id = $codeModel->user_id;
        $newChatModel->tg_username = $name;

        if ($newChatModel->save()) {
            $codeModel->is_activated = true;
            $codeModel->save();
            $bot->sendMessage($chatId, 'Аккаунт успешно привязан!');
        }

        return 'ok';
    }

    /**
     * Управление заказами из TG бота
     * @param $callbackQuery
     * @param $bot
     * @return string
     */
    private function controlOrder($callbackQuery, $bot)
    {
        $chatId = !empty($callbackQuery['from']['id']) ? $callbackQuery['from']['id'] : null;
        $data = !empty($callbackQuery['data']) ? json_decode($callbackQuery['data'], true) : null;
        if (empty($chatId) || empty($data)) {
            return 'ok';
        }
        $chatModel = UserTgChat::findOne(['chat_id' => $chatId]);
        if (!$chatModel) {
            $bot->sendMessage($chatId, 'Ваш аккаунт не привязан! Чтобы можно было управлять заказами через бота, нужно привязать аккаунт в настройках оповещений.');
            return 'ok';
        }
        $user = User::findOne(['id' => $chatModel->user_id]);
        $order = OrderModel::find()->where(['id' => $data['id'], 'site_id' => $user->site_id, 'token' => $data['token']])->one();
        if (!$order) {
            $bot->sendMessage($chatModel->chat_id, 'Заказ не найден. Возможно он передан в другой магазин.');
            return 'ok';
        }

        if ($data['action'] == 'accept') {
            if ($order->is_accepted) {
                $bot->sendMessage($chatModel->chat_id, "Заказ №{$order->crm_id} уже принят.");
            } else {
                if ($order->accept()) {
                    $bot->sendMessage($chatModel->chat_id, "Заказ №{$order->crm_id} успешно принят.");
                } else {
                    $bot->sendMessage($chatModel->chat_id, "Не удалось принять заказ №{$order->crm_id}. Попробуйте ещё раз.");
                }
            }
        } elseif ($data['action'] == 'reject') {
            if ($order->is_accepted) {
                $bot->sendMessage($chatModel->chat_id, "Заказ №{$order->crm_id} уже был принят.");
            } else {
                if ($order->reassign()) {
                    $bot->sendMessage($chatModel->chat_id, "Заказ №{$order->crm_id} передан другому магазину.");
                } else {
                    $bot->sendMessage($chatModel->chat_id, "Не удалось передать заказ №{$order->crm_id} другому магазину. Попробуйте ещё раз.");
                }
            }
        }

        return 'ok';
    }
}
