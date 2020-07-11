<?php

namespace crm\modules\settings\controllers;

use crm\modules\settings\forms\SettingsForm;
use Yii;
use yii\web\Controller;

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

        return $this->render('index', ['model' => $model]);
    }
}
