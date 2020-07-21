<?php

use common\components\order\models\OrderItemModel;
use common\components\order\models\OrderPaymentModel;
use crm\modules\order\models\Order;
use kartik\detail\DetailView;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Order */

$this->title = "Заказ {$model->crm_id}";
$this->params['breadcrumbs'][] = ['label' => 'Заказы', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
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
                    return Editable::widget([
                        'model' => $model,
                        'additionalData' => ['editableIndex' => 0, 'editableKey' => $model->id],
                        'formOptions' => ['action' => ['update-status']],
                        'attribute' => '[0]status_id',
                        'displayValue' => (function () use ($model) {
                            return $model->status ? Html::tag('span', $model->status->name, [
                                'class' => 'btn btn-status',
                                'style' => "background: {$model->status->bgColor}"
                            ]) : '-';
                        })(),
                        'asPopover' => true,
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'data' => Order::getAvailableStatuses(),
                    ]);
                },
                'format' => 'raw',
            ],
            'created_at:datetime',
            'customer',
            'recipient',
            'customer_comment',
            'manager_comment',
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
                Html::tag('b', 'Стоимость доставки: ' . Yii::$app->formatter->format($model->deliveryCost, 'currency')) .
                Html::tag('br') .
                Html::tag('b', 'Сумма: ' . Yii::$app->formatter->format($model->totalSumm, 'currency')) .
                Html::endTag('div'),
            'footer' => false,
        ],
        'columns' => [
            [
                'label' => 'Изображение',
                'value' => function (OrderItemModel $data) {
                    $imgSrc = $data->offer->images[0]->image_url ?? null;
                    if (!$imgSrc) {
                        return null;
                    }
                    return Html::a(Html::img($imgSrc, ['width' => '100', 'alt' => '']), $imgSrc, ['target' => '_blank']);
                },
                'format' => 'raw',
                'headerOptions' => ['style' => 'width: 117px;'],
            ],
            [
                'label' => 'Название',
                'attribute' => 'name',
            ],
            [
                'label' => 'Стоимость',
                'value' => function (OrderItemModel $data) {
                    return $data->price > 0 ? (int)($data->price / 100) : 0;
                },
                'format' => 'currency',
            ],
            [
                'label' => 'Количество',
                'attribute' => 'quantity',
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
            'heading' => 'Доставка',
        ],
        'attributes' => [
            'deliveryAddress',
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
</div>
