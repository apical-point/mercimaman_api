<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\MailDeliveryRepositoryInterface;

class MailDeliveryService extends Bases\BaseService
{
    // リポジトリ
    protected $mailDeliveryRepo;

    public function __construct(
        MailDeliveryRepositoryInterface $mailDeliveryRepo
    ) {
        // リポジトリ
        $this->mailDeliveryRepo = $mailDeliveryRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->mailDeliveryRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        return $this->mailDeliveryRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->mailDeliveryRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->mailDeliveryRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->mailDeliveryRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->mailDeliveryRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->mailDeliveryRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->mailDeliveryRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->mailDeliveryRepo->getItemById($id);
    }

}
