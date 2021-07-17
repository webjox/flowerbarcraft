<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;

?>
<?php

echo FileInput::widget([
    'name' => 'files[]',
    'options' => ['multiple' => true, 'id' => 'unique-id-1'],
    'pluginOptions' => ['allowedFileExtensions' => ['jpg','jpeg', 'gif', 'png'],
        'previewFileType' => 'any', 'showUpload' => false, 'showRemove' => false, 'initialPreviewAsData' => true, 'overwriteInitial' => true,
        "uploadUrl" => Url::to(['/order/upload/'.$model['crm_id']]),
        'msgUploadBegin' => Yii::t('app', 'Загружается'),
        'msgUploadThreshold' => Yii::t('app', 'Загружается'),
        'msgUploadEnd' => Yii::t('app', 'Файлы загружены'),
        'dropZoneClickTitle' => '',
        "uploadAsync" => false,
        "browseOnZoneClick" => true,
        'fileActionSettings' => [
            'showZoom' => true,
            'showRemove' => false,
            'showUpload' => false,
        ],
        'maxFileCount' => 20, 'maxFileSize' => 50000,
    ],
    'pluginEvents' => [
        'filebatchselected' => 'function() {
        $(this).fileinput("upload");
        }',
        'fileloaded' => 'function(){
        $("#send-picture").css("display","block");
        }',
    ],
]);
?>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'],'id'=>'image','action'=>Url::to(['/order/load-image/'.$model->id])]) ?>

    <?= $form->field($input, 'crm')->hiddenInput(['value'=>$model->crm_id])->label(false) ?>

    <button id="send-picture" class="btn btn-primary" style="float: right;width: 125px; display: none" >Отправить</button>

    <?php ActiveForm::end() ?>



