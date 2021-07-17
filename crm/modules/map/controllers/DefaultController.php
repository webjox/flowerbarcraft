<?php

namespace crm\modules\map\controllers;

use Cassandra\Map;
use common\components\user\models\UserTgChat;
use common\models\User;
use crm\modules\map\models\MapForm;
use crm\modules\notifications\models\UserChatsSearch;
use crm\modules\notifications\models\UserTgCodes;
use crm\modules\order\models\Order;
use crm\modules\site\models\Site;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class DefaultController
 * @package crm\modules\map\controllers
 */
class DefaultController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => [User::ROLE_FLORIST],
                    ],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        $site = Site::find()->where(['id' => Yii::$app->user->identity->site_id])->one();
//        $sinh = Order::find()->where(['>=','crm_id','108000'])->all();
//        foreach ($sinh as $item){
//            $value = (int)preg_replace("/[^0-9]/", '', $item->delivery_time);
//            if ($value > 2359) {
//                $value = (int)($value / 10000);
//            }
//            $item->delivery_time_ordering_start =$value;
//            $item->save();
//        }
        $orders = Order::find()
            ->where(['<>', 'status_id', '2'])
            ->andWhere(['site_id' => $site->id])
            ->andWhere(['=', 'delivery_date', date("Y-m-d")])
            ->andWhere(['delivery_type' => 'Доставка курьером'])
            ->andWhere(['is not', 'delivery_address_geo_lon', null])
            ->andWhere(['is not', 'delivery_address_geo_lat', null])
            ->all();

        $orders_error = Order::find()
            ->where(['<>', 'status_id', '2'])
            ->andWhere(['site_id' => $site->id])
            ->andWhere(['=', 'delivery_date', date("Y-m-d")])
            ->andWhere(['delivery_type' => 'Доставка курьером'])
            ->andWhere(['is', 'delivery_address_geo_lon', null])
            ->andWhere(['is', 'delivery_address_geo_lat', null])
            ->all();
        $model = new MapForm();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $orders = Order::find()
//                ->where(['<>', 'status_id', '2'])
                ->andWhere(['site_id' => $site->id])
                ->andWhere(['delivery_type' => 'Доставка курьером'])
                ->andWhere(['is not', 'delivery_address_geo_lon', null])
                ->andWhere(['is not', 'delivery_address_geo_lat', null]);
            $orders_error = Order::find()
//              ->where(['<>', 'status_id', '2'])
                ->andWhere(['site_id' => $site->id])
                ->andWhere(['delivery_type' => 'Доставка курьером'])
                ->andWhere(['is', 'delivery_address_geo_lon', null])
                ->andWhere(['is', 'delivery_address_geo_lat', null]);
            if ($model['status']) {
                $orders = $orders->andWhere(['in','status_id',$model['status']]);
                $orders_error = $orders_error->andWhere(['status_id' => $model['status']]);
            }
            if ($model['dateFrom'] || $model['dateTo']) {
                if ($model['dateFrom']) {
                    $orders = $orders->andWhere(['>=', 'delivery_date', $model['dateFrom']]);
                    $orders_error = $orders_error->andWhere(['>=', 'delivery_date', $model['dateFrom']]);
                }
                if ($model['dateTo']) {
                    $orders = $orders->andWhere(['<=', 'delivery_date', $model['dateTo']]);
                    $orders_error = $orders_error->andWhere(['<=', 'delivery_date', $model['dateTo']]);
                }
            } else {
                $orders = $orders->andWhere(['=', 'delivery_date', date("Y-m-d")]);
                $orders_error = $orders_error->andWhere(['=', 'delivery_date', date("Y-m-d")]);
            }
            if ($model['timeFrom'] || $model['timeTo']) {
                if ($model['timeFrom']) {
                    $timeFrom = (int)preg_replace("/[^0-9]/", '', $model['timeFrom']);
                    $orders = $orders->andWhere(['>=', 'delivery_time_ordering_start', $timeFrom]);
                    $orders_error = $orders_error->andWhere(['>=', 'delivery_time_ordering_start', $timeFrom]);
                }
                if ($model['timeTo']) {
                    $timeTo = (int)preg_replace("/[^0-9]/", '', $model['timeTo']);
                    $orders = $orders->andWhere(['<=', 'delivery_time_ordering', $timeTo]);
                    $orders_error = $orders_error->andWhere(['<=', 'delivery_time_ordering', $timeTo]);
                }
            }
            $orders = $orders->all();
            $orders_error = $orders_error->all();
        }

        return $this->render('index', [
            'orders' => $orders,
            'orders_error' => $orders_error,
            'site' => $site,
            'model' => $model,
        ]);
    }
}
