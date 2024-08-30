<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface OrderDetailRepositoryInterface extends BaseBasicInterface
{
    // idで作成する
    public function createItemByOrderId($orderId, $data);

    public function getEvaluation($userId, $type);
}
