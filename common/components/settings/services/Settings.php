<?php

namespace common\components\settings\services;

use common\components\settings\models\SettingsModel;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;

/**
 * Class Settings
 * @package common\components\settings\services
 */
class Settings extends Component implements BootstrapInterface
{
    private $data;

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        $this->data = SettingsModel::find()->select(['value', 'key'])->indexBy('key')->asArray()->column();
    }

    /**
     * @param $key
     * @return array|string|null
     */
    public function get($key)
    {
        $data = $this->data[$key] ?? null;

        if ($key == SettingsModel::PARAM_CRM_STATUS_LIST) {
            $data = array_map('trim', explode(',', $data));
        }

        return $data;
    }
}
