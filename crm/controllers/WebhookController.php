<?php

namespace crm\controllers;

use common\components\order\entities\Order;
use common\components\order\models\OrderModel;
use crm\modules\order\models\OrderWebhookModel;
use Exception;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class WebhookController
 * @package crm\controllers
 */
class WebhookController extends Controller
{
    public $enableCsrfValidation = false;

    private $token = 'JgvFyjLoi7gr5t89hG77TRty67ckl3456Ftg3f';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws Throwable
     */
    public function actionOrderChanged()
    {
        if ($this->token != Yii::$app->request->post('token')) {
            throw new NotFoundHttpException();
        }

        try {
            $order = Order::create(Yii::$app->request->post());
            $orderModel = OrderModel::findOne(['crm_id' => $order->crm_id]);
            $model = null;
            if ($orderModel || $order->isConfirmed()) {
                Yii::info("Старт процесса: Синхронизация заказа #{$order->crm_id} по триггеру", 'retailcrm');
                $model = new OrderWebhookModel();
                if ($model->load($order->asArray()) && $model->save()) {
                    Yii::info("Завершение процесса: Синхронизация заказа #{$order->crm_id} по триггеру\nСтатус: success", 'retailcrm');
                } else {
                    Yii::info($order->asArray(), 'retailcrm');
                    Yii::info("Завершение процесса: Синхронизация заказа #{$order->crm_id} по триггеру\nСтатус: fail", 'retailcrm');
                }
            }
        } catch (Exception $e) {
            Yii::info("Ошибка при синхронизации заказа по триггеру\n" . $e->getMessage(), 'retailcrm');
        }
        return 'ok';
    }
}
