<?php namespace App\Services;

// ulid
use \Ulid;

use App\Repositories\UserDetailRepositoryInterface;

class UserDetailService extends Bases\BaseService
{
    protected $userDetailRepo;

    public function __construct(
        UserDetailRepositoryInterface $userDetailRepo
    ) {
        $this->userDetailRepo = $userDetailRepo;
    }


    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->userDetailRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->userDetailRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->userDetailRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->userDetailRepo->getItem($where);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->userDetailRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->userDetailRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->userDetailRepo->deleteItems($where);
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

