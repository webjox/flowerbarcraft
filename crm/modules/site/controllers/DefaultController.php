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

        if ($model->load(Yii::$app->request->post()) ) {

            $address = $model->city;
            $parameters = array(
                'apikey' => '10d0aae4-eb7c-4e41-91f3-1d6217a9b798',
                'geocode' => $address,
                'format' => 'json'
            );
            $response = file_get_contents('https://geocode-maps.yandex.ru/1.x/?'. http_build_query($parameters));
            $obj = json_decode($response, true);
            if($obj) {
                $cord = explode(' ', $obj['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos']);
                $model->city_lon=$cord[0];
                $model->city_lat=$cord[1];
            }
            $model->save();
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(['list']);
        }

        return $this->render('update', ['model' => $model]);
    }
}
