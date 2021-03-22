<?php

use yii\db\Migration;

/**
 * Class m210301_084447_add_order_delivery_table
 */
class m210301_084447_add_order_delivery_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_delivery}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'external_id' => $this->string(),
            'status' => $this->string(),
            'price' => $this->integer(),
            'accepted' => $this->boolean()->defaultValue(false),
            'source_city' => $this->string(),
            'source_street' => $this->string(),
            'source_building' => $this->string(),
            'source_floor' => $this->string(),
            'source_flat' => $this->string(),
            'source_sender_name' => $this->string(),
            'source_sender_phone' => $this->string(),
            'source_comment' => $this->text(),
            'source_geo_lon' => $this->string(),
            'source_geo_lat' => $this->string(),
            'destination_city' => $this->string(),
            'destination_street' => $this->string(),
            'destination_building' => $this->string(),
            'destination_floor' => $this->string(),
            'destination_flat' => $this->string(),
            'destination_recipient_name' => $this->string(),
            'destination_recipient_phone' => $this->string(),
            'destination_comment' => $this->text(),
            'destination_geo_lon' => $this->string(),
            'destination_geo_lat' => $this->string(),
            'comment' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('IN_order_id', '{{%order_delivery}}', 'order_id');
        $this->addForeignKey('FK_order_delivery_order_id', '{{%order_delivery}}', 'order_id', '{{%order}}', 'id', 'CASCADE', 'NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210301_084447_add_order_delivery_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210301_084447_add_order_delivery_table cannot be reverted.\n";

        return false;
    }
    */
}
