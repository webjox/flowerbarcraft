<?php

use yii\db\Migration;

/**
 * Class m201228_103654_add_tg_tables
 */
class m201228_103654_add_tg_tables extends Migration
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

        $this->createTable('{{%user_tg_chat}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'chat_id' => $this->integer()->notNull(),
            'tg_username' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%user_tg_code}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'is_activated' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('IN_user_id', '{{%user_tg_chat}}', 'user_id');
        $this->addForeignKey('FK_user_tg_chat_user_id', '{{%user_tg_chat}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'NO ACTION');

        $this->createIndex('IN_user_id', '{{%user_tg_code}}', 'user_id');
        $this->addForeignKey('FK_user_tg_code_user_id', '{{%user_tg_code}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201228_103654_add_tg_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201228_103654_add_tg_tables cannot be reverted.\n";

        return false;
    }
    */
}
