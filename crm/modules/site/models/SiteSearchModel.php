<?php

namespace crm\modules\site\models;

use common\components\site\models\SiteModel;
use yii\data\ActiveDataProvider;

/**
 * Class SiteSearchModel
 * @package crm\modules\site\models
 */
class SiteSearchModel extends SiteModel
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'string', 'max' => 255],
            ['active', 'boolean'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = SiteModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'code', $this->code]);
        $query->andFilterWhere(['active' => $this->active]);

        return $dataProvider;
    }
}
