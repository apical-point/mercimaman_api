<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\MailTemplateRepositoryInterface;

class MailTemplateService extends Bases\BaseService
{
    // リポジトリ
    protected $mailTemplateRepo;

    public function __construct(
        MailTemplateRepositoryInterface $mailTemplateRepo
    ) {
        // リポジトリ
        $this->mailTemplateRepo = $mailTemplateRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->mailTemplateRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        return $this->mailTemplateRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->mailTemplateRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->mailTemplateRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->mailTemplateRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->mailTemplateRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->mailTemplateRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->mailTemplateRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->mailTemplateRepo->getItemById($id);
    }

}
