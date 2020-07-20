<?php

use yii\db\Migration;

/**
 * Class m200720_173913_add_columns_to_order
 */
class m200720_173913_add_columns_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'delivery_address_city', $this->string()->null()->after('delivery_address'));
        $this->addColumn('order', 'delivery_address_street', $this->string()->null()->after('delivery_address_city'));
        $this->addColumn('order', 'delivery_address_building', $this->string()->null()->after('delivery_address_street'));
        $this->addColumn('order', 'delivery_address_house', $this->string()->null()->after('delivery_address_building'));
        $this->addColumn('order', 'delivery_address_housing', $this->string()->null()->after('delivery_address_house'));
        $this->addColumn('order', 'delivery_address_block', $this->string()->null()->after('delivery_address_housing'));
        $this->addColumn('order', 'delivery_address_flat', $this->string()->null()->after('delivery_address_block'));
        $this->addColumn('order', 'delivery_address_floor', $this->string()->null()->after('delivery_address_flat'));
        $this->addColumn('order', 'delivery_address_metro', $this->string()->null()->after('delivery_address_floor'));
        $this->addColumn('order', 'delivery_address_notes', $this->text()->after('delivery_address_metro'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200720_173913_add_columns_to_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200720_173913_add_columns_to_order cannot be reverted.\n";

        return false;
    }
    */
}
