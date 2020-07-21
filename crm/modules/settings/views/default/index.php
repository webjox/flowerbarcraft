<?php

use common\components\settings\models\SettingsModel;
use crm\modules\settings\forms\SettingsForm;
use crm\modules\settings\models\Status;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model SettingsForm */
/* @var $statusesDataProvider ActiveDataProvider */

$this->title = 'Настройки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-8">
        <div class="settings-form box box-primary">
            <?php $form = ActiveForm::begin(); ?>
            <div class="box-body">
                <?= $form->errorSummary($model); ?>

                <?= $form->field($model, SettingsModel::PARAM_CRM_URL)->textInput() ?>
                <?= $form->field($model, SettingsModel::PARAM_CRM_API_KEY)->textInput() ?>

                <?= GridView::widget([
                    'dataProvider' => $statusesDataProvider,
                    'responsiveWrap' => false,
                    'emptyText' => 'Нет данных',
                    'export' => false,
                    'layout' => '{items}',
                    'summary' => false,
                    'toggleData' => false,
                    'panel' => [
                        'heading' => '<h3 class="panel-title">Статусы</h3>',
                        'before' => false,
                        'after' => false,
                        'footer' => false
                    ],
                    'columns' => [
                        'name',
                        'active:boolean',
                        'available:boolean',
                        'show_in_list:boolean',
                        [
                            'attribute' => 'bgColor',
                            'value' => function (Status $model) {
                                return Html::tag('div', '', [
                                    'style' => "width: 20px; height: 20px; background: {$model->bgColor}"
                                ]);
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}',
                            'urlCreator' => function ($action, $model, $key, $index, $column) {
                                return Url::to(['status-update', 'id' => $model->id]);
                            }
                        ],
                    ],
                ]) ?>
            </div>
            <div class="box-footer">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
