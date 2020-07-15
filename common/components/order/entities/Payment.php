<?php

namespace common\components\order\entities;

/**
 * Class Payment
 * @package common\components\order\entities
 */
class Payment
{
    public $crm_id;
    public $status;
    public $type;
    public $amount;
    public $paid_at;
    public $comment;

    /**
     * @param array $data
     * @return Payment
     */
    public static function create(array $data)
    {
        $payment = new self();
        $payment->crm_id = !empty($data['id']) ? $data['id'] : null;
        $payment->status = !empty($data['status']) ? $data['status'] : null;
        $payment->type = !empty($data['type']) ? $data['type'] : null;
        $payment->amount = !empty($data['amount']) ? (int)($data['amount'] * 100) : 0;
        $payment->paid_at = !empty($data['paidAt']) ? $data['paidAt'] : null;
        $payment->comment = !empty($data['comment']) ? $data['comment'] : null;

        return $payment;
    }
}
