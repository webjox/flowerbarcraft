<?php

use yii\db\Migration;

/**
 * Class m200710_124024_add_columns_to_user
 */
class m200710_124024_add_columns_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'site_id', $this->integer()->null()->after('email'));

        $this->createIndex('IN_site_id', 'user', 'site_id');
        $this->addForeignKey(
            'FK_user_site_id', 'user', 'site_id', '{{%site}}', 'id', 'SET NULL', 'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200710_124024_add_columns_to_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200710_124024_add_columns_to_user cannot be reverted.\n";

        return false;
    }
    */
}
