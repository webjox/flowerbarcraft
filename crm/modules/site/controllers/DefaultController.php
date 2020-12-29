<?php

namespace crm\modules\site\controllers;

use crm\modules\site\models\Site;
use crm\modules\site\models\SiteSearchModel;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 * @package crm\modules\site\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionList()
    {
        $searchModel = new SiteSearchModel();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = Site::findOne(['id' => $id, 'active' => true]);

        if (!$model) {
            throw new NotFoundHttpException('Магазин не найден');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(['list']);
        }

        return $this->render('update', ['model' => $model]);
    }
}
