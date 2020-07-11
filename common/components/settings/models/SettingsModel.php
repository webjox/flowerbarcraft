<?php

namespace common\components\settings\models;

use yii\db\ActiveRecord;

/**
 * Class SettingsModel
 * @package common\components\settings\models
 * @property int $id [int(11)]
 * @property string $key [varchar(255)]
 * @property string $value
 */
class SettingsModel extends ActiveRecord
{
    const PARAM_CRM_STATUS_LIST = 'retailCrmStatuses';
    const PARAM_CRM_URL = 'retailCrmUrl';
    const PARAM_CRM_API_KEY = 'retailCrmApiKey';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%settings}}';
    }
}
