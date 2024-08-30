<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\UserProfileRepositoryInterface;

class UserProfileService extends Bases\BaseService
{
    // リポジトリ
    protected $userProfileRepo;

    public function __construct(
        UserProfileRepositoryInterface $userProfileRepo
    ) {
        // リポジトリ
        $this->userProfileRepo = $userProfileRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->userProfileRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->userProfileRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->userProfileRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->userProfileRepo->getItem($where);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->userProfileRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->userProfileRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->userProfileRepo->deleteItems($where);
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


    // --------------------------- user id系 ---------------------------
    // 作成
    public function createItemByUserId($userId, $data)
    {
        $data['user_id'] = $userId;
        return $this->createItem($data);
    }

    // 作成
    public function updateItemByUserId($userId, $data)
    {
        return $this->updateItem(['user_id'=>$userId], $data);
    }

    // 削除
    public function deleteItemByUserId($userId)
    {
        return $this->deleteItem(['user_id'=>$userId]);
    }

}
