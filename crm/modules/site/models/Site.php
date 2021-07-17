<?php

namespace crm\modules\site\models;

use common\components\site\models\SiteModel;

/**
 * Class Site
 * @package crm\modules\site\models
 */
class Site extends SiteModel
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['is_main', 'is_denial','is_active_personal_area','is_active_map'], 'boolean'],
            ['probability', 'integer', 'min' => 1, 'max' => 99],
            [['city','city_lon','city_lat'], 'safe'],
            ['parent_id', 'validateParentId'],
            [['probability', 'timezone'], 'required', 'when' => function ($model) {
                return !empty($model->parent_id);
            }, 'whenClient' => "function (attribute, value) {
                return $('#parent-id').val() > 0;
            }"],
            ['timezone', 'in', 'range' => array_keys(self::TimezoneList())],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateParentId($attribute, $params)
    {
        if ($this->is_main) {
            $this->addError($attribute, 'Главный магазин должен быть корневым.');
        } elseif ($this->is_denial) {
            $this->addError($attribute, 'Отказной магазин должен быть корневым.');
        } elseif ($this->id == $this->$attribute) {
            $this->addError($attribute, 'Текущий магазин не может быть выбран в качестве родительского');
        } elseif (!empty($this->$attribute) && !self::find()->where(['is_main' => true, 'active' => true, 'id' => $this->$attribute])->exists()) {
            $this->addError($attribute, 'Выбранный магазин не может быть родительским');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->is_main || $this->is_denial || !$this->parent_id) {
                $this->parent_id = null;
                $this->probability = null;
                $this->timezone = null;
            }
            if ($this->is_main && $this->is_denial) {
                $this->is_denial = false;
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!empty($changedAttributes)) {
            if (isset($changedAttributes['is_main']) && $changedAttributes['is_main'] != $this->getOldAttribute('is_main')) {
                if (!$this->is_main) { // если магазин был главным и стал обычным, то обнуляем все связи с ним
                    self::updateAll(['parent_id' => null, 'probability' => null, 'timezone' => null], ['parent_id' => $this->id]);
                }
            }
            if (isset($changedAttributes['is_denial']) && $changedAttributes['is_denial'] != $this->getOldAttribute('is_denial')) {
                if ($this->is_denial) { // если магазин стал отказным, то отключаем другие отказные
                    self::updateAll(['is_denial' => false], ['!=', 'id', $this->id]);
                }
            }
        }
    }
}
