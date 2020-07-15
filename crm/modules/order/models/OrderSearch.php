<?php

namespace crm\modules\order\models;

use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class OrderSearch
 * @package crm\modules\order\models
 */
class OrderSearch extends Order
{
    public $date;
    public $status;
    public $from_date;
    public $to_date;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['date', 'status', 'from_date', 'to_date'], 'safe'],
        ];
    }

    /**
     * @return string|null
     */
    public function getToDateToFilter()
    {
        if (!$this->to_date) {
            return null;
        }
        return DateTime::createFromFormat('d.m.Y', $this->to_date)->format('Y-m-d');
    }

    /**
     * @return string|null
     */
    public function getFromDateToFilter()
    {
        if (!$this->from_date) {
            return null;
        }
        return DateTime::createFromFormat('d.m.Y', $this->from_date)->format('Y-m-d');
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find()->with('items.offer.images', 'status');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'crm_id',
                    'date' => [
                        'asc' => ['delivery_date' => SORT_ASC, new Expression('delivery_time_ordering IS NULL ASC')],
                        'desc' => ['delivery_date' => SORT_DESC, new Expression('delivery_time_ordering IS NULL DESC')],
                        'default' => SORT_ASC
                    ],
                ],
                'defaultOrder' => ['crm_id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        $query->where(['site_id' => Yii::$app->user->identity->site_id]);
        $query->andFilterWhere(['status_id' => $this->status]);
        $query->andFilterWhere(['>=', 'delivery_date', $this->getFromDateToFilter()]);
        $query->andFilterWhere(['<=', 'delivery_date', $this->getToDateToFilter()]);

        return $dataProvider;
    }
}
