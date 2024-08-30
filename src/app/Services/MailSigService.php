<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\MailSigRepositoryInterface;

class MailSigService extends Bases\BaseService
{
    // リポジトリ
    protected $mailSigRepo;

    public function __construct(
        MailSigRepositoryInterface $mailSigRepo
    ) {
        // リポジトリ
        $this->mailSigRepo = $mailSigRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->mailSigRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        return $this->mailSigRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->mailSigRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->mailSigRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->mailSigRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->mailSigRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->mailSigRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->mailSigRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->mailSigRepo->getItemById($id);
    }

}
