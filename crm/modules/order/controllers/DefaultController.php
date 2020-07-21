<?php

namespace crm\modules\order\controllers;

use common\models\User;
use crm\modules\order\models\Order;
use crm\modules\order\models\OrderSearch;
use kartik\grid\EditableColumnAction;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 * @package crm\modules\order\controllers
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
                        'actions' => ['update-status'],
                        'roles' => ['inSite'],
                        'roleParams' => function ($rule) {
                            $siteId = null;
                            $order = Order::findOne(['id' => Yii::$app->request->post('editableKey')]);
                            if ($order) {
                                $siteId = $order->site_id;
                            }
                            return ['id' => $siteId];
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['list', 'view'],
                        'roles' => [User::ROLE_FLORIST],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'update-status' => [
                'class' => EditableColumnAction::class,
                'modelClass' => Order::class,
                'outputValue' => function ($model) {
                    return $model->status ? Html::tag('span', $model->status->name, [
                        'class' => 'btn btn-status',
                        'style' => "background: {$model->status->bgColor}"
                    ]) : '-';
                },
            ]
        ]);
    }

    /**
     * @return string
     */
    public function actionList()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->find($id);

        return $this->render('view', ['model' => $model]);
    }

    /**
     * @param $id
     * @return Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function find($id)
    {
        $model = Order::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('Заказ не найден');
        }

        if (!Yii::$app->user->can('inSite', ['id' => $model->site_id])) {
            throw new ForbiddenHttpException('Доступ запрещен.');
        }

        return $model;
    }
}
