<?php

use yii\db\Migration;

/**
 * Class m200712_064742_add_order_tables
 */
class m200712_064742_add_order_tables extends Migration
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

        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'crm_id' => $this->integer()->notNull()->unique(),
            'number' => $this->string()->null(),
            'external_id' => $this->string()->null(),
            'site_id' => $this->integer(),
            'status_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'customer_last_name' => $this->string()->null(),
            'customer_first_name' => $this->string()->null(),
            'customer_patronymic' => $this->string()->null(),
            'customer_phone' => $this->string()->null(),
            'customer_additional_phone' => $this->string()->null(),
            'recipient_name' => $this->string()->null(),
            'recipient_phone' => $this->string()->null(),
            'customer_comment' => $this->text(),
            'manager_comment' => $this->text(),
            'delivery_address' => $this->text(),
            'delivery_date' => $this->date(),
            'delivery_time' => $this->string()->null(),
            'delivery_time_ordering' => $this->integer()->null(),
            'delivery_cost' => $this->integer()->defaultValue(0),
            'total_summ' => $this->integer()->defaultValue(0),
            'prepay_sum' => $this->integer()->defaultValue(0),
            'to_pay_summ' => $this->integer()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('IN_site_id', 'order', 'site_id');
        $this->addForeignKey(
            'FK_order_site_id', 'order', 'site_id', '{{%site}}', 'id', 'SET NULL', 'NO ACTION'
        );

        $this->createIndex('IN_status_id', 'order', 'status_id');
        $this->addForeignKey(
            'FK_order_status_id', 'order', 'status_id', '{{%status}}', 'id', 'SET NULL', 'NO ACTION'
        );

        $this->createTable('{{%order_payment}}', [
            'id' => $this->primaryKey(),
            'crm_id' => $this->integer()->notNull()->unique(),
            'order_id' => $this->integer()->notNull(),
            'status' => $this->string()->null(),
            'type' => $this->string()->null(),
            'amount' => $this->integer()->defaultValue(0),
            'paid_at' => $this->dateTime(),
            'comment' => $this->text(),
        ], $tableOptions);

        $this->createIndex('IN_order_id', 'order_payment', 'order_id');
        $this->addForeignKey(
            'FK_order_payment_order_id', 'order_payment', 'order_id', '{{%order}}', 'id', 'CASCADE', 'NO ACTION'
        );

        $this->createTable('{{%order_item}}', [
            'id' => $this->primaryKey(),
            'crm_id' => $this->integer()->notNull()->unique(),
            'order_id' => $this->integer()->notNull(),
            'price' => $this->integer()->defaultValue(0),
            'quantity' => $this->integer()->defaultValue(0),
            'summ' => $this->integer()->defaultValue(0),
            'offer_id' => $this->integer()->null(),
            'crm_offer_id' => $this->string()->null(),
            'name' => $this->string()->null(),
        ], $tableOptions);

        $this->createIndex('IN_order_id', 'order_item', 'order_id');
        $this->addForeignKey(
            'FK_order_item_order_id', 'order_item', 'order_id', '{{%order}}', 'id', 'CASCADE', 'NO ACTION'
        );

        $this->createIndex('IN_offer_id', 'order_item', 'offer_id');
        $this->addForeignKey(
            'FK_order_item_offer_id', 'order_item', 'offer_id', '{{%product_offer}}', 'id', 'CASCADE', 'NO ACTION'
        );

        $this->createTable('{{%order_file}}', [
            'id' => $this->primaryKey(),
            'crm_id' => $this->integer()->notNull()->unique(),
            'order_id' => $this->integer()->notNull(),
            'filename' => $this->string()->notNull(),
            'type' => $this->string(),
            'created_at' => $this->dateTime(),
            'size' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('IN_order_id', 'order_file', 'order_id');
        $this->addForeignKey(
            'FK_order_file_order_id', 'order_file', 'order_id', '{{%order}}', 'id', 'CASCADE', 'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200712_064742_add_order_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200712_064742_add_order_tables cannot be reverted.\n";

        return false;
    }
    */
}
