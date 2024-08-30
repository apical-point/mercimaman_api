<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\ProductCategoryRepositoryInterface;



class ProductCategoryService extends Bases\BaseService
{
    protected $productCategoryRepo;

    public function __construct(
        ProductCategoryRepositoryInterface $productCategoryRepo

    ) {
        $this->productCategoryRepo = $productCategoryRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        // 親idのデフォルトは0
//        if(empty($data['parentid'])) $data['parentid'] = 0;

        // 一番最後に追加する
//        $data['v_order'] = $this->getCountNumByParentid($data['parentid']) + 1;

        // 新規作成
        return $this->productCategoryRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->productCategoryRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->productCategoryRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->productCategoryRepo->getItem($where);
    }

    // idで取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // 更新
    public function updateItem($where, $data)
    {
        return $this->productCategoryRepo->updateItem($where, $data);
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->productCategoryRepo->deleteItem($where);
    }

    // --------------------------- チェック関数 ---------------------------
    // 親が存在するかどうか
    public function isParent($parentid)
    {
        if($parentid===0) return true;

        return $this->getItemById($parentid) ? true : false;
    }

    // カテゴリが存在するかどうか
    public function isItemById($id)
    {
        return $this->getItemById($id) ? true : false;
    }


    // --------------------------- その他関数 ---------------------------
    // 特定の親の子供の個数を返す
    public function getCountNumByParentid($parentid)
    {
        return $this->productCategoryRepo->getCountNumByParentid($parentid);
    }

    // 並び替え
    public function sortVOrder($data)
    {
        // 順番の定義
        $vOrder = 0;

        foreach($data['ids'] as $id) {
            // 順番を進める
            $vOrder++;

            // 更新
            $this->updateItem(['id'=>$id], ['v_order'=>$vOrder]);
        }

        return true;
    }




}
