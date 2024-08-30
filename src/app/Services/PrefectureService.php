<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\PrefectureRepositoryInterface;

class PrefectureService extends Bases\BaseService
{
    // リポジトリ
    protected $prefecturesRepo;

    public function __construct(
        PrefectureRepositoryInterface $prefecturesRepo
    ) {

        // リポジトリ
        $this->prefecturesRepo = $prefecturesRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        // パスワードが指定されていれば入力データとする
        if(!empty($password)) $data['password'] = $password;

        // ユーザーの作成
        if(!$user = $this->prefecturesRepo->createItem($data)) return false;

        // 返す
        return $user;
    }

    // リスト取得
    public function getList($search=[])
    {
        return $this->prefecturesRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->prefecturesRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->prefecturesRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {
        // ユーザーの更新
        if(!$this->prefecturesRepo->updateItem($where, $data)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->prefecturesRepo->deleteItem($where);
    }

    // i削除
    public function deleteItems($where)
    {
        return $this->prefecturesRepo->deleteItem($where);
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

    // --------------------------- チェック関数 ---------------------------

    // --------------------------- その他関数 ---------------------------
    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->prefecturesRepo->getItemsByIds($ids);
    }
}
