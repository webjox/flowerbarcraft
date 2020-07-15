<?php

use common\components\order\models\OrderItemModel;
use common\components\order\models\OrderPaymentModel;
use crm\modules\order\models\Order;
use kartik\detail\DetailView;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\web\View;

/* @var $this View */
/* @var $model Order */

$this->title = "Заказ {$model->crm_id}";
$this->params['breadcrumbs'][] = ['label' => 'Заказы', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-list">
    <?= DetailView::widget([
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
            'crm_id',
            'number',
            'external_id',
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
                            return $model->status->name ?? '-';
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
            'customerPhones',
            'recipient_name',
            'recipient_phone',
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
        'emptyText' => 'Нет данных',
        'export' => false,
        'layout' => '{items}',
        'summary' => false,
        'toggleData' => false,
        'panel' => [
            'heading' => '<h3 class="panel-title">Позиции</h3>',
            'before' => false,
            'after' => false,
            'footer' => false
        ],
        'columns' => [
            [
                'label' => 'ID',
                'attribute' => 'crm_id',
            ],
            [
                'label' => 'ID товара',
                'attribute' => 'crm_offer_id',
            ],
            [
                'label' => 'Изображение',
                'value' => function (OrderItemModel $data) {
                    return $data->offer->images[0]->image_url ?? null;
                },
                'format' => ['image', ['width' => '100']],
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
            'delivery_address',
            'delivery_date:date',
            'delivery_time',
            'deliveryCost:currency',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->payments,
            'pagination' => false,
        ]),
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
                'label' => 'ID',
                'attribute' => 'crm_id',
            ],
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
