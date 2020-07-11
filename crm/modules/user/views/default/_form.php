<?php

use common\components\user\models\FloristManage;
use common\components\user\models\UserManage;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model UserManage */
?>
<div class="user-form box box-primary">
    <?php $form = ActiveForm::begin(); ?>
    <div class="box-body">
        <?= $form->errorSummary($model); ?>

        <?= $form->field($model, 'username')->textInput([
            'maxlength' => true,
            'disabled' => $model->scenario != UserManage::SCENARIO_CREATE
        ]) ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?php if ($model instanceof FloristManage): ?>
            <?= $form->field($model, 'site_id')->widget(Select2::class, [
                'data' => FloristManage::getSitesList(),
                'options' => ['placeholder' => 'Выбрать магазин...'],
            ]) ?>
        <?php endif; ?>

        <?php if ($model->scenario == UserManage::SCENARIO_UPDATE): ?>
            <?= $form->field($model, 'status')->dropDownList(UserManage::statusList()) ?>
        <?php endif; ?>

        <?= $form
            ->field($model, 'password')
            ->passwordInput(['maxlength' => true])
            ->hint($model->scenario == UserManage::SCENARIO_UPDATE ? 'Оставьте пустым, если не хотите менять' : false) ?>
    </div>
    <div class="box-footer">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
