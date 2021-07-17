<?php

namespace crm\modules\settings\models;

use common\components\settings\models\StatusModel;

/**
 * Class Status
 * @package crm\modules\settings\models
 */
class Status extends StatusModel
{
    /**
     * @inheritDoc
     */


    public function rules()
    {
        return [
            [['available', 'show_in_list','permission'], 'boolean'],
            [['color','nextStatus'], 'string'],
            ['available', 'validateAvailable'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Статус',
            'active' => 'Активен',
            'available' => 'Доступен',
            'show_in_list' => 'Показывать заказы',
            'permission' => 'Доступна смена статуса',
            'color' => 'Цвет',
            'bgColor' => 'Цвет',
            'nextStatus' => 'Следущий статус',
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateAvailable($attribute, $params)
    {
        if (!$this->active && $this->available) {
            $this->addError($attribute, 'Невозможно сделать доступным неактивный статус.');
        }
    }
}
