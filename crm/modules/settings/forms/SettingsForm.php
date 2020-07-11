<?php

namespace crm\modules\settings\forms;

use common\components\settings\models\SettingsModel;
use common\components\settings\models\StatusModel;
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

    protected $_retailCrmStatuses;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['retailCrmUrl', 'retailCrmApiKey'], 'required'],
            [['retailCrmUrl', 'retailCrmApiKey'], 'string'],
            ['retailCrmStatuses', 'safe'],
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
            'retailCrmStatuses' => 'Список статусов заказов',
        ];
    }

    /**
     * @return array
     */
    public function getRetailCrmStatuses()
    {
        return StatusModel::find()
            ->select(['id'])
            ->where(['available' => true])
            ->column();
    }

    /**
     * @param $value
     */
    public function setRetailCrmStatuses($value)
    {
        $this->_retailCrmStatuses = $value;
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

        $statusIds = implode(',', array_values($this->_retailCrmStatuses));
        if (!empty($statusIds)) {
            StatusModel::updateAll(['available' => true], "id IN ({$statusIds})");
            StatusModel::updateAll(['available' => false], "id NOT IN ({$statusIds})");
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
