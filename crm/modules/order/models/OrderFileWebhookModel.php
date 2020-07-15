<?php

namespace crm\modules\order\models;

use common\components\order\models\OrderFileModel;
use common\components\retailcrm\RetailCrm;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class OrderFileWebhookModel
 * @package crm\modules\order\models
 */
class OrderFileWebhookModel extends Model
{
    public $crm_id;
    public $filename;
    public $type;
    public $created_at;
    public $size;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['filename', 'type', 'created_at', 'size'], 'safe'],
            ['crm_id', 'unique', 'targetClass' => OrderFileModel::class, 'targetAttribute' => 'crm_id'],
        ];
    }

    /**
     * @param array $data
     * @param string $formName
     * @return bool
     */
    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    /**
     * @param $orderId
     * @return bool
     * @throws InvalidConfigException
     */
    public function save($orderId)
    {
        $fileModel = OrderFileModel::findOne(['order_id' => $orderId, 'crm_id' => $this->crm_id]);
        if (!$fileModel) {
            $fileModel = new OrderFileModel(['order_id' => $orderId]);
            $crm = RetailCrm::getInstance();
            $response = $crm->request->fileDownload($this->crm_id);
            file_put_contents(Yii::getAlias("@crm/web/files/{$this->crm_id}_{$this->filename}"), $response->getResponseBody());
        }
        $fileModel->setAttributes($this->attributes, false);
        return $fileModel->save(false);
    }
}
