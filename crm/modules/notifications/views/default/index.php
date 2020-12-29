<?php

use yii\bootstrap\Modal;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $this View */
/* @var $tgChatsDataProvider ActiveDataProvider */

$this->title = 'Настройка оповещений';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notifications-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Telegram аккаунты</div>
                <div class="panel-body">
                    <?php try {
                        echo GridView::widget([
                            'dataProvider' => $tgChatsDataProvider,
                            'columns' => [
                                [
                                    'attribute' => 'tg_username',
                                    'headerOptions' => ['style' => 'width: 130px;'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'urlCreator' => function ($action, $model, $key, $index) {
                                        if ($action == 'delete') {
                                            return Url::toRoute(['tg-delete', 'id' => $key]);
                                        }
                                        return Url::toRoute([$action, 'id' => $key]);
                                    },
                                    'template' => '{delete}',
                                    'headerOptions' => ['style' => 'width: 30px;'],
                                ],
                            ],
                        ]);
                    } catch (Exception $e) {
                    } ?>
                    <div class="form-group">
                        <?= Html::a('Добавить telegram', ['generate-tg-code'], ['class' => 'btn btn-warning get-tg-code-js']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php Modal::begin([
    'header' => '<h4>Активация telegram-бота</h4>',
    'id' => 'tg-modal',
]); ?>
    <div id="modalContent"></div>
<?php Modal::end(); ?>
<?php $this->registerJs('
$(".get-tg-code-js").on("click", function(e){
    e.preventDefault();
    var url = $(this).attr("href");

    $.ajax({
        "url": url,
        "method": "GET",
        "dataType": "json",
        "success": function (res) {
            var modalWindow = $("#tg-modal");
            var modalContent = modalWindow.find("#modalContent");
            modalWindow.modal("show");
            if (res.code !== undefined && res.code != null) {
                modalContent.html("<p>Для активации уведомлений в telegram необходимо выполнить один из этих пунктов:</p>" +
                    "<ul>" +
                    "<li><p>Перейдите по ссылке <b><a target=\"_blank\" href=\"tg://resolve?domain=YouKraft_bot&start=" + res.code + "\">tg://resolve?domain=YouKraft_bot&start=" + res.code + "</a></b>, нажмите \"<b>запустить</b>\" и активация должна пройти автоматически</p></li>" +
                    "<li><p>либо найдите в telegram бота <b>@YouKraft_bot</b>, напишите \"<b>\\\start</b>\" и введите код: <b>" + res.code + "</b></p></li>" +
                    "</ul>" +
                    "<p>Обработка запроса может занять около минуты. Ссылка и код действительны в течение 1 часа и могут быть использованы только 1 раз.</p>" +
                    "<p>После активации бота на вашей странице в личном кабинете должен появиться список привязанных telegram-аккаунтов.</p>" +
                    "<p>Вы можете получить несколько кодов, чтобы привязать несколько telegram-аккаунтов для уведомлений.</p>");
            } else {
                modalContent.html("Ошибка генерации кода для привязки telegram аккаунта");
            }
        }
    });
});

$("#tg-modal").on("hidden.bs.modal", function (e) {
    $(this).find("#modalContent").html("");
});
');
