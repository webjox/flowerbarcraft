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
?>
<div class="order-list">
    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'panel' => [
            'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
            'before' => false,
            'after' => false,
        ],
        'columns' => [
            'crm_id',
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
                            $img = Html::a(Html::img($url, ['width' => 30, 'height' => 30]), $url, [
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
                'label' => 'Сумма',
                'attribute' => 'total_summ',
                'value' => function (Order $model) {
                    $sum = $model->total_summ ?: 0;
                    return Yii::$app->formatter->asCurrency($sum / 100);
                },
            ],
            [
                'label' => 'Получатель',
                'value' => function (Order $model) {
                    $data = [];
                    if ($model->recipient_name) {
                        $data[] = $model->recipient_name;
                    }
                    if ($model->recipient_phone) {
                        $data[] = $model->recipient_phone;
                    }
                    if (empty($data)) {
                        return '-';
                    }
                    return implode(', ', $data);
                },
            ],
            [
                'label' => 'Заказчик',
                'value' => function (Order $model) {
                    $data = [];
                    if ($model->customer_last_name) {
                        $data[] = $model->customer_last_name;
                    }
                    if ($model->customer_first_name) {
                        $data[] = $model->customer_first_name;
                    }
                    if ($model->customer_patronymic) {
                        $data[] = $model->customer_patronymic;
                    }
                    if ($model->customer_phone && $model->customer_additional_phone) {
                        $data[] = "{$model->customer_phone} ({$model->customer_additional_phone})";
                    } elseif ($model->customer_phone) {
                        $data[] = $model->customer_phone;
                    } elseif ($model->customer_additional_phone) {
                        $data[] = $model->customer_additional_phone;
                    }
                    if (empty($data)) {
                        return '-';
                    }
                    return implode(' ', $data);
                }
            ],
            [
                'label' => 'Адрес доставки',
                'value' => function (Order $model) {
                    return $model->delivery_address ?: '-';
                }
            ],
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
                'class' => EditableColumn::class,
                'attribute' => 'status_id',
                'readonly' => false,
                'value' => function ($model) {
                    return $model->status->name ?? '-';
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    StatusModel::find()->select(['name', 'id'])->where(['active' => true])->indexBy('id')->column(),
                    ['class' => 'form-control', 'prompt' => 'Все']
                ),
                'headerOptions' => ['style' => 'width: 150px;'],
                'editableOptions' => [
                    'header' => 'статус',
                    'formOptions' => ['action' => ['update-status']],
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'placement' => PopoverX::ALIGN_LEFT_BOTTOM,
                    "data" => $statusList,
                ],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}'
            ],
        ],
    ]) ?>
</div>
