<?php

namespace console\controllers;

use common\components\order\models\OrderModel;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Class OrderController
 * @package console\controllers
 */
class OrderController extends Controller
{
    /**
     * Ежеминутно отслеживаем заказы, распределяем и тд
     * @return int
     */
    public function actionWatch()
    {
        /* @var $orders OrderModel[] */
        $orders = OrderModel::find()
            ->with('site')
            ->joinWith('site')
            ->where(['is_accepted' => false])
            ->andWhere(['site.is_denial' => false])
            ->all();

        if (!empty($orders)) {
            foreach ($orders as $order) {
                try {
                    if ($order->isAcceptingExpired()) { // заказ просрочен для принятия
                        // распределяем
                        $order->reassign();
                    }
                } catch (Exception $e) {
                    Yii::info("Возникла ошибка при переназначении заказа #{$order->crm_id}", 'retailcrm');
                }
            }
        }

        return ExitCode::OK;
    }
}
