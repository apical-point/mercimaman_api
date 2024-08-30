<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\ContentMessageRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;

class ContentMessageService extends Bases\BaseService
{
    // リポジトリ
    protected $contentMessageRepo;

    public function __construct(
        UserDetailRepositoryInterface $userDetailRepo,
        ContentMessageRepositoryInterface $contentMessageRepo
    ) {
        // リポジトリ
        $this->contentMessageRepo = $contentMessageRepo;
        $this->userDetailRepo = $userDetailRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->contentMessageRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {

        $arr = $this->contentMessageRepo->getList($search);
        foreach($arr as $key=>$val){
            //$tmp = $this->userProfileRepo->getItem(["user_id" => $val["user_id"]]);
            $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $val["user_id"]]);
            if(!empty($tmpDetail)){
                //$arr[$key]["image_id"] = $tmp->image_id;
                //$arr[$key]["nickname"] = $tmp->nickname;
                $arr[$key]["kanri_user_flg"] = $tmpDetail->kanri_user_flg;
            }
            
        }
        
        return $arr;
        
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->contentMessageRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->contentMessageRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // ユーザーの更新
        if(!$this->contentMessageRepo->updateItem($where, $data)) return false;

        // 新しいユーザーの取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->contentMessageRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->contentMessageRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->contentMessageRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

}
