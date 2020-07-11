<?php

namespace console\controllers;

use common\components\rbac\UserGroupRule;
use common\models\User;
use Exception;
use Yii;
use yii\console\Controller;

/**
 * Class RbacController
 * @package console\controllers
 */
class RbacController extends Controller
{
    /**
     * @throws Exception
     */
    public function actionInit()
    {
        $authManager = Yii::$app->authManager;
        $authManager->removeAll();

        // Create roles
        $admin = $authManager->createRole(User::ROLE_ADMIN);
        $florist = $authManager->createRole(User::ROLE_FLORIST);

        // Add rule, based on UserExt->group === $user->group
        $userGroupRule = new UserGroupRule();
        $authManager->add($userGroupRule);

        // Add rule "UserGroupRule" in roles
        $admin->ruleName = $userGroupRule->name;
        $florist->ruleName = $userGroupRule->name;

        // Add roles in Yii::$app->authManager
        $authManager->add($admin);
        $authManager->add($florist);
    }
}
