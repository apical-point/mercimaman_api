<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\NewsRepositoryInterface;
use App\Repositories\UserRepositoryInterface;

class NewsService extends Bases\BaseService
{
    // リポジトリ
    protected $newsRepo;
    protected $userRepo;

    public function __construct(
        NewsRepositoryInterface $newsRepo,
        UserRepositoryInterface $userRepo
    ) {
        // リポジトリ
        $this->newsRepo = $newsRepo;
        $this->userRepo = $userRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->newsRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->newsRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->newsRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->newsRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

        // 更新
        if(!$this->newsRepo->updateItem($where, $data)) return false;

        // 取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->newsRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->newsRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->newsRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // 日付で削除
    public function datedelete($where)
    {
        return $this->newsRepo->datedelete($where);
    }

}
