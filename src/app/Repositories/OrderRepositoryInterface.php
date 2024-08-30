<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface OrderRepositoryInterface extends BaseBasicInterface
{

    // マイル履歴新規作成
    // public function createMileHistoryByOrderId($orderId, $data);

    // idsで取得
    public function getItemsByIds($ids);
    public function getSalesGroupList($search=[]);
    public function getpayment();

}
