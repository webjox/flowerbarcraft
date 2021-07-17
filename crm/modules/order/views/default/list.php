<?php

use common\components\settings\models\StatusModel;
use crm\modules\order\models\Order;
use crm\modules\order\models\OrderSearch;
use crm\modules\site\models\Site;
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
<?php if($site['is_active_personal_area']){ ?>
    <div class="menu-list" style="padding-bottom: 10px">
        <?= Html::a('Все' , ['/order/default/list'], ['class' => 'btn btn-primary','style'=>'width:125px']) ?>
        <?= Html::a('Мои' , ['/order/default/list-personal'], ['class' => 'btn btn-warning','style'=>'width:125px']) ?>
    </div>
    <div class="menu-list" style="padding-bottom: 20px">
        <?= Html::a('Свободные' , ['/order/default/list-paint'], ['class' => 'btn btn-danger','style'=>'width:125px']) ?>
        <?= Html::a('Готов к сборке' , ['/order/default/list-build'], ['class' => 'btn btn-info','style'=>'width:125px']) ?>
    </div>
<?php } ?>
<div class="order-list">
    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'id' => 'order-list',
        'responsiveWrap' => false,
        'rowOptions' => function ($model, $key, $index, $grid) {
            if (!$model->is_accepted) {
                return ['style' => 'color: #a94442; background-color: #f2dede;'];
            }
            return null;
        },
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
                'attribute' => 'crm_id',
                'format' => 'raw',
                'value' => function (Order $model) {
                    return Html::a($model->crm_id, ['view', 'id' => $model->id]);
                },
            ],
            [
                'label' => 'Дата и время',
                'attribute' => 'date',
                'format' => 'raw',
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
                    return implode('<br>', $data);
                },
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'from_date',
                    'attribute2' => 'to_date',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => ['format' => 'dd.mm.yyyy']
                ]),
                'headerOptions' => ['style' => 'width: 135px;'],
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
                        if (isset($item['imageUrl'])) {
                            $name = $item->name;;
                            $img =  Html::a(Html::img($item->imageUrl, ['width' => 45, 'height' => 45]), $item->imageUrl, [
                                'target' => '_blank',
                                'style' => 'margin-right: 10px',
                            ]);
                            $li .= Html::tag('li', $name . '<br>' . $img);
                        } else {
                            $name = $item->name;
                            $img = null;
                            $offer = $item->offer;
                            if ($offer && $offer->images) {
                                $url = $offer->lastImage->image_url;
                                $img = Html::a(Html::img($url, ['width' => 45, 'height' => 45]), $url, [
                                    'target' => '_blank',
                                    'style' => 'margin-right: 10px',
                                ]);
                            }
                            $li .= Html::tag('li', $name . '<br>' . $img);
                        }
                    }
                    return Html::tag('ul', $li, ['class' => 'product-item-list']);
                },
                'format' => 'raw',
            ],
            [
                'label' => 'Адрес доставки',
                'attribute' => 'deliveryAddress',
            ],
            [
                'label' => 'Тип доставки',
                'attribute' => 'delivery_type',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'delivery_type',
                    ['Самовывоз'=>'Самовывоз','Доставка курьером'=>'Доставка курьером'],
                    ['class' => 'form-control', 'prompt' => 'Все']
                ),
            ],
            [
                'label' => 'Получатель',
                'attribute' => 'recipient',
            ],
            [
                'label' => 'Заказчик',
                'attribute' => 'customer',
                'contentOptions' => ['class' => 'order-one'],
                'filterOptions'=>['class'=>'order-one'],
                'headerOptions' => ['class' => 'order-one']
            ],
            [
                'label' => 'Заказчик',
                'attribute' => 'customer',
                'contentOptions' => ['class' => 'order-two'],
                'filterOptions'=>['class'=>'order-two'],
                'headerOptions' => ['class' => 'order-two']
            ],
            [

                'attribute' => 'status_id',
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

                'class' => EditableColumn::class,
                'editableOptions' => function ($model, $key, $index) use ($statusList) {

                    return [
                        'header' => 'статус',
                        'formOptions' => ['action' => ['update-status']],
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'options'=>['class'=>'order-status-'.$model->status->id,'onchange'=>'change'.$model->status->code.'(this)'],
                        'placement' => PopoverX::ALIGN_LEFT_BOTTOM,
                        "data" =>  Order::getListStatus($model->status->id,$model->delivery_type),
                        'displayValue' => $model->status ? Html::tag('span', $model->status->name, [
                            'class' => 'btn btn-status',
                            'style' => "background: {$model->status->bgColor}",
                        ]) : '-',
                        'editableValueOptions'=> Order::checkPermission($model->status->id,$model->user_id)? []:['disabled'=>''],

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
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}<br>{accept}<br>{reject}',
                'buttons' => [
                    'accept' => function ($url, $model) {
                        return Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok']), $url, ['title' => 'Принять']);
                    },
                    'reject' => function ($url, $model) {
                        return Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-ban-circle']), $url, ['title' => 'Отказ']);
                    }
                ],
                'visibleButtons' => [
                    'accept' => function ($model, $key, $index) {
                        return !$model->is_accepted;
                    },
                    'reject' => function ($model, $key, $index) {
                        return !$model->is_accepted;
                    },
                ],
            ],
        ],
    ]) ?>
</div>
<script>
    function changevpokraske (elem){
        if(elem.style.backgroundColor === "rgb(255, 0, 0)"){
            elem.style.backgroundColor = "#008000";
        }else{
            elem.style.backgroundColor = "#FF0000";
        }
    }
</script>
<?php $this->registerJs('
$(".kv-editable-submit").click(function e(){setTimeout(function(){
location.reload();
},2000)
})');
