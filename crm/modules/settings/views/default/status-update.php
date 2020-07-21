<?php

use crm\modules\settings\models\Status;
use kartik\color\ColorInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Status */

$this->title = 'Редактирование статуса: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-8">
        <div class="settings-form box box-primary">
            <?php $form = ActiveForm::begin(); ?>
            <div class="box-body">
                <?= $form->errorSummary($model); ?>

                <?= $form
                    ->field($model, 'active')
                    ->checkbox(['disabled' => true])
                    ->hint('Активность меняется в retailCRM') ?>

                <?= $form
                    ->field($model, 'available')
                    ->checkbox()
                    ->hint('Доступно ли изменения статуса заказа на этот статус?') ?>

                <?= $form
                    ->field($model, 'show_in_list')
                    ->checkbox()
                    ->hint('Выводить заказы с этим статусом?') ?>

                <?= ColorInput::widget([
                    'model' => $model,
                    'attribute' => 'color',
                    'value' => $model->bgColor,
                    'size' => 'sm',
                    'options' => ['readonly' => true, 'placeholder' => $model->bgColor],
                ]); ?>

                <br>

            </div>
            <div class="box-footer">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
