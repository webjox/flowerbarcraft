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
<p><?= Html::a('Открыть в PDF', ['download', 'id' => $model->id], ['class' => 'btn btn-success', 'target' => '_blank']) ?></p>
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
            'deliveryAddressList:html',
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
