<?php

namespace crm\modules\user\models;

use common\models\User;
use yii\data\ActiveDataProvider;

/**
 * Class UserSearch
 * @package crm\modules\user\models
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'group'], 'integer'],
            [['name', 'username'], 'safe'],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'username' => $this->username,
            'name' => $this->name,
            'status' => $this->status,
            'group' => $this->group,
        ]);

        return $dataProvider;
    }
}
