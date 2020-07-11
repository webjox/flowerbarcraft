<?php

namespace crm\modules\settings\forms;

use common\components\settings\models\SettingsModel;
use yii\base\Model;

/**
 * Class SettingsForm
 * @package crm\modules\settings\forms
 */
class SettingsForm extends Model
{
    public $retailCrmUrl;
    public $retailCrmApiKey;
    public $retailCrmStatuses;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['retailCrmUrl', 'retailCrmApiKey', 'retailCrmStatuses'], 'required'],
            [['retailCrmUrl', 'retailCrmApiKey', 'retailCrmStatuses'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->attributes = SettingsModel::find()->select(['value', 'key'])->indexBy('key')->asArray()->column();
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'retailCrmUrl' => 'Адрес retailCRM',
            'retailCrmApiKey' => 'API-ключ retailCRM',
            'retailCrmStatuses' => 'Список статусов заказов',
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

        $attributes = $this->getAttributes();
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
