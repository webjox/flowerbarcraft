<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%status}}`.
 */
class m200720_190330_add_columns_to_status_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('status', 'show_in_list', $this->boolean()->defaultValue(1)->after('available'));
        $this->addColumn('status', 'color', $this->string()->null()->after('show_in_list'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
