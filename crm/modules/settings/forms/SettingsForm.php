<?php

namespace crm\modules\settings\forms;

use common\components\settings\models\SettingsModel;
use yii\base\Model;

/**
 * Class SettingsForm
 * @package crm\modules\settings\forms
 *
 * @property array $retailCrmStatuses
 */
class SettingsForm extends Model
{
    public $retailCrmUrl;
    public $retailCrmApiKey;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['retailCrmUrl', 'retailCrmApiKey'], 'required'],
            [['retailCrmUrl', 'retailCrmApiKey'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->attributes = SettingsModel::find()
            ->select(['value', 'key'])
            ->where(['!=', 'key', SettingsModel::PARAM_CRM_STATUS_LIST])
            ->indexBy('key')
            ->asArray()
            ->column();
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'retailCrmUrl' => 'Адрес retailCRM',
            'retailCrmApiKey' => 'API-ключ retailCRM',
        ];
    }

    /**
     * Сохранение данных
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $attributes = $this->getAttributes(null, [SettingsModel::PARAM_CRM_STATUS_LIST]);
        foreach ($attributes as $attribute => $value) {
            $this->saveAttribute($attribute, $value);
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $value
     */
    protected function saveAttribute($attribute, $value)
    {
        $settings = SettingsModel::findOne(['key' => $attribute]);

        if (!$settings) {
            $settings = new SettingsModel(['key' => $attribute]);
        }

        $settings->value = $value;
        $settings->save(false);
    }
}
