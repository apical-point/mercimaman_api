<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\SiteConfigRepositoryInterface;


class SiteConfigService extends Bases\BaseService
{
    protected $siteConfigRepo;

    public function __construct(
        SiteConfigRepositoryInterface $siteConfigRepo
    ) {
        $this->siteConfigRepo = $siteConfigRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    // public function createItem(array $data)
    // {
    //     return $this->siteConfigRepo->createItem($data);
    // }

    // 複数取得
    public function getList($search=[])
    {
        return $this->siteConfigRepo->getList($search);
    }

    // // 複数取得
    // public function getItems($where=[], $take=0, $orderByRaw='')
    // {
    //     return $this->siteConfigRepo->getItems($where, $take, $orderByRaw);
    // }

    // 1件取得
    public function getItem($where)
    {
        return $this->siteConfigRepo->getItem($where);
    }

    // Keyから1件取得
    public function getItemByKey($key)
    {
        $item=$this->siteConfigRepo->getItem(['key_name'=>$key]);
        return $item['value'];
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->siteConfigRepo->updateItem($where, $data);
    }

    // // 1件の削除
    // public function deleteItem($where)
    // {
    //     return $this->siteConfigRepo->deleteItem($where);
    // }

    // // 複数の削除
    // public function deleteItems($where)
    // {
    //     return $this->siteConfigRepo->deleteItems($where);
    // }


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


    // --------------------------- チェック関数 ---------------------------


    // --------------------------- その他の関数 ---------------------------



}


