<?php

use common\components\settings\models\SettingsModel;
use yii\db\Migration;

/**
 * Class m200711_152433_add_status_table
 */
class m200711_152433_add_status_table extends Migration
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

        $this->createTable('{{%status}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'name' => $this->string(),
            'active' => $this->boolean()->defaultValue(0),
            'available' => $this->boolean()->defaultValue(0),
            'ordering' => $this->integer()->defaultValue(0),
        ], $tableOptions);

        SettingsModel::deleteAll("`key` LIKE '" . SettingsModel::PARAM_CRM_STATUS_LIST . "'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200711_152433_add_status_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200711_152433_add_status_table cannot be reverted.\n";

        return false;
    }
    */
}
