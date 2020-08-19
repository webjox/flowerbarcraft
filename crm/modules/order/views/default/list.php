<?php

use common\components\settings\models\StatusModel;
use crm\modules\order\models\Order;
use crm\modules\order\models\OrderSearch;
use kartik\date\DatePicker;
use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use kartik\popover\PopoverX;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel OrderSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Заказы';
$this->params['breadcrumbs'][] = $this->title;

$statusList = Order::getAvailableStatuses();
/* @var $query \yii\db\ActiveQuery */
$query = clone $dataProvider->query;
$dataSumm = Yii::$app
    ->db
    ->createCommand($query->select([
        'sum(total_summ) as total',
        'sum(initial_product_summ) as init',
        'sum(summ) as discount',
        'sum(delivery_cost) as delivery'
    ])->createCommand()->rawSql)
    ->queryOne();
?>
<div class="order-list">
    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'id' => 'order-list',
        'responsiveWrap' => false,
        'panel' => [
            'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
            'before' => false,
            'after' => false,
            'footer' => Html::tag('div', 'Суммы по заказам в фильтре:', ['style' => 'width: 100%; text-align: right']) .
                Html::tag('div', 'Товаров в заказах на сумму: ' . Yii::$app->formatter->asCurrency(($dataSumm['init'] ?? 0) / 100), ['style' => 'width: 100%; text-align: right']) .
                Html::tag('div', 'Товаров в заказах с учетом скидки на сумму: ' . Yii::$app->formatter->asCurrency(($dataSumm['discount'] ?? 0) / 100), ['style' => 'width: 100%; text-align: right']) .
                Html::tag('div', 'Сумма по доставке: ' . Yii::$app->formatter->asCurrency(($dataSumm['delivery'] ?? 0) / 100), ['style' => 'width: 100%; text-align: right']) .
                Html::tag('div', 'Итого: ' . Yii::$app->formatter->asCurrency(($dataSumm['total'] ?? 0) / 100), ['style' => 'width: 100%; text-align: right']),
        ],
        'columns' => [
            [
                'label' => 'Дата и время доставки',
                'attribute' => 'date',
                'value' => function (Order $model) {
                    $data = [];
                    if ($model->delivery_date) {
                        $data[] = Yii::$app->formatter->asDate($model->delivery_date, 'php:d.m.Y');
                    }
                    if ($model->delivery_time) {
                        $data[] = $model->delivery_time;
                    }
                    if (empty($data)) {
                        return '-';
                    }
                    return implode(' ', $data);
                },
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'from_date',
                    'attribute2' => 'to_date',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => ['format' => 'dd.mm.yyyy']
                ]),
                'headerOptions' => ['style' => 'width: 240px;'],
            ],
            [
                'label' => 'Товары',
                'value' => function (Order $model) {
                    $items = $model->items;
                    if (empty($items)) {
                        return '-';
                    }
                    $li = '';
                    foreach ($items as $item) {
                        $name = $item->name;
                        $img = null;
                        $offer = $item->offer;
                        if ($offer && $offer->images) {
                            $url = $offer->images[0]->image_url;
                            $img = Html::a(Html::img($url, ['width' => 45, 'height' => 45]), $url, [
                                'target' => '_blank',
                                'style' => 'margin-right: 10px',
                            ]);
                        }
                        $li .= Html::tag('li', $img . $name);
                    }
                    return Html::tag('ul', $li, ['style' => 'list-style: none; padding-left:0']);
                },
                'format' => 'raw',
            ],
            [
                'label' => 'Адрес доставки',
                'attribute' => 'deliveryAddress',
            ],
            [
                'label' => 'Получатель',
                'attribute' => 'recipient',
            ],
            [
                'label' => 'Заказчик',
                'attribute' => 'customer',
            ],
            [
                'class' => EditableColumn::class,
                'attribute' => 'status_id',
                'readonly' => false,
                'value' => function ($model) {
                    return $model->status->name ?? '-';
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    StatusModel::find()->select(['name', 'id'])->where(['active' => true, 'show_in_list' => true])->indexBy('id')->column(),
                    ['class' => 'form-control', 'prompt' => 'Все']
                ),
                'headerOptions' => ['style' => 'width: 150px;'],
                'editableOptions' => function ($model, $key, $index) use ($statusList) {
                    return [
                        'header' => 'статус',
                        'formOptions' => ['action' => ['update-status']],
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'placement' => PopoverX::ALIGN_LEFT_BOTTOM,
                        "data" => $statusList,
                        'displayValue' => $model->status ? Html::tag('span', $model->status->name, [
                            'class' => 'btn btn-status',
                            'style' => "background: {$model->status->bgColor}"
                        ]) : '-',
                    ];
                },
            ],
            [
                'label' => 'Сумма',
                'attribute' => 'total_summ',
                'value' => function (Order $model) {
                    $sum = $model->total_summ ?: 0;
                    return Yii::$app->formatter->asCurrency($sum / 100);
                },
            ],
            'crm_id',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}'
            ],
        ],
    ]) ?>
</div>
