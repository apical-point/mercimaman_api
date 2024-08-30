<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\ProductBrandRepositoryInterface;

class ProductBrandService extends Bases\BaseService
{
    // リポジトリ
    protected $productBrandRepo;

    public function __construct(
        ProductBrandRepositoryInterface $productBrandRepo
    ) {
        // リポジトリ
        $this->productBrandRepo = $productBrandRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->productBrandRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->productBrandRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->productBrandRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->productBrandRepo->getItem($where);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->productBrandRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->productBrandRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->productBrandRepo->deleteItems($where);
    }

}
