<?php

use yii\db\Migration;

/**
 * Class m200710_140346_add_settings_table
 */
class m200710_140346_add_settings_table extends Migration
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

        $this->createTable('{{%settings}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'value' => $this->text(),
        ], $tableOptions);

        $settings = [
            ['retailCrmUrl', 'https://flowerbarkraft3.retailcrm.ru'],
            ['retailCrmApiKey', 'OhbxE2g7Nzwf8ICnFQTp0cQAVZSnQxMP'],
            ['retailCrmStatuses', 'Подтверждён, Собран, Доставляется, В работе, Выполнен, Нет в наличии, Предложить замену'],
        ];

        $this->batchInsert('settings', ['key', 'value'], $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200710_140346_add_settings_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200710_140346_add_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
