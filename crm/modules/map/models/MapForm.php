<?php

namespace crm\modules\map\models;

use yii\base\Model;

class MapForm extends Model
{
    public $status;
    public $dateFrom;
    public $dateTo;
    public $timeFrom;
    public $timeTo;

    public function rules()
    {
        return [
            [['status','dateFrom','dateTo','timeFrom','timeTo'], 'safe'],
        ];
    }
}
