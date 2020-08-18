<?php

use crm\modules\order\models\Order;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Order */
?>
<div class="pdf-wrap">
    <p>Заказ <b><?= Html::encode($model->number) ?></b></p>
    <hr>
    <div class="client-info">
        <p><b>ПОЛУЧАТЕЛЬ</b></p>
        <p><b>Имя: <?= Html::encode($model->recipient_name) ?></b></p>
        <p class="mb-15"><b>Телефон: <?= Html::encode($model->recipient_phone) ?></b></p>
        <p><b>ЗАКАЗЧИК</b></p>
        <p><b>Имя: <?= Html::encode($model->customerName) ?></b></p>
        <p><b>Телефон: <?= Html::encode($model->customerPhones) ?></b></p>
    </div>
    <div class="order-detail">
        <table>
            <tr class="table-header">
                <th>ДОСТАВКА</th>
                <th class="space"></th>
                <th>ОПЛАТА</th>
            </tr>
            <tr>
                <td>
                    <p><b>Тип: <?= Html::encode($model->delivery_type ?? '-') ?></b></p>
                    <hr style="color: #fff; height: 1px; margin: 2px 0">
                    <p><b>Адрес доставки: <?= Html::encode($model->deliveryAddress) ?></b></p>
                    <hr style="color: #fff; height: 1px; margin: 2px 0">
                    <p><b>Время доставки: <?= $model->deliveryDate ?></b></p>
                    <hr style="color: #fff; height: 1px; margin: 2px 0">
                    <p><b>Стоимость: <?= Yii::$app->formatter->asDecimal($model->deliveryCost, 2) ?> Р</b></p>
                </td>
                <td class="space"></td>
                <td>
                    <p><b>Статус: <?= $model->to_pay_summ == 0 ? 'Оплачено' : ($model->prepay_sum > 0 ? "Частичная оплата (осталось " . Yii::$app->formatter->asDecimal($model->toPaySumm, 2) . " руб.)" : 'Не оплачено') ?></b></p>
                </td>
            </tr>
        </table>
    </div>
    <div class="order-items">
        <p><b>СОСТАВ ЗАКАЗА</b></p>
        <table width="100%">
            <tr class="table-header">
                <th width="18%">Артикул</th>
                <th width="30%">Товар</th>
                <th width="14%">Цена</th>
                <th width="10%">Кол-во</th>
                <th width="14%">Скидка</th>
                <th width="14%">Стоимость</th>
            </tr>
            <tr><td colspan="6" class="hr"></td></tr>
            <?php foreach ($model->items as $item): ?>
                <tr>
                    <td><b><?= Html::encode($item->offer->article ?? '-') ?></b></td>
                    <td class="item-name"><b><?= Html::encode($item->name) ?></b></td>
                    <td><b><?= Yii::$app->formatter->asDecimal((int)(($item->initial_price ?: $item->offer->price) / 100), 2) ?> Р</b></td>
                    <td><b><?= $item->quantity ?></b></td>
                    <td><b><?= Yii::$app->formatter->asDecimal((int)(($item->discount_summ ?: 0) / 100), 2) ?> Р</b></td>
                    <td><b><?= Yii::$app->formatter->asDecimal((int)($item->summ / 100), 2) ?> Р</b></td>
                </tr>
                <tr><td colspan="6" class="hr"></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
    <p class="mb-10 align-right"><b>Сумма по товарам: <?= Yii::$app->formatter->asDecimal((int)($model->initial_product_summ / 100), 2) ?> Р</b></p>
    <p class="mb-10 align-right"><b>Сумма со скидкой: <?= Yii::$app->formatter->asDecimal((int)($model->summ / 100), 2) ?> Р</b></p>
    <p class="mb-10 align-right"><b>Стоимость доставки: <?= Yii::$app->formatter->asDecimal($model->deliveryCost, 2) ?> Р</b></p>
    <p class="align-right"><b>Итого: <?= Yii::$app->formatter->asDecimal($model->totalSumm, 2) ?> Р</b></p>
</div>
