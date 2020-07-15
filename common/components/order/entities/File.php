<?php

namespace common\components\order\entities;

/**
 * Class File
 * @package common\components\order\entities
 */
class File
{
    public $crm_id;
    public $filename;
    public $type;
    public $created_at;
    public $size;

    /**
     * @param array $data
     * @return File
     */
    public static function create(array $data)
    {
        $item = new self();
        $item->crm_id = !empty($data['id']) ? $data['id'] : null;
        $item->filename = !empty($data['filename']) ? $data['filename'] : null;
        $item->type = !empty($data['type']) ? $data['type'] : null;
        $item->created_at = !empty($data['createdAt']) ? $data['createdAt'] : null;
        $item->size = !empty($data['size']) ? $data['size'] : null;

        return $item;
    }
}
