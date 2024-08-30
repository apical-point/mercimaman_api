<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\AdminerRepositoryInterface;


class AdminerService extends Bases\BaseService
{
    // リポジトリ
    protected $adminerRepo;

    public function __construct(
        AdminerRepositoryInterface $adminerRepo
    ) {
        // リポジトリ
        $this->adminerRepo = $adminerRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        // パスワードのハッシュ化作成
        $data['password'] = $this->passwordHash($data['password']);

        // ユーザーの作成
        if(!$admin = $this->adminerRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->adminerRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->adminerRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->adminerRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

        // パスワードが指定されていれば入力データとする
        if(!empty($data['password'])){
            // パスワードのハッシュ化作成
            $data['password'] = $this->passwordHash($data['password']);
        }

        // ユーザーの更新
        if(!$this->adminerRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->adminerRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->adminerRepo->deleteItem($where);
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
    // 新規作成でメアドが使えるかどうか
    public function canUseEmailOfCreate($email)
    {
        return !empty($this->adminerRepo->getItem([['email', $email]])) ? false :true;
    }

    // 更新でメアドが使えるかどうか
    public function canUseEmailOfUpdate($id, $email)
    {
        return !empty($this->adminerRepo->getItem([['id', '!=', $id], ['email', $email]])) ? false :true;
    }


    // ---------------------------  ---------------------------
    // ハッシュ化
    public function passwordHash($password)
    {
        // return sha1($password);
        return Hash::make($password);
    }

    // パス変
    public function updatePasswordByIdAndPassword($id, $password)
    {
        // パスワードのハッシュ化
        $password = $this->passwordHash($password);

        return $this->adminerRepo->updateItem(['id'=>$id], ['password'=>$password]);
    }

}
