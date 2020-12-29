<?php

namespace crm\modules\notifications\controllers;

use common\components\user\models\UserTgChat;
use crm\modules\notifications\models\UserChatsSearch;
use crm\modules\notifications\models\UserTgCodes;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class DefaultController
 * @package crm\modules\notifications\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $tgChatsSearchModel = new UserChatsSearch(['user_id' => Yii::$app->user->id]);
        $tgChatsDataProvider = $tgChatsSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'tgChatsDataProvider' => $tgChatsDataProvider,
        ]);
    }

    /**
     * @param null $userId
     * @return array|null
     * @throws Exception
     */
    public function actionGenerateTgCode($userId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user;
        if (!$user->can('root') || $userId === null) {
            $userId = $user->id;
        }

        $code = UserTgCodes::generateCode($userId);

        if (!empty($code)) {
            return ['code' => $code];
        }

        return null;
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionTgDelete($id)
    {
        $userId = Yii::$app->user->id;
        if (($model = UserTgChat::findOne(['id' => $id, 'user_id' => $userId])) !== null) {
            $model->delete();
            return $this->redirect(['index']);
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
