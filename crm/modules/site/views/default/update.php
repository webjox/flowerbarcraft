<?php

use crm\modules\site\models\Site;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Site */

$this->title = 'Редактирование магазина';

$mainSites = Site::find()
    ->select(['name', 'id'])
    ->where([
        'is_main' => true,
        'active' => true,
    ])
    ->andWhere(['!=', 'id', $model->id])
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->column();
?>
<div class="row">
    <div class="site-create col-md-8">
        <div class="user-form box box-primary">
            <?php $form = ActiveForm::begin(); ?>
            <div class="box-body">
                <?= $form->errorSummary($model); ?>
                <?= $form->field($model, 'name')->textInput(['disabled' => true]) ?>
                <?= $form->field($model, 'code')->textInput(['disabled' => true]) ?>
                <?= $form->field($model, 'city')->textInput() ?>
                <?= $form->field($model, 'is_main')->checkbox(['id' => 'is-main']) ?>
                <?= $form->field($model, 'is_active_personal_area')->checkbox(['id' => 'is_active_personal_area']) ?>
                <?= $form->field($model, 'is_active_map')->checkbox(['id' => 'is_active_map']) ?>
                <?= $form->field($model, 'is_denial')
                    ->checkbox(['id' => 'is-denial'])
                    ->hint('Можно выбрать только один отказной магазин. Если выбрать несколько, то отказным будет последний сохраненный, остальные станут обычными.') ?>
                <?= $form->field($model, 'parent_id', ['options' => ['id' => 'field-parent-id', 'style' => ($model->is_main || $model->is_denial) ? 'display:none' : '']])
                    ->dropDownList($mainSites, ['prompt' => 'Выберите родительский магазин','id' => 'parent-id'])
                    ->hint(empty($mainSites) ? 'Необходимо выбрать хотя бы один главный магазин, чтобы в списке появились варианты' : '') ?>
                <div id="additional-options" style="display: <?= ($model->is_main || $model->is_denial || !$model->parent_id) ? 'none' : 'block' ?>">
                    <?= $form->field($model, 'probability')
                        ->textInput(['type' => 'number'])
                        ->hint('Указывается как число. Чем оно больше, тем больше вероятность передачи заказа данному магазину. Доступные значения от 1 до 99.') ?>
                    <?= $form->field($model, 'timezone')->dropDownList(Site::TimezoneList(), ['prompt' => 'Выберите часовой пояс']) ?>
                </div>
            </div>
            <div class="box-footer">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php $this->registerJs('
(function(){
    var SiteSettings = {
        init: function() {
            this.$fieldParentId = $("#field-parent-id");
            this.$additionalOptions = $("#additional-options");
            this.$isMain = $("#is-main");
            this.$isDenial = $("#is-denial");
            this.$parentId = $("#parent-id");
            this._events();
        },
        _events: function() {
            this.$isMain.on("change", {app: this}, function(e){
                var checked = $(this).prop("checked");
                if (checked) {
                    e.data.app.$isDenial.prop("checked", false);
                    e.data.app._hideParent();
                    e.data.app._hideAdditionalOptions();
                    e.data.app.$parentId.val(null);
                } else if (!e.data.app.$isDenial.prop("checked")) {
                    e.data.app._showParent();
                }
            });
            this.$isDenial.on("change", {app: this}, function(e) {
                var checked = $(this).prop("checked");
                if (checked) {
                    e.data.app.$isMain.prop("checked", false);
                    e.data.app._hideParent();
                    e.data.app._hideAdditionalOptions();
                    e.data.app.$parentId.val(null);
                } else if (!e.data.app.$isMain.prop("checked")) {
                    e.data.app._showParent();
                }
            });
            this.$parentId.on("change", {app: this}, function(e) {
                var value = $(this).val();
                if (value) {
                    e.data.app._showAdditionalOptions();
                } else {
                    e.data.app._hideAdditionalOptions();
                }
            });
        },
        _hideParent: function() {
            this.$fieldParentId.hide();
        },
        _hideAdditionalOptions: function() {
            this.$additionalOptions.hide();
        },
        _showParent: function() {
            this.$fieldParentId.show();
            this.$fieldParentId.trigger("change");
        },
        _showAdditionalOptions: function() {
            this.$additionalOptions.show();
        }
    };
    SiteSettings.init();
})();
');
