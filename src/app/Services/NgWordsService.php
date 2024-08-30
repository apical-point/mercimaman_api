<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\Eloquent\NgWordsRepository;


class NgWordsService extends Bases\BaseService
{
    // リポジトリ
    protected $NgWordsRepo;

    public function __construct(
        NgWordsRepository $NgWordsRepo
    ) {
        // リポジトリ
        $this->NgWordsRepo = $NgWordsRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {

        // ユーザーの作成
        if(!$admin = $this->NgWordsRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->NgWordsRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[])
    {
        return $this->NgWordsRepo->getItems($where);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->NgWordsRepo->getItem($where);
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->NgWordsRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->NgWordsRepo->deleteItem($where);
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

    // idで削除
    public function checkNgWords($data)
    {
        $ng_word_list = $this->NgWordsRepo->getList()->toArray();

        foreach($ng_word_list['data'] as $ng_word){

            if(strpos($data['word'], $ng_word['ng_word']) !== false){
                //'abcd'のなかに'bc'が含まれている場合
                return ['success' => false, 'data' => $ng_word['ng_word']];
            }

        }

        return ['success' => true];

    }

    // --------------------------- チェック関数 ---------------------------

}