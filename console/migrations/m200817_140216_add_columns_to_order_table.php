<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order}}`.
 */
class m200817_140216_add_columns_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'delivery_type', $this->string()->after('delivery_address_notes'));
        $this->addColumn('order', 'summ', $this->integer()->defaultValue(0)->after('delivery_cost'));
        $this->addColumn('order', 'initial_product_summ', $this->integer()->defaultValue(0)->after('summ'));

        $this->addColumn('order_item', 'initial_price', $this->integer()->defaultValue(0)->after('price'));
        $this->addColumn('order_item', 'discount_summ', $this->integer()->defaultValue(0)->after('summ'));
        $this->addColumn('order_item', 'weight', $this->integer()->null());

        $this->addColumn('product_offer', 'weight', $this->integer()->null()->after('price'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
