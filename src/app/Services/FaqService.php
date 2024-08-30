<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\FaqRepositoryInterface;

class FaqService extends Bases\BaseService
{
    // リポジトリ
    protected $faqRepo;

    public function __construct(
        FaqRepositoryInterface $faqRepo
    ) {
        // リポジトリ
        $this->faqRepo = $faqRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->faqRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        return $this->faqRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->faqRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->faqRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->faqRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->faqRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->faqRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->faqRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

}
