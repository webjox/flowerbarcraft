<?php

use common\components\site\models\SiteModel;
use crm\modules\site\models\SiteSearchModel;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel SiteSearchModel */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Магазины';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-list">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'id' => 'site-list',
        'responsiveWrap' => false,
        'columns' => [
            'id',
            'name',
            'code',
            [
                'attribute' => 'active',
                'value' => function (SiteModel $model) {
                    return $model->active ? 'Да' : 'Нет';
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'active',
                    [0 => 'Нет', 1 => 'Да'],
                    ['class' => 'form-control', 'prompt' => 'Все']
                ),
            ],
            'created_at:datetime',
            'updated_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'visibleButtons' => [
                    'update' => function ($model, $key, $index) {
                        return $model->active == true;
                    }
                ],
            ],
        ],
    ]) ?>
</div>
