<?php

use common\models\User;
use yii\db\Migration;

/**
 * Class m200710_091646_add_columns_to_user
 */
class m200710_091646_add_columns_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'name', $this->string()->notNull()->after('username'));
        $this->addColumn('user', 'group', $this->integer()->notNull()->after('email'));

        $password = Yii::$app->security->generateRandomString(8);
        $password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->insert('{{%user}}', [
            'username' => 'florist-admin',
            'password_hash' => $password_hash,
            'name' => 'Admin',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'email' => 'admin@crm.flowerbarkraft.ru',
            'status' => User::STATUS_ACTIVE,
            'group' => User::GROUP_ADMIN,
            'created_at' => time(),
            'updated_at' => time()
        ]);

        echo "Add user. Login: florist-admin, password: ".$password."\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200710_091646_add_columns_to_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200710_091646_add_columns_to_user cannot be reverted.\n";

        return false;
    }
    */
}
