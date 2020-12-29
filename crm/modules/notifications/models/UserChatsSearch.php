<?php


namespace crm\modules\notifications\models;

use common\components\user\models\UserTgChat;
use yii\data\ActiveDataProvider;

/**
 * Class UserChatsSearch
 * @package crm\modules\notifications\models
 */
class UserChatsSearch extends UserTgChat
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserTgChat::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andWhere(['user_id' => $this->user_id]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }
}
