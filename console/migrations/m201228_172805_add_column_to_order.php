<?php

use yii\db\Migration;

/**
 * Class m201228_172805_add_column_to_order
 */
class m201228_172805_add_column_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'token', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201228_172805_add_column_to_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201228_172805_add_column_to_order cannot be reverted.\n";

        return false;
    }
    */
}
