<?php

namespace crm\modules\order\controllers;

use common\components\order\models\OrderDeliveryModel;
use crm\modules\order\models\OrderImage;
use common\components\settings\models\SettingsModel;
use common\components\user\models\UserDelivery;
use common\components\yandexGoDelivery\YaDeliveryClient;
use common\models\User;
use crm\modules\order\models\Order;
use crm\modules\order\models\OrderSearch;
use crm\modules\settings\forms\SettingsForm;
use crm\modules\settings\models\Status;
use crm\modules\site\models\Site;
use kartik\grid\EditableColumnAction;
use kartik\mpdf\Pdf;
use Mpdf\MpdfException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use Swift_Image;
use Symfony\Component\String\ByteString;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\httpclient\Client;
use function MongoDB\BSON\toJSON;
use yii\imagine\Image;
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
                        'actions' => ['list', 'view', 'download', 'accept', 'reject', 'create-delivery', 'get-delivery-status', 'cancel-delivery', 'accept-delivery','upload','delete','load-image','list-personal','list-paint','list-build'],
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
                     if($model->status->name=="Заказ принят") {
                        $model->user_id = " ";
                        $model->statusCrm = 1;
                        $model->save();
                    }
                    if($model->status->name=="В РАБОТЕ") {
                        $site = Site::find()->where(['id'=>$model->site_id])->one();
                        if($site['is_active_personal_area']){
                            $model->user_id = Yii::$app->user->getIdentity()->id;;
                            $model->statusCrm = 2;
                            $model->save();
                        }
                    }
                    if($model->status->name=="В покраске ") {
                        $model->user_id = Yii::$app->user->getIdentity()->id;
                        $model->statusCrm = 2;
                        $model->save();
                    }
                    if($model->status->name=="Покрашен") {
                        $model->user_id = Yii::$app->user->getIdentity()->id;
                        $model->statusCrm = 3;
                        $model->save();
                    }
                    if($model->status->name=="Собирается") {
                        $model->user_id = Yii::$app->user->getIdentity()->id;
                        $model->statusCrm = 2;
                        $model->save();
                    }
                    return $model->status ? Html::tag('span', $model->status->name, [
                        'class' => 'btn btn-status',
                        'style' => "background: {$model->status->bgColor}"
                    ])  : '-';
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
        $site = Site::find()->where(['id'=>Yii::$app->user->identity->site_id])->one();

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'site'=>$site,
        ]);
    }

    public function actionListPersonal()
    {
        $searchModel = new OrderSearch();
        $site = Site::find()->where(['id'=>Yii::$app->user->identity->site_id])->one();
        if($site['is_active_personal_area']) {
            $dataProvider = $searchModel->searchPersonal(Yii::$app->request->queryParams);
        }else{
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'site'=>$site,
        ]);
    }

    public function actionListPaint()
    {
        $searchModel = new OrderSearch();
        $site = Site::find()->where(['id'=>Yii::$app->user->identity->site_id])->one();
        if($site['is_active_personal_area']) {
            $dataProvider = $searchModel->searchPaint(Yii::$app->request->queryParams);
        }else{
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'site'=>$site,
        ]);
    }

    public function actionListBuild()
    {
        $searchModel = new OrderSearch();
        $site = Site::find()->where(['id'=>Yii::$app->user->identity->site_id])->one();
        if($site['is_active_personal_area']) {
            $dataProvider = $searchModel->searchBuild(Yii::$app->request->queryParams);
        }else{
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'site'=>$site,
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
        OrderImage::deleteAll(['order_id'=>$model['crm_id']]);
        $input = new Order();
        $input_comment = new Order();
        $yaDeliveryModel = null;
        $delivery = Yii::$app->user->identity->delivery ?? null;
        if ($delivery && $delivery->active) {
            if ($model->yaDelivery) {
                $yaDeliveryModel = $model->yaDelivery;
            } else {
                $yaDeliveryModel = new OrderDeliveryModel([
                    'source_city' => $delivery->city,
                    'source_street' => $delivery->street,
                    'source_building' => $delivery->building,
                    'source_floor' => $delivery->floor,
                    'source_flat' => $delivery->flat,
                    'source_sender_name' => $delivery->sender_name,
                    'source_sender_phone' => $delivery->sender_phone,
                    'source_comment' => $delivery->comment ?: "Доставка из магазина FlowerBarKraft.\nСообщите менеджеру, что заказ по доставке Яндекс.Такси.\nНазовите номер заказа {$model->number} и заберите посылку.\nЗаказ оплачен безналично, при передаче заказа нельзя требовать с получателя деньги за доставку.",
                    'destination_city' => $model->delivery_address_city ?: $delivery->city,
                    'destination_street' => $model->delivery_address_street,
                    'destination_building' => implode(' ', array_filter([
                        $model->delivery_address_building,
                        $model->delivery_address_house,
                        $model->delivery_address_housing,
                        $model->delivery_address_block
                    ], function ($item) {
                        return !empty($item);
                    })),
                    'destination_floor' => $model->delivery_address_floor,
                    'destination_flat' => $model->delivery_address_flat,
                    'destination_recipient_name' => $model->recipient_name,
                    'destination_recipient_phone' => preg_replace('/^[7,8]/', '+7', $model->recipient_phone),
                    'destination_comment' => $model->delivery_address_notes,
                ]);
            }
        }
        if(Yii::$app->request->isPost&&$input->load(Yii::$app->request->post())){
            $post = Yii::$app->request->post();
            $order = Yii::$app->request->post('Order');
            $input->crm = UploadedFile::getInstances($input,'crm');
            $input->comment = $order['comment'];
            if($input->validate()){
                if($input->crm) {

                }
                else {
                    $order = Yii::$app->request->post('Order');
                    $comment = $order['comment'];
                    $comment = $model->manager_comment." | ".$comment." |";
                    $comment = str_replace(array("\r","\n"),"",$comment);
                    //var_dump($comment);
                    if($comment){
                        $site = SettingsModel::find()->select(['value','key'])->where(['id'=>1])->one();
                        $site = $site['value'];
                        $api = SettingsModel::find()->select(['value','key'])->where(['id'=>2])->one();
                        $api = $api['value'];
                        $code = $model->site->code;
                        $url = $site."/api/v5/orders/".$model->number."/edit?apiKey=".$api."&by=id&site=".$code;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,$url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,"order={
                                \"managerComment\":\"".$comment."\"
                                            }");

                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $server_output = curl_exec ($ch);
                        curl_close ($ch);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,$url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,"order={
                                \"managerComment\":\"".$comment."\"
                                            }");

                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $server_output = curl_exec ($ch);
                        curl_close ($ch);

                        //echo "Ответ: " . $server_output;die;
                    }else{
                        Yii::$app->session->setFlash('error', "Заполните поля");
                    }
                }
            }


        }
        return $this->render('view', [
            'model' => $model,
            'yaDeliveryModel' => $yaDeliveryModel,
            'input'=>$input,
            'input_comment'=>$input_comment,
        ]);
    }

    public function actionUpload($id)
    {
        if (isset($_POST)) {
            $files= Order::SaveTempAttachments($_FILES);
            $result = ['files'=>$files];
            foreach ($files as $file){
                $image = Yii::getAlias('@webroot/images/'.$file['fileName']);
                Image::resize($image,958,1080)->save();
                $model = new OrderImage();
                $model->order_id = $id;
                $model->filename = $file['fileName'];
                $model->save();
            }
            Yii::$app->response->format = trim(Response::FORMAT_JSON);
            return $result;
        }

    }

    public function actionDelete(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            OrderImage::deleteAll(['order_id'=>$post['data']['id']]);
        }
    }

    public function actionLoadImage($id){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            $files = OrderImage::find()->where(['order_id'=>$post['Order']['crm']])->all();
            foreach ($files as $file){
                $filename = $file['filename'];
                $site = SettingsModel::find()->select(['value','key'])->where(['id'=>1])->one();
                $site = $site['value'];
                $api = SettingsModel::find()->select(['value','key'])->where(['id'=>2])->one();
                $api = $api['value'];
                $url = $site."/api/v5/files/upload?apiKey=".$api;
                $contents = file_get_contents('images/'.$filename);
                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('post')
                    ->addHeaders(['content-type' => 'application/x-www-form-urlencoded'])
                    ->setUrl($url)
                    ->setContent($contents)
                    ->send();
                if($response->isOk){
                    $json = (json_decode($response->content,true));
                    $idImage = $json['file']['id'];
                    $ch = curl_init();
                    $url = $site."/api/v5/files/$idImage/edit?apiKey=".$api;
                    $filename = "photo-".$post['Order']['crm']."-".$filename;
                    $idOrder = $post['Order']['crm'];
                    $site = "spb-flowerbarkraft.ru";
                    curl_setopt($ch, CURLOPT_URL,$url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                        "file={
                                \"filename\":\"".$filename."\",
                                \"attachment\":[{
                                   \"order\":{\"id\":\"".$idOrder."\",
                                   \"site\":\"".$site."\"}
                                }]
                                            }");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $server_output = curl_exec ($ch);
                    curl_close ($ch);
                }else{

                }
            }
            OrderImage::deleteAll(['order_id'=>$post['Order']['crm']]);
            $model = Order::find()->where(['id'=>$id])->one();
            $id_status = Status::find()->select('id')->where(["name"=>"На согласовании"])->one();
            $model->status_id = $id_status['id'];
            $model->statusCrm = 4;
            $model->save();
            return $this->redirect('/order/view/'.$id);
        }
    }


    /**
     * @param $id
     * @return array|bool[]
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCreateDelivery($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->find($id);

        if (!empty($model->yaDelivery)) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Заявка уже создана',
                ],
            ];
        }

        /* @var $delivery UserDelivery */
        $delivery = Yii::$app->user->identity->delivery ?? null;
        if (!$delivery || !$delivery->active) {
            throw new ForbiddenHttpException();
        }

        $yaDeliveryModel = new OrderDeliveryModel(['order_id' => $model->id]);
        if ($yaDeliveryModel->load(Yii::$app->request->post())) {
            if ($yaDeliveryModel->source_city == $delivery->city
                && $yaDeliveryModel->source_street == $delivery->street
                && $yaDeliveryModel->source_building == $delivery->building
                && !empty($delivery->geo_lat) && !empty($delivery->geo_lon)) {
                $yaDeliveryModel->source_geo_lat = $delivery->geo_lat;
                $yaDeliveryModel->source_geo_lon = $delivery->geo_lon;
            } else {
                if (!$yaDeliveryModel->updateSourceCoordinates()) {
                    return [
                        'success' => false,
                        'message' => [
                            'message' => 'Не удалось определить координаты точки отправки заказа.'
                        ]
                    ];
                }
            }

            if (!$yaDeliveryModel->updateDestinationCoordinates()) {
                return [
                    'success' => false,
                    'message' => [
                        'message' => 'Не удалось определить координаты точки доставки заказа.'
                    ]
                ];
            }

            $yaDeliveryResult = $yaDeliveryModel->sendDeliveryRequest();
            if ($yaDeliveryResult['statusCode'] != 200) {
                return [
                    'success' => false,
                    'message' => $yaDeliveryResult['content'],
                ];
            }

            if ($yaDeliveryModel->save(false)) {
                return [
                    'success' => true,
                    'message' => $yaDeliveryResult['content'],
                ];
            }
        }

        return [
            'success' => false,
            'message' => [
                'message' => 'Что-то пошло не так, попробуйте еще раз.'
            ]
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \yii\httpclient\Exception
     */
    public function actionGetDeliveryStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->find($id);

        if (empty($model->yaDelivery)) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Заявка не найдена',
                ],
            ];
        }

        $info = $model->yaDelivery->getDeliveryStatus();
        if ($info['statusCode'] != 200 || !isset($info['content']['status'])) {
            return [
                'success' => false,
                'message' => [
                    'message' => "Не удалось получить статус заявки на доставку.\n" . $info['content']['message'] ?? null,
                ],
            ];
        }

        return [
            'success' => true,
            'message' => [
                'status' => $info['content']['status'],
                'price' => $info['content']['pricing']['final_price'] ?? $info['content']['pricing']['offer']['price'] ?? null,
            ],
        ];
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\httpclient\Exception
     */
    public function actionCancelDelivery($id)
    {
        $model = $this->find($id);

        if (empty($model->yaDelivery)) {
            Yii::$app->session->setFlash('error', 'Заявка на доставку не найдена');
            return $this->redirect(['view', 'id' => $id]);
        }

        $info = YaDeliveryClient::cancelDelivery($model->yaDelivery->external_id);
        if ($info['statusCode'] == 200) {
            $model->yaDelivery->delete();
            Yii::$app->session->setFlash('success', 'Заявка на доставку успешно отменена');
        } else {
            $errorMsg = 'Не удалось отменить заявку на доставку.';
            if (!empty($info['content']['message'])) {
                $errorMsg .= ' ' . $info['content']['message'];
            }
            Yii::$app->session->setFlash('error', $errorMsg);
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param $id
     * @return array|bool[]
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \yii\httpclient\Exception
     */
    public function actionAcceptDelivery($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->find($id);

        if (empty($model->yaDelivery)) {
            return [
                'success' => false,
                'message' => 'Заявка не найдена',
            ];
        }

        return $model->yaDelivery->acceptDelivery();
    }

    /**
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws MpdfException
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws InvalidConfigException
     */
    public function actionDownload($id)
    {
        $model = $this->find($id);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $this->renderPartial('pdf', ['model' => $model]),
            'cssFile' => '@crm/modules/order/views/default/pdf-asset/style.css',
            'filename' => "Заказ {$model->number}\.pdf",
            'methods' => [
                'SetTitle' => "Заказ {$model->number}",
            ]
        ]);

        return $pdf->render();
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionAccept($id)
    {
        try {
            $model = $this->find($id);
        } catch (ForbiddenHttpException $e) {
            Yii::$app->session->setFlash('error', 'Заказ недоступен. Возможно истекло время на принятие заказа и он перешел другому магазину.');
            return $this->redirect(['list']);
        }

        if ($model->accept()) {
            Yii::$app->session->setFlash('success', 'Заказ принят в работу.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'Что-то пошло не так, попробуйте ещё раз.');
        return $this->redirect(['list']);
    }

    /**
     * @param $id
     * @return Response
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        try {
            $model = $this->find($id);
        } catch (ForbiddenHttpException $e) {
            Yii::$app->session->setFlash('error', 'Заказ недоступен. Возможно истекло время на отказ от заказа и он перешел другому магазину.');
            return $this->redirect(['list']);
        }

        if ($model->reassign()) {
            Yii::$app->session->setFlash('success', 'Заказ перенаправлен другому магазину.');
            return $this->redirect(['list']);
        }

        Yii::$app->session->setFlash('error', 'Что-то пошло не так, попробуйте ещё раз.');
        return $this->redirect(['list']);
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
