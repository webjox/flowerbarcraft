<?php
use yii\widgets\ActiveForm;
?>

<div>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'],'id'=>'image']) ?>

    <?= $form->field($input_comment, 'comment')->textarea()->label(false) ?>

    <?= "<button style='' id='image-download' name = 'submit_comment'>Отправить</button>" ?>

    <?php ActiveForm::end() ?>
</div>
