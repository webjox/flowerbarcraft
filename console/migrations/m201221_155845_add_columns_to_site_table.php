<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%site}}`.
 */
class m201221_155845_add_columns_to_site_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('site', 'is_main', $this->boolean()->defaultValue(false)->after('code'));
        $this->addColumn('site', 'is_denial', $this->boolean()->defaultValue(false)->after('is_main'));
        $this->addColumn('site', 'parent_id', $this->integer()->null()->after('id'));
        $this->addColumn('site', 'probability', $this->integer()->null()->after('code'));
        $this->addColumn('site', 'timezone', $this->string()->null()->after('probability'));

        $this->createIndex('IN_parent_id', 'site', 'parent_id');
        $this->addForeignKey(
            'FK_site_parent_id', 'site', 'parent_id', '{{%site}}', 'id', 'SET NULL', 'NO ACTION'
        );

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_site_denial}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'site_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('IN_order_id', 'order_site_denial', 'order_id');
        $this->addForeignKey(
            'FK_order_site_denial_order_id', 'order_site_denial', 'order_id', '{{%order}}', 'id', 'CASCADE', 'NO ACTION'
        );

        $this->createIndex('IN_site_id', 'order_site_denial', 'site_id');
        $this->addForeignKey(
            'FK_order_site_denial_site_id', 'order_site_denial', 'site_id', '{{%site}}', 'id', 'CASCADE', 'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
