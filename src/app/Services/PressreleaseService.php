<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\Eloquent\PressreleaseRepository;


class PressreleaseService extends Bases\BaseService
{
    // リポジトリ
    protected $pressreleaseRepo;

    public function __construct(
        PressreleaseRepository $pressreleaseRepo
    ) {
        // リポジトリ
        $this->pressreleaseRepo = $pressreleaseRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {

        // ユーザーの作成
        if(!$admin = $this->pressreleaseRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }
    

    // 複数取得
    public function getList($search=[])
    {
        return $this->pressreleaseRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[])
    {
        return $this->pressreleaseRepo->getItems($where);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->pressreleaseRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {
        \Log::debug($where);
        \Log::debug($data);

        return $this->pressreleaseRepo->updateItem($where, $data);
    }

    // 削除
    public function deleteItem($where)
    {
        \Log::debug($where);

        return $this->pressreleaseRepo->deleteItem($where);
    }

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