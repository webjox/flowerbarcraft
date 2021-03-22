<?php

namespace crm\modules\user\controllers;

use common\components\user\models\AdminManage;
use common\components\user\models\FloristManage;
use common\components\user\models\UserDelivery;
use common\components\user\models\UserManage;
use crm\modules\user\models\UserSearch;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class DefaultController
 * @package crm\modules\user\controllers
 */
class DefaultController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionList()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreateAdmin()
    {
        $model = new AdminManage(['scenario' => AdminManage::SCENARIO_CREATE]);
        return $this->create($model, 'Администратор успешно добавлен');
    }

    /**
     * @return string|Response
     */
    public function actionCreateFlorist()
    {
        $model = new FloristManage(['scenario' => FloristManage::SCENARIO_CREATE]);
        return $this->create($model, 'Сотрудник успешно добавлен');
    }

    /**
     * @param $model
     * @param string $successMessage
     * @return string|Response
     */
    private function create($model, $successMessage = 'Пользователь успешно добавлен')
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $successMessage);
            return $this->redirect(['list']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateAdmin($id)
    {
        $model = AdminManage::find()->where(['group' => AdminManage::GROUP_ADMIN, 'id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException('Администратор не найден.');
        }

        $model->scenario = UserManage::SCENARIO_UPDATE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Администратор успешно обновлен');
            return $this->redirect(['list']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateFlorist($id)
    {
        /* @var $model FloristManage */
        $model = FloristManage::find()->where(['group' => FloristManage::GROUP_FLORIST, 'id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException('Флорист не найден.');
        }

        $model->scenario = UserManage::SCENARIO_UPDATE;
        $delivery = $model->delivery ?: new UserDelivery(['user_id' => $model->id]);
        $request = Yii::$app->request;
        if ($model->load($request->post()) && $delivery->load($request->post())) {
            if ($model->save() && $delivery->save()) {
                Yii::$app->session->setFlash('success', 'Флорист успешно обновлен');
                return $this->redirect(['list']);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'delivery' => $delivery,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = UserManage::findOne(['id' => $id]);

        if (!$model) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        if ($model->username == 'florist-admin') {
            throw new ForbiddenHttpException('Невозможно удалить администратора с логином florist-admin');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Пользователь успешно удален');
        }

        return $this->redirect(['list']);
    }
}
