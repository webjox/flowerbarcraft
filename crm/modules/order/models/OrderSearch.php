<?php

namespace crm\modules\order\models;

use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\Expression;

/**
 * Class OrderSearch
 * @package crm\modules\order\models
 *
 * @property-read null|string $fromDateToFilter
 * @property-read null|string $toDateToFilter
 */
class OrderSearch extends Order
{
    public $crm_id;
    public $recipient;
    public $customer;
    public $date;
    public $status;
    public $from_date;
    public $to_date;
    public $delivery_type;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['date', 'status', 'from_date', 'to_date', 'crm_id', 'recipient', 'customer','delivery_type'], 'safe'],
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



    public function getTime($date)
    {
        return (self::find()->select(['delivery_time'])->where(['>=','delivery_date',$date])->all());
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find()->with('items.offer.images', 'status')->joinWith('status');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'crm_id',
                    'date' => [
                        'asc' => ['delivery_date' => SORT_ASC, 'delivery_time_ordering' => SORT_ASC],
                        'desc' => ['delivery_date' => SORT_DESC,'delivery_time_ordering' => SORT_ASC],
                        'default' => SORT_ASC,
                    ],
                ],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        $query->where(['site_id' => Yii::$app->user->identity->site_id]);
        $query->andWhere(['status.show_in_list' => true]);
        $query->andWhere(['statusCrm' => [1,2,3,4]]);
//        $query->andWhere(['>=','delivery_date',date("Y-m-d")]);
        $query->andFilterWhere(['status_id' => $this->status]);
        $query->andFilterWhere(['>=', 'delivery_date', $this->getFromDateToFilter()]);
        $query->andFilterWhere(['<=', 'delivery_date', $this->getToDateToFilter()]);
        $query->andFilterWhere(['crm_id' => $this->crm_id]);
        $query->andFilterWhere([
            'or',
            ['like', 'recipient_name', $this->recipient],
            ['like', 'recipient_phone', $this->recipient],
        ]);
        $query->andFilterWhere(['delivery_type'=> $this->delivery_type]);
        $query->andFilterWhere([
            'or',
            ['like', 'customer_last_name', $this->customer],
            ['like', 'customer_first_name', $this->customer],
            ['like', 'customer_patronymic', $this->customer],
            ['like', 'customer_phone', $this->customer],
            ['like', 'customer_additional_phone', $this->customer],
        ]);

        return $dataProvider;
    }

    public function searchPaint($params)
    {
        $query = Order::find()->with('items.offer.images', 'status')->joinWith('status');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'crm_id',
                    'date' => [
                        'asc' => ['delivery_date' => SORT_ASC, 'delivery_time_ordering' => SORT_ASC],
                        'desc' => ['delivery_date' => SORT_DESC, 'delivery_time_ordering' => SORT_ASC],
                        'default' => SORT_ASC
                    ],
                ],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        $query->where(['site_id' => Yii::$app->user->identity->site_id]);
        $query->andWhere(['status.show_in_list' => true]);
        $query->andWhere(['statusCrm' => 1]);
        $query->andWhere(['status_id' => [1,19,20,24]]);
        $query->andFilterWhere(['status_id' => $this->status]);
        $query->andFilterWhere(['>=', 'delivery_date', $this->getFromDateToFilter()]);
        $query->andFilterWhere(['<=', 'delivery_date', $this->getToDateToFilter()]);
        $query->andFilterWhere(['crm_id' => $this->crm_id]);
        $query->andFilterWhere([
            'or',
            ['like', 'recipient_name', $this->recipient],
            ['like', 'recipient_phone', $this->recipient],
        ]);
        $query->andFilterWhere([
            'or',
            ['like', 'customer_last_name', $this->customer],
            ['like', 'customer_first_name', $this->customer],
            ['like', 'customer_patronymic', $this->customer],
            ['like', 'customer_phone', $this->customer],
            ['like', 'customer_additional_phone', $this->customer],
        ]);

        return $dataProvider;
    }

    public function searchPersonal($params)
    {
        $query = Order::find()->with('items.offer.images', 'status')->joinWith('status');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'crm_id',
                    'date' => [
                        'asc' => ['delivery_date' => SORT_ASC , 'delivery_time_ordering' => SORT_ASC],
                        'desc' => ['delivery_date' => SORT_DESC, 'delivery_time_ordering' => SORT_ASC],
                        'default' => SORT_ASC
                    ],
                ],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        $query->where(['site_id' => Yii::$app->user->identity->site_id]);
        $query->andWhere(['status.show_in_list' => true]);
        $query->andWhere(['statusCrm' => 2]);
        $query->andWhere(['user_id' => Yii::$app->user->identity->id]);
        $query->andFilterWhere(['status_id' => $this->status]);
        $query->andFilterWhere(['>=', 'delivery_date', $this->getFromDateToFilter()]);
        $query->andFilterWhere(['<=', 'delivery_date', $this->getToDateToFilter()]);
        $query->andFilterWhere(['crm_id' => $this->crm_id]);
        $query->andFilterWhere([
            'or',
            ['like', 'recipient_name', $this->recipient],
            ['like', 'recipient_phone', $this->recipient],
        ]);
        $query->andFilterWhere([
            'or',
            ['like', 'customer_last_name', $this->customer],
            ['like', 'customer_first_name', $this->customer],
            ['like', 'customer_patronymic', $this->customer],
            ['like', 'customer_phone', $this->customer],
            ['like', 'customer_additional_phone', $this->customer],
        ]);

        return $dataProvider;
    }

    public function searchBuild($params)
    {
        $query = Order::find()->with('items.offer.images', 'status')->joinWith('status');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'crm_id',
                    'date' => [
                        'asc' => ['delivery_date' => SORT_ASC, 'delivery_time_ordering' => SORT_ASC],
                        'desc' => ['delivery_date' => SORT_DESC,  'delivery_time_ordering' => SORT_ASC],
                        'default' => SORT_ASC
                    ],
                ],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        $query->where(['site_id' => Yii::$app->user->identity->site_id]);
        $query->andWhere(['status.show_in_list' => true]);
        $query->andFilterWhere(['or',
            ['statusCrm' => 3],
            ['status_id' => 26]]);
        $query->andFilterWhere(['status_id' => $this->status]);
        $query->andFilterWhere(['>=', 'delivery_date', $this->getFromDateToFilter()]);
        $query->andFilterWhere(['<=', 'delivery_date', $this->getToDateToFilter()]);
        $query->andFilterWhere(['crm_id' => $this->crm_id]);
        $query->andFilterWhere([
            'or',
            ['like', 'recipient_name', $this->recipient],
            ['like', 'recipient_phone', $this->recipient],
        ]);
        $query->andFilterWhere([
            'or',
            ['like', 'customer_last_name', $this->customer],
            ['like', 'customer_first_name', $this->customer],
            ['like', 'customer_patronymic', $this->customer],
            ['like', 'customer_phone', $this->customer],
            ['like', 'customer_additional_phone', $this->customer],
        ]);

        return $dataProvider;
    }
}
