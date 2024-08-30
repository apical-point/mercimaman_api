<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\ProductMessageRepositoryInterface;

class ProductMessageService extends Bases\BaseService
{
    // リポジトリ
    protected $productMessageRepo;

    public function __construct(
        ProductMessageRepositoryInterface $productMessageRepo
    ) {
        // リポジトリ
        $this->productMessageRepo = $productMessageRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->productMessageRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->productMessageRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->productMessageRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->productMessageRepo->getItem($where);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->productMessageRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->productMessageRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->productMessageRepo->deleteItems($where);
    }

    // --------------------------- id系 ---------------------------
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // idで更新
    public function updateItemById($id, $data)
    {
        return $this->updateItem(['id'=>$id], $data);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->deleteItem(['id'=>$id]);
    }


}
