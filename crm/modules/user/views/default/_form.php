<?php

use common\components\user\models\FloristManage;
use common\components\user\models\UserDelivery;
use common\components\user\models\UserManage;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this View */
/* @var $model UserManage */
/* @var $delivery UserDelivery */
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

        <?php if ($model instanceof FloristManage && $model->scenario == UserManage::SCENARIO_UPDATE): ?>
            <div class="panel panel-default">
                <div class="panel-heading">Яндекс.Доставка</div>
                <div class="panel-body">
                    <?= $form->field($delivery, 'active')->checkbox() ?>
                    <?= $form->field($delivery, 'city')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($delivery, 'street')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($delivery, 'building')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($delivery, 'floor')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($delivery, 'flat')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($delivery, 'sender_name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($delivery, 'sender_phone')->widget(MaskedInput::class, ['mask' => '+79999999999']) ?>
                    <?= $form->field($delivery, 'comment')->textarea(['rows' => 5]) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="box-footer">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
