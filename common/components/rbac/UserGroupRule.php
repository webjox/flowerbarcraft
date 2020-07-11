<?php

namespace common\components\rbac;

use common\models\User;
use Yii;
use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * Class UserGroupRule
 * @package common\components\rbac
 */
class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    /**
     * @param int|string $user
     * @param Item $item
     * @param array $params
     * @return bool
     */
    public function execute($user, $item, $params)
    {
        if (!Yii::$app->user->isGuest) {
            $group = Yii::$app->user->identity->group;
            if ($item->name === User::ROLE_ADMIN) {
                return $group == User::GROUP_ADMIN;
            } elseif ($item->name === User::ROLE_FLORIST) {
                return $group == User::GROUP_FLORIST;
            }
        }
        return false;
    }
}
