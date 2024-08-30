<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\AdvertisementRepositoryInterface;
use App\Repositories\UserRepositoryInterface;

class AdvertisementService extends Bases\BaseService
{
    // リポジトリ
    protected $advertisementRepo;
    protected $userRepo;

    public function __construct(
        AdvertisementRepositoryInterface $advertisementRepo,
        UserRepositoryInterface $userRepo
    ) {
        // リポジトリ
        $this->advertisementRepo = $advertisementRepo;
        $this->userRepo = $userRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->advertisementRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        return $this->advertisementRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->advertisementRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->advertisementRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->advertisementRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->advertisementRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->advertisementRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->advertisementRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // メイン画像の登録
    public function updateOrCreateImageData($Id, $imageData, $status)
    {
        $imageData['status'] = $status;
        if(!$image=$this->advertisementRepo->updateOrCreateImageData($Id, ['status'=>$status], $imageData)) return false;

        return $image;
    }

}
