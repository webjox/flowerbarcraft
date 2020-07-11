<?php

use common\components\user\models\UserManage;
use yii\web\View;

/* @var $this View */
/* @var $model UserManage */

$this->title = 'Добавление пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="user-create col-md-8">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>
