<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\OrderPaymentRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;


class OrderPaymentService extends Bases\BaseService
{
    protected $orderPaymentRepo;
    protected $productRepo;

    public function __construct(
        OrderPaymentRepositoryInterface $orderPaymentRepo,
        ProductRepositoryInterface $productRepo
    ) {
        $this->orderPaymentRepo = $orderPaymentRepo;
        $this->productRepo = $productRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItemByOrderId($orderId, array $data)
    {
        return $this->orderPaymentRepo->createItemByOrderId($orderId, $data);
    }

    // 作成
    public function createItem(array $data)
    {
        if(!$item = $this->orderPaymentRepo->createItem($data)) return false;

        // 返す
        return $item;
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->orderPaymentRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->orderPaymentRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->orderPaymentRepo->getItem($where);
    }

    // idで1件取得
    public function getItemById($id)
    {
        return $this->orderPaymentRepo->getItem(['id'=>$id]);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->orderPaymentRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->orderPaymentRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->orderPaymentRepo->deleteItems($where);
    }




}
