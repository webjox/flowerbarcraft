<?php

use common\components\settings\models\StatusModel;
use crm\modules\order\models\Order;
use crm\modules\order\models\OrderSearch;
use crm\modules\settings\models\Status;
use crm\modules\site\models\Site;
use kartik\date\DatePicker;
use kartik\datetime\DateTimePicker;
use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use kartik\popover\PopoverX;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;


$this->title = 'Карта';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<link rel="stylesheet" href="css/bootstrap.min.css">-->
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=10d0aae4-eb7c-4e41-91f3-1d6217a9b798"
        type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.maskedinput@1.4.1/src/jquery.maskedinput.min.js"
        type="text/javascript"></script>

<?php if ($site->is_active_map) { ?>

    <?php $form = ActiveForm::begin(); ?>
    <div class="form-status" style="display:-webkit-inline-box;width: 750px">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(Status::find()->where(['active' => 1, 'available' => 1, 'show_in_list' => 1])->all(), 'id', 'name'),
                'options' => ['placeholder' => 'Все'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'multiple' => true,
                    'width' => '250px',
                ],
            ]
        )->label("Стаусы", ['style' => 'padding-left:40%']);
        ?>
        <div style="width: 150px;margin-left: 30px">
            <?= $form->field($model, 'dateFrom')->widget(
                DatePicker::class,
                [
                    'options' => ['autocomplete' => 'off'],
                    'convertFormat' => true,
                    'attribute2' => 'dateTo',
                    'type' => DatePicker::TYPE_RANGE,
                    'separator' => '-',
                    'pluginOptions' => [
                        'format' => 'y-MM-dd',
                        'todayHighlight' => true
                    ]
                ]
            )->label("Дата", ['style' => 'padding-left:42%']);
            ?>
        </div>
        <?= $form->field($model, 'timeFrom')->textInput(['style' => 'width:70px;margin-left:30px', 'id' => 'input-time', 'autocomplete' => 'off'])->label("Время", ['style' => 'display: flex;
    position: relative;
    left: 85px;']);
        ?>
        <div class="separator"><p>-</p></div>
        <?= $form->field($model, 'timeTo')->textInput(['style' => 'width:70px;margin-left:10px;margin-top:24px', 'id' => 'input-time2', 'autocomplete' => 'off'])->label(false);
        ?>
        <?= Html::submitButton('Найти', ['class' => 'btn btn-primary', 'style' => 'margin-left:20px;margin-top:24px']) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="order" style="float: left">
        <?php if ($orders_error) { ?>
            <div class="order-text" style="width: 11em;margin-left: 27px">Заказы с
                ошибкой: <?= count($orders_error) ?></div>
            <div class="order-items-map">
                <? foreach ($orders_error as $item) { ?>
                    <div class="order-item-map"><a target="_blank"
                                                   href="/order/view/<?= $item->id ?>"><?= $item->crm_id ?>
                            (<?= $item->delivery_time ?>)</a>
                    </div>
                <? } ?>
            </div>
        <? } ?>

        <div class="order-text" style="margin-left: 40px;width: 9em">Всего заказов: <?= count($orders) ?></div>
        <?php if ($orders) { ?>
            <div class="order-items-map">
                <? foreach ($orders as $item) { ?>
                    <div class="order-item-map"><a target="_blank"
                                                   href="/order/view/<?= $item->id ?>"><?= $item->crm_id ?>
                            (<?= $item->delivery_time ?>)</a>
                    </div>
                <? } ?>
            </div>
        <? } ?>

    </div>

    <div class="order-map" style="width:<? if (!$orders && !$orders_error) echo '100%'; else echo '75%' ?>">
        <div id="map" style="width: 100%;height: 450px"></div>
    </div>
<? } else {
    Yii::$app->getResponse()->redirect("/orders");
} ?>


<script>
    var myMap;
    var center = [<?=$site['city_lat']?>, <?=$site['city_lon']?>];
    ymaps.ready(init);
    var timeFrom = "<?=$model['timeFrom']?>";
    var timeTo = "<?=$model['timeTo']?>";
    var date = "";
    if (timeFrom||timeTo) {
        if(timeFrom){
            timeFrom = timeFrom.replace(/[^+\d]/g, '');
            timeFrom = timeFrom.replace(/^0+/, '');

        }
        if(timeTo){
            timeTo = timeTo.replace(/[^+\d]/g, '');
            timeTo = timeTo.replace(/^0+/, '');

        }
    }else{
        date = new Date();
        hours = date.getHours();
        minutes = String(date.getMinutes()).padStart(2, "0");
        date = hours+ "" +minutes;
    }
    if(timeFrom>1&&timeTo<1){
        date = timeFrom;
        timeFrom = 0;
    }
    if(timeFrom<1&&timeTo>1){
        date = new Date();
        hours = date.getHours();
        minutes = String(date.getMinutes()).padStart(2, "0");
        date = hours+ "" +minutes;
        timeTo = 0;
    }
    console.log("date - " + date)
    console.log("timeFrom" + timeFrom);
    console.log("timeTo" + timeTo);
    function init() {
        myMap = new ymaps.Map('map', {
            center: center,
            zoom: 10,
            controls: ['searchControl', 'zoomControl']
        }, {
            searchControlProvider: 'yandex#search'
        });
        <? foreach ($orders as $item){ ?>
        myPlacemark = new ymaps.Placemark([<?=$item->delivery_address_geo_lat?>, <?=$item->delivery_address_geo_lon?>], {
            balloonContentHeader: <?=$item->crm_id ?>,
            balloonContentBody: "<?
            if (isset($item->delivery_address_city)) echo $item->delivery_address_city;
            if (isset($item->delivery_address_street)) echo ", " . $item->delivery_address_street;
            if (isset($item->delivery_address_building)) echo ", " . $item->delivery_address_building;
            //              if(isset($item->delivery_address_house)) echo ", ".$item->delivery_address_house;
            ?>
            "+ " <br> "+
        "<?
            if (isset($item->delivery_date)) echo $item->delivery_date;
            if (isset($item->delivery_time)) echo ", " . $item->delivery_time;
            ?>",
            balloonContentFooter
    :
        "<a target='_blank' href='/order/view/<?=$item->id?>'>Перейти в " + "<?=$item->crm_id?></a>",
            hintContent
    :
        <?=$item->crm_id ?>
    },
        typeof date != "undefined" && timeFrom < 1 && timeTo < 1 ?
            ("<?=$item->delivery_time_ordering?>" - date  >= 0 && "<?=$item->delivery_time_ordering?>" - date  <= 100  ?
                {preset: 'islands#redIcon'}
                :
                ("<?=$item->delivery_time_ordering?>" - date  >= 100 && "<?=$item->delivery_time_ordering?>" - date  <= 200 ?
                    {preset: 'islands#yellowIcon'}
                    :
                    ("<?=$item->delivery_time_ordering?>" - date < 0 ?
                        {preset: 'islands#redIcon'}
                        :
                        ({preset: 'islands#greenIcon'}))))
            : timeFrom > 1 && timeTo >1?
            ("<?=$item->delivery_time_ordering_start?>" <= timeFrom  &&
            "<?=$item->delivery_time_ordering?>" >= timeTo && "<?=$item->delivery_time_ordering?>" -100 <= timeFrom ?
                {preset: 'islands#redIcon'}
                :
                ("<?=$item->delivery_time_ordering_start?>" <= timeFrom  &&
                "<?=$item->delivery_time_ordering?>" >= timeTo && "<?=$item->delivery_time_ordering?>" -200 <= timeFrom ?
                    {preset: 'islands#yellowIcon'}
                    :
                    ({preset: 'islands#greenIcon'})))
            :"",
    );

        // "<?=$item->delivery_time_ordering?>" - date<0 ?
        //     console.log(<?=$item->delivery_time_ordering?>): alert("ne tut ");
        myMap.geoObjects.add(myPlacemark);
        <?} ?>
    }

</script>

<script>
    $('#input-time').mask(' 99:99');
    $('#input-time2').mask(' 99:99');
</script>
