<?php

use yii\db\Migration;

/**
 * Class m200708_063641_add_products_table
 */
class m200708_063641_add_products_table extends Migration
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

        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'article' => $this->string()->null(),
            'name' => $this->string(),
            'url' => $this->string()->null(),
            'image_url' => $this->string(),
            'description' => $this->text()->null(),
            'external_id' => $this->string()->null(),
            'quantity' => $this->integer(),
            'active' => $this->boolean(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createTable('{{%product_offer}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'offer_id' => $this->integer()->notNull(),
            'article' => $this->string()->null(),
            'name' => $this->string(),
            'price' => $this->integer()->notNull(),
            'external_id' => $this->string()->null(),
            'xml_id' => $this->string()->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('IN_product_id', 'product_offer', 'product_id');
        $this->addForeignKey(
            'FK_product_offer_product_id', 'product_offer', 'product_id', '{{%product}}', 'id', 'CASCADE', 'NO ACTION'
        );

        $this->createTable('{{%product_offer_image}}', [
            'id' => $this->primaryKey(),
            'offer_id' => $this->integer()->notNull(),
            'image_url' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createIndex('IN_offer_id', 'product_offer_image', 'offer_id');
        $this->addForeignKey(
            'FK_product_offer_image_offer_id', 'product_offer_image', 'offer_id', '{{%product_offer}}', 'id', 'CASCADE', 'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200708_063641_add_products_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200708_063641_add_products_table cannot be reverted.\n";

        return false;
    }
    */
}
