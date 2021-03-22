<?php

use common\components\user\models\UserDelivery;
use common\components\user\models\UserManage;
use yii\web\View;

/* @var $this View */
/* @var $model UserManage */
/* @var $delivery UserDelivery */

$this->title = 'Обновление пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title . ': ' . $model->name;
?>
<div class="row">
    <div class="user-create col-md-8 col-md-offset-2">
        <?= $this->render('_form', [
            'model' => $model,
            'delivery' => isset($delivery) ? $delivery : null,
        ]) ?>
    </div>
</div>
