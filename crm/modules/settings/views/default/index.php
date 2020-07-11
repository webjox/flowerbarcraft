<?php

use common\components\settings\models\SettingsModel;
use common\components\settings\models\StatusModel;
use crm\modules\settings\forms\SettingsForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model SettingsForm */

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

                <?= $form
                    ->field($model, SettingsModel::PARAM_CRM_STATUS_LIST)
                    ->listBox(StatusModel::find()
                        ->select(['name', 'id'])
                        ->where(['active' => true])
                        ->orderBy(['ordering' => SORT_ASC, 'name' => SORT_ASC])
                        ->indexBy('id')
                        ->column(), [
                        'multiple' => true,
                        'size' => 12
                    ])
                    ->hint('Удерживайте Ctrl, чтобы выделить несколько вариантов') ?>
            </div>
            <div class="box-footer">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
