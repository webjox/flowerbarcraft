<?php

use yii\db\Migration;

/**
 * Class m201225_165919_add_order_columns
 */
class m201225_165919_add_order_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'site_received_at', $this->integer()->after('site_id'));
        $this->addColumn('order', 'is_accepted', $this->boolean()->defaultValue(true)->after('site_received_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201225_165919_add_order_columns cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201225_165919_add_order_columns cannot be reverted.\n";

        return false;
    }
    */
}
