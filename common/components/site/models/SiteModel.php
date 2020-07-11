<?php

namespace common\components\site\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class SiteModel
 * @package common\components\site\models
 * @property int $id [int(11)]
 * @property string $name [varchar(255)]
 * @property string $code [varchar(255)]
 * @property bool $active [tinyint(1)]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 */
class SiteModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%site}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
