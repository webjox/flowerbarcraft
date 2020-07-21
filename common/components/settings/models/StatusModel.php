<?php

namespace common\components\settings\models;

use yii\db\ActiveRecord;

/**
 * Class StatusModel
 * @package common\components\settings\models
 * @property int $id [int(11)]
 * @property string $code [varchar(255)]
 * @property string $name [varchar(255)]
 * @property bool $active [tinyint(1)]
 * @property bool $available [tinyint(1)]
 * @property int $ordering [int(11)]
 * @property bool $show_in_list [tinyint(1)]
 * @property string $color [varchar(255)]
 *
 * @property-read string $bgColor
 */
class StatusModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%status}}';
    }

    /**
     * @return string
     */
    public function getBgColor()
    {
        return $this->color ?: '#888888';
    }
}
