<?php

namespace common\components\rbac;

use common\models\User;
use Yii;
use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * Class SiteRule
 * @package common\components\rbac
 */
class SiteRule extends Rule
{
    public $name = 'inSite';

    /**
     * @param int|string $user
     * @param Item $item
     * @param array $params
     * @return bool
     */
    public function execute($user, $item, $params)
    {
        $currentSiteId = isset($params['id']) ? $params['id'] : 0;
        /* @var $userModel User */
        $userModel = Yii::$app->user->identity;
        if ($userModel->site_id == $currentSiteId) {
            return true;
        }
        return false;
    }
}
