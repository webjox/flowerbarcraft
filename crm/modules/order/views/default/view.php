<?php

use common\components\order\models\OrderDeliveryModel;
use common\components\order\models\OrderItemModel;
use common\components\order\models\OrderPaymentModel;
use common\components\yandexGoDelivery\YaDeliveryClient;
use common\models\User;
use crm\modules\order\models\Order;
use kartik\detail\DetailView;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this View */
/* @var $model Order */
/* @var $yaDeliveryModel null|OrderDeliveryModel */

$this->title = "Заказ {$model->crm_id}";
$this->params['breadcrumbs'][] = ['label' => 'Заказы', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
$delivery = Yii::$app->user->identity->delivery ?? null;
?>
<?php if ($model->is_accepted): ?>
    <p><?= Html::a('Открыть в PDF', ['download', 'id' => $model->id], ['class' => 'btn btn-success', 'target' => '_blank']) ?></p>
<?php else: ?>
    <p>
        <?= Html::a('Принять', ['accept', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отказаться', ['reject', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
    </p>
<?php endif; ?>
<div class="order-list">

    <?= DetailView::widget([
        'id' => 'order-info',
        'model' => $model,
        'bordered' => true,
        'striped' => false,
        'condensed' => false,
        'responsive' => true,
        'hover' => false,
        'enableEditMode' => false,
        'panel' => [
            'heading' => $this->title,
            'before' => false,
        ],
        'attributes' => [
            'number',
            'siteName',
            [
                'attribute' => 'status_id',
                'value' => function () use ($model) {
                    if (!$model->is_accepted) {
                        return $model->status->name;
                    }

                    return Editable::widget([
                        'model' => $model,
                        'additionalData' => ['editableIndex' => 0, 'editableKey' => $model->id],
                        'formOptions' => ['action' => ['update-status']],
                        'attribute' => '[0]status_id',
                        'options'=>['class'=>'order-status-'.$model->status->id,'onchange'=>'change'.$model->status->code.'(this)'],
                        'displayValue' => (function () use ($model) {
                            return $model->status ? Html::tag('span', $model->status->name, [
                                'class' => 'btn btn-status',
                                'style' => "background: {$model->status->bgColor}",


                            ]) : '-';
                        })(),
                        'asPopover' => true,
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'data' => Order::getListStatus($model->status_id,$model->delivery_type),
                        'editableValueOptions'=> Order::checkPermission($model->status_id,$model->user_id)? []:['disabled'=>''],
                    ]);

                },
                'format' => 'raw',
            ],
            [
                    'attribute'=> 'crm',
                    'value'=>function() use ($input,$model){
                     return  $this->render('form',['input'=>$input,'model'=>$model]);
                    },
                'format' => 'raw',
                'visible'=>(function() use ($model){
                    if ($model->user_id==Yii::$app->user->identity->id&&($model->status_id==19 || $model->status_id==26 || $model->status_id==30)||$model->status_id==19) return true;
                    else return false;
                })(),
            ],
            [
                'attribute'=> 'comment',
                'value'=>function() use ($input_comment){
                    return  $this->render('form_comment',['input_comment'=>$input_comment]);
                },
                'format' => 'raw',

            ],


            'created_at:datetime',
            'customer',
            'recipient',
            [
                'attribute'=> 'user_id',
                'label'=>'Флорист',
                'value'=>function() use ($model){
                    $user = User::find()->where(['id'=>$model->user_id])->one();
                    return $user['username'];
                },
                'format' => 'raw',
                'visible'=>(function() use ($model){
                    if($model->user_id) return true;
                    else return false;
                })(),

            ],
            [
                'attribute' => 'customer_comment',
                'value' => function () use ($model) {
                    return $model->customer_comment ? Html::tag('div', $model->customer_comment, ['style' => 'white-space: pre']) : '-';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'manager_comment',
                'value' => function () use ($model) {
                    return $model->manager_comment ? Html::tag('div', $model->manager_comment, ['style' => 'white-space: pre']) : '-';
                },
                'format' => 'raw',
            ],
            'totalSumm:currency',
            'prepaySum:currency',
            'toPaySumm:currency',
            'fileList:raw',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->items,
            'pagination' => false,
        ]),
        'responsiveWrap' => false,
        'emptyText' => 'Нет данных',
        'export' => false,
        'layout' => '{items}',
        'summary' => false,
        'toggleData' => false,
        'panel' => [
            'heading' => '<h3 class="panel-title">Позиции</h3>',
            'before' => false,
            'after' => Html::beginTag('div', ['style' => 'text-align: right;']) .
                Html::tag('b', 'Сумма по товарам: ' . Yii::$app->formatter->format((int)($model->initial_product_summ / 100), 'currency')) .
                Html::tag('br') .
                Html::tag('b', 'Сумма со скидкой: ' . Yii::$app->formatter->format((int)($model->summ / 100), 'currency') . ($model->summ > 0 ? (' (' . Yii::$app->formatter->asDecimal((1 - $model->summ / $model->initial_product_summ) * 100, 0) . '%)') : '')) .
                Html::tag('br') .
                Html::tag('b', 'Стоимость доставки: ' . Yii::$app->formatter->format($model->deliveryCost, 'currency')) .
                Html::tag('br') .
                Html::tag('b', 'Итого: ' . Yii::$app->formatter->format($model->totalSumm, 'currency')) .
                Html::endTag('div'),
            'footer' => false,
        ],
        'columns' => [
            [
                'label' => 'Изображение',     // Проверка на наличие ссылки на картинку (новое поле) убрать через неделю
                'value' => function (OrderItemModel $data) {
                    if(isset($data->imageUrl)) {
                        $imgSrc = $data->imageUrl ?? null;
                        if (!$imgSrc) {
                            return null;
                        }
                        return Html::a(Html::img($imgSrc, ['width' => '100', 'alt' => '']), $imgSrc, ['target' => '_blank']);
                    }else {
                        $imgSrc = $data->offer->lastImage->image_url ?? null;
                        if (!$imgSrc) {
                            return null;
                        }
                        return Html::a(Html::img($imgSrc, ['width' => '100', 'alt' => '']), $imgSrc, ['target' => '_blank']);
                    }
                },
                'format' => 'raw',
                'headerOptions' => ['style' => 'width: 117px;'],
            ],
            [
                'label' => 'Название',
                'attribute' => 'name',
                'value' => function (OrderItemModel $data) {
                    $name = $data->name;
                    if ($data->weight) {
                        $name .= Html::tag('div', "Вес: {$data->weight} гр.", ['class' => 'item-property']);
                    }
                    return $name;
                },
                'format' => 'html'
            ],
             [
                'label' => 'Состав',
                'attribute' => 'manufacturer',
                'value' => function (OrderItemModel $data) {
                    return $data->manufacturer;
                },
                'format' => 'html'
            ],
            [
                'label' => 'Цена',
                'value' => function (OrderItemModel $data) {
                    return $data->initial_price > 0 ? (int)($data->initial_price / 100) : 0;
                },
                'format' => 'currency',
            ],
            [
                'label' => 'Кол-во',
                'attribute' => 'quantity',
            ],
            [
                'label' => 'Скидка',
                'attribute' => 'discount_summ',
                'value' => function (OrderItemModel $data) {
                    $formatter = Yii::$app->formatter;
                    if ($data->discount_summ > 0) {
                        $result = $formatter->format((int)($data->discount_summ / 100), 'currency');
                        $result .= ' ';
                        $result .= '(-' . $formatter->asDecimal($data->discount_summ / $data->initial_price * 100, 0) . '%)';
                        return $result;
                    }
                    return $formatter->format(0, 'currency');
                },
            ],
            [
                'label' => 'Стоимость',
                'attribute' => 'summ',
                'value' => function (OrderItemModel $data) {
                    return $data->summ > 0 ? (int)($data->summ / 100) : 0;
                },
                'format' => 'currency',
            ],
        ],
    ]) ?>

    <?= DetailView::widget([
        'model' => $model,
        'bordered' => true,
        'striped' => false,
        'condensed' => false,
        'responsive' => true,
        'hover' => false,
        'enableEditMode' => false,
        'panel' => [
            'before' => false,
            'heading' => $model->delivery_type == 'Самовывоз' ? 'Самовывоз' : 'Доставка',
        ],
        'attributes' => [
            'delivery_type',
            [
                    'attribute'=>'deliveryAddressList',
                    'format'=>'html',
                    'value'=>'<div class="address-detail" >'.$model->deliveryAddressList.'</div>',
            ],
            'delivery_date:date',
            'delivery_time',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->payments,
            'pagination' => false,
        ]),
        'responsiveWrap' => false,
        'emptyText' => 'Нет данных',
        'export' => false,
        'layout' => '{items}',
        'summary' => false,
        'toggleData' => false,
        'panel' => [
            'heading' => '<h3 class="panel-title">Платежи</h3>',
            'before' => false,
            'after' => false,
            'footer' => false
        ],
        'columns' => [
            [
                'label' => 'Статус',
                'attribute' => 'status',
            ],
            [
                'label' => 'Тип',
                'attribute' => 'type',
            ],
            [
                'label' => 'Сумма',
                'value' => function (OrderPaymentModel $data) {
                    return $data->amount > 0 ? (int)($data->amount / 100) : 0;
                },
                'format' => 'currency',
            ],
            [
                'label' => 'Дата',
                'attribute' => 'paid_at',
                'format' => 'datetime',
            ],
            [
                'label' => 'Комментарий',
                'attribute' => 'comment',
            ],
        ],
    ]) ?>

    <?php if ($model->is_accepted && $yaDeliveryModel): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Яндекс.Доставка</h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'ya-delivery-form',
                ]); ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">Отправитель</div>
                            <div class="panel-body">
                                <?= $form->field($yaDeliveryModel, 'source_city')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_street')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_building')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_floor')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_flat')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_sender_name')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_sender_phone')->widget(MaskedInput::class, ['mask' => '+79999999999', 'options' => ['disabled' => !empty($yaDeliveryModel->external_id)]]) ?>
                                <?= $form->field($yaDeliveryModel, 'source_comment')->textarea(['rows' => 5, 'disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">Получатель</div>
                            <div class="panel-body">
                                <?= $form->field($yaDeliveryModel, 'destination_city')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_street')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_building')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_floor')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_flat')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_recipient_name')->textInput(['disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_recipient_phone')->widget(MaskedInput::class, ['mask' => '+79999999999', 'options' => ['disabled' => !empty($yaDeliveryModel->external_id)]]) ?>
                                <?= $form->field($yaDeliveryModel, 'destination_comment')->textarea(['rows' => 5, 'disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= $form->field($yaDeliveryModel, 'comment')->textarea(['rows' => 5, 'disabled' => !empty($yaDeliveryModel->external_id)]) ?>
                <div class="panel panel-default <?= !empty($yaDeliveryModel->external_id) && $model->status->code != 'complete'  ? ($yaDeliveryModel->accepted ? 'accepted-status-updating' : 'status-updating') : '' ?>" id="delivery-status-block" <?= empty($yaDeliveryModel->external_id) ? 'style="display: none"' : '' ?>>
                    <div class="panel-heading">Статус заявки</div>
                    <div class="panel-body">
                        <p>Статус: <span id="delivery-status"><?= !empty($yaDeliveryModel->status) ? YaDeliveryClient::getStatusInfo($yaDeliveryModel->status) : '' ?></span></p>
                        <p id="delivery-cost-block" <?= empty($yaDeliveryModel->price) ? 'style="display:none"' : '' ?>>Стоимость: <span id="delivery-cost"><?= !empty($yaDeliveryModel->price) ? ($yaDeliveryModel->price / 100) : '' ?></span> руб</p>
                    </div>
                </div>
                <?php if (empty($yaDeliveryModel->external_id)): ?>
                    <button class="btn btn-success" id="create-delivery">Отправить заявку на расчет</button>
                <?php endif; ?>
                <button class="btn btn-success" id="accept-delivery" style="display: <?= $yaDeliveryModel->status == 'ready_for_approval' && !$yaDeliveryModel->accepted ? 'inline-block' : 'none' ?>">Подтвердить</button>
                <?= Html::a('Отменить', ['cancel-delivery', 'id' => $model->id], ['class' => 'btn btn-danger', 'id' => 'cancel-delivery', 'style' => ('display:' . (!empty($yaDeliveryModel->external_id && !in_array($yaDeliveryModel->status, ['delivered_finish', 'returned_finish'])) ? 'inline-block' : 'none'))]) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $this->registerJs('


function getStatusInfo($status) {
    if ($status === "new") {
        return "Заявка создана";
    } else if ($status === "estimating") {
        return "Идет процесс оценки заявки";
    } else if ($status === "estimating_failed") {
        return "Не удалось оценить заявку";
    } else if ($status === "ready_for_approval") {
        return "Заявка успешно оценена и ожидает подтверждения";
    } else if ($status === "accepted") {
        return "Заявка подтверждена";
    } else if ($status === "performer_lookup") {
        return "Заявка взята в обработку";
    } else if ($status === "performer_draft") {
        return "Идет поиск водителя";
    } else if ($status === "performer_found") {
        return "Водитель найден и едет за заказом";
    } else if ($status === "performer_not_found") {
        return "Не удалось найти водителя. Можно попробовать снова через некоторое время";
    } else if ($status === "pickup_arrived") {
        return "Водитель приехал за заказом";
    } else if ($status === "ready_for_pickup_confirmation") {
        return "Водитель ждет, когда отправитель назовет ему код подтверждения";
    } else if ($status === "pickuped") {
        return "Водитель успешно забрал заказ";
    } else if ($status === "pay_waiting") {
        return "Заказ ожидает оплаты";
    } else if ($status === "delivery_arrived") {
        return "Водитель приехал к получателю";
    } else if ($status === "ready_for_delivery_confirmation") {
        return "Водитель ждет, когда получатель назовет ему код подтверждения";
    } else if ($status === "delivered") {
        return "Водитель успешно доставил заказ";
    } else if ($status === "delivered_finish") {
        return "Заказ завершен";
    } else if ($status === "returning") {
        return "Водителю пришлось вернуть заказ и он едет в точку возврата";
    } else if ($status === "return_arrived") {
        return "Водитель приехал в точку возврата";
    } else if ($status === "ready_for_return_confirmation") {
        return "Водитель в точке возврата ожидает, когда ему назовут код подтверждения";
    } else if ($status === "returned") {
        return "Водитель успешно вернул заказ";
    } else if ($status === "returned_finish") {
        return "Возврат заказа";
    } else if ($status === "cancelled") {
        return "Заказ был отменен клиентом бесплатно";
    } else if ($status === "cancelled_with_payment") {
        return "Заказ был отменен клиентом платно (водитель уже приехал)";
    } else if ($status === "cancelled_by_taxi") {
        return "Водитель отменил заказ (до получения заказа)";
    } else if ($status === "cancelled_with_items_on_hands") {
        return "Клиент платно отменил заявку без необходимости возврата заказа";
    } else if ($status === "failed") {
        return "При выполнение заказа произошла ошибка, дальнейшее выполнение невозможно";
    }
    return null;
}

$(".kv-editable-submit").click(function e(){setTimeout(function(){
location.reload();
},2000)
})

        $(".fileinput-remove").on("click", function(e){
            var status = '.$model->status_id.';
            if(status===19) {
                var id = '.$model->crm_id.';
                var obj = {"id": id};
                $.ajax({
                    url: "/order/delete",
                    method: "post",
                    dataType: "json",
                    data: {data: obj},
                    success: function (data) {
                        console.log(data);
                    }
                })
            }
            location.reload();
          
        })


function updateDeliveryStatus() {
    $.ajax({
        url: "/order/get-delivery-status/' . $model->id . '",
        type: "POST",
        success: function(res) {
            console.log(res);
            if (res.success == true) {
                $("#delivery-status").html(getStatusInfo(res.message.status));
                if (res.message.status == "ready_for_approval") {
                    $("#accept-delivery").show();
                }
                if (res.message.price != null) {
                    $("#delivery-cost").html(Math.floor(res.message.price * 100) / 100 );
                    $("#delivery-cost-block").show();
                }

                if (res.message.status == "delivered_finish" || res.message.status == "returned_finish" || res.message.status == "cancelled" || res.message.status == "cancelled_with_payment" || res.message.status == "cancelled_by_taxi" || res.message.status == "cancelled_with_items_on_hands" || res.message.status == "failed") {
                    clearTimeout(timerId);
                }
            } else {
                alert(res.message.message);
                alert("Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.");
            }
        },
        error: function() {
            alert("Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.");
        }
    });
}

var timerId = null;
if ($("#delivery-status-block").hasClass("status-updating")) {
    if (timerId != null) {
        clearTimeout(timerId);
    }
    timerId = setInterval(updateDeliveryStatus, 5000);
}
if ($("#delivery-status-block").hasClass("accepted-status-updating")) {
    if (timerId != null) {
        clearTimeout(timerId);
    }
    timerId = setInterval(updateDeliveryStatus, 60000);
}

$("#accept-delivery").on("click", function (e) {
    e.preventDefault();
    $.ajax({
        url: "/order/accept-delivery/' . $model->id . '",
        type: "POST",
        success: function(res) {
            if (res.success == true) {
                $("#accept-delivery").hide();
                $("#delivery-status").html(getStatusInfo(res.status));
                if (timerId != null) {
                    clearTimeout(timerId);
                }
                timerId = setInterval(updateDeliveryStatus, 60000);
            } else {
                alert(res.message);
                alert("Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.");
            }
        },
        error: function() {
            alert("Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.");
        }
    });
});

$("#ya-delivery-form").on("beforeSubmit", function() {
    var data = $(this).serialize();
    $.ajax({
        url: "/order/create-delivery/' . $model->id . '",
        type: "POST",
        data: data,
        success: function(res) {
            if (res.success == true) {
                $("#create-delivery").hide();
                $("#ya-delivery-form input").attr("disabled", true);
                $("#ya-delivery-form textarea").attr("disabled", true);
                $("#delivery-status").html(getStatusInfo(res.message.status));
                $("#delivery-status-block").show();
                $("#cancel-delivery").show();
                if (timerId != null) {
                    clearTimeout(timerId);
                }
                timerId = setInterval(updateDeliveryStatus, 5000);
            } else {
                alert(res.message.message);
                alert("Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.");
            }
        },
        error: function() {
            alert("Что-то пошло не так. Попробуйте обновить страницу и отправить новый запрос.");
        }
    });
    return false;
});
');
?>
<script>
    function changevpokraske (elem){
        if(elem.style.backgroundColor === "rgb(255, 0, 0)"){
            elem.style.backgroundColor = "#008000";
        }else{
            elem.style.backgroundColor = "#FF0000";
        }
    }
</script>






