<?php

namespace crm\modules\settings\controllers;

use crm\modules\settings\forms\SettingsForm;
use crm\modules\settings\models\Status;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 * @package crm\modules\settings\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $model = new SettingsForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Настройки успешно сохранены');
            return $this->refresh();
        }

        $statusesDataProvider = new ActiveDataProvider([
            'query' => Status::find(),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('index', [
            'model' => $model,
            'statusesDataProvider' => $statusesDataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException]
     */
    public function actionStatusUpdate($id)
    {
        $model = Status::findOne(['id' => $id]);

        if (!$model) {
            throw new NotFoundHttpException('Статус не найден');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Статус \"{$model->name}\" успешно обновлен");
            return $this->redirect(['index']);
        }

        return $this->render('status-update', ['model' => $model]);
    }
}
