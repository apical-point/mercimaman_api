<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\InquiryRepositoryInterface;
use App\Repositories\UserRepositoryInterface;

class InquiryService extends Bases\BaseService
{
    // リポジトリ
    protected $inquiryRepo;
    protected $userRepo;

    public function __construct(
        InquiryRepositoryInterface $inquiryRepo,
        UserRepositoryInterface $userRepo
    ) {
        // リポジトリ
        $this->inquiryRepo = $inquiryRepo;
        $this->userRepo = $userRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->inquiryRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        $cateArr = config('const.inquiry_category');
        $arr = $this->inquiryRepo->getList($search);
        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->inquiryRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->inquiryRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->inquiryRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->inquiryRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->inquiryRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->inquiryRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

}
