<?php

use yii\db\Migration;

/**
 * Class m210222_165059_add_user_delivery_table
 */
class m210222_165059_add_user_delivery_table extends Migration
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

        $this->createTable('{{%user_delivery}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->defaultValue(false),
            'city' => $this->string(),
            'comment' => $this->text(),
            'street' => $this->string(),
            'building' => $this->string(),
            'floor' => $this->string(),
            'flat' => $this->string(),
            'sender_name' => $this->string(),
            'sender_phone' => $this->string(),
            'geo_lon' => $this->string(),
            'geo_lat' => $this->string(),
        ], $tableOptions);

        $this->createIndex('IN_user_id', '{{%user_delivery}}', 'user_id');
        $this->addForeignKey('FK_user_delivery_user_id', '{{%user_delivery}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210222_165059_add_user_delivery_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210222_165059_add_user_delivery_table cannot be reverted.\n";

        return false;
    }
    */
}
