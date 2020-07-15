<?php

use yii\db\Migration;

/**
 * Class m200715_154402_add_order_update_table
 */
class m200715_154402_add_order_update_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_update_queue}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'status_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('IN_order_id', 'order_update_queue', 'order_id');
        $this->addForeignKey(
            'FK_order_update_queue_order_id', 'order_update_queue', 'order_id', '{{%order}}', 'id', 'CASCADE', 'NO ACTION'
        );

        $this->createIndex('IN_status_id', 'order_update_queue', 'status_id');
        $this->addForeignKey(
            'FK_order_update_queue_status_id', 'order_update_queue', 'status_id', '{{%status}}', 'id', 'CASCADE', 'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200715_154402_add_order_update_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200715_154402_add_order_update_table cannot be reverted.\n";

        return false;
    }
    */
}
