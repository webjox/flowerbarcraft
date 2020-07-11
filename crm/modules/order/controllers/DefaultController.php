<?php

namespace crm\modules\order\controllers;

use yii\web\Controller;

/**
 * Class DefaultController
 * @package crm\modules\order\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionList()
    {
        return $this->render('list');
    }
}
