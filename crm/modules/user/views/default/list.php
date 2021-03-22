<?php

use common\components\user\models\UserManage;
use common\models\User;
use crm\modules\user\models\UserSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Добавить администратора', ['create-admin'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Добавить флориста', ['create-florist'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'name',
            [
                'attribute' => 'group',
                'filter' => Html::activeDropDownList($searchModel, 'group', UserManage::groupList(), [
                    'prompt' => 'Все',
                    'class' => 'form-control'
                ]),
                'value' => function (User $data) {
                    return UserManage::getGroupName($data->group);
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'filter' => Html::activeDropDownList($searchModel, 'status', UserManage::statusList(), [
                    'prompt' => 'Все',
                    'class' => 'form-control'
                ]),
                'value' => function (User $data) {
                    $labelType = 'default';
                    if ($data->status == UserManage::STATUS_ACTIVE) {
                        $labelType = 'success';
                    } elseif ($data->status == UserManage::STATUS_DELETED) {
                        $labelType = 'danger';
                    }
                    return Html::tag('span', UserManage::getStatusName($data->status), ['class' => "label label-{$labelType}"]);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action == 'update') {
                        if ($model->group == User::GROUP_ADMIN) {
                            return Url::to(['update-admin', 'id' => $key]);
                        } elseif ($model->group == User::GROUP_FLORIST) {
                            return Url::to(['update-florist', 'id' => $key]);
                        }
                    }
                    return Url::to([$action, 'id' => $key]);
                },
            ],
        ],
    ]) ?>
</div>
