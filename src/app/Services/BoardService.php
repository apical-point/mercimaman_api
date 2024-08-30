<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\BoardRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\UserProfileRepositoryInterface;

//use App\Http\Controllers\Api\BoardController;

class BoardService extends Bases\BaseService
{
    // リポジトリ
    protected $boardRepo;
    protected $userRepo;

    public function __construct(
        BoardRepositoryInterface $boardRepo,
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        UserProfileRepositoryInterface $userProfileRepo
    ) {
        // リポジトリ
        $this->boardRepo = $boardRepo;
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->userProfileRepo = $userProfileRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->boardRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        $arr =  $this->boardRepo->getList($search);
        foreach($arr as $key=>$val){
            //リクエスト投稿者情報の取得

            $tmp = $this->userProfileRepo->getItem(["user_id" => $val["user_id"]]);

            $arr[$key]["image_id"] = $tmp->image_id;
            $arr[$key]["nickname"] = $tmp->nickname;

        }

        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->boardRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->boardRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

        // 更新
        if(!$this->boardRepo->updateItem($where, $data)) return false;

        // 取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->boardRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->boardRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->boardRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {

        \Log::debug($id);
        $arr =  $this->getItem(['id'=>$id]);
        \Log::debug($arr);
        //リクエスト投稿者情報の取得
        $tmp = $this->userProfileRepo->getItem(["user_id" => $arr["user_id"]]);
        \Log::debug($tmp);
        $arr["image_id"] = $tmp->image_id;
        $arr["nickname"] = $tmp->nickname;

        return $arr;

    }

    //==== 体験記　掲示板 ============

    // 作成
    public function createItemExp(array $data, $password='')
    {
        if(!$admin = $this->boardRepo->createItemExp($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getListExp($search=[])
    {
        $arr =  $this->boardRepo->getListExp($search);
        foreach($arr as $key=>$val){

            //リクエスト投稿者情報の取得

            $tmp = $this->userProfileRepo->getItem(["user_id" => $val["user_id"]]);
            $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $val["user_id"]]);
            if(!empty($tmp)){
                $arr[$key]["image_id"] = $tmp->image_id;
                $arr[$key]["nickname"] = $tmp->nickname;
                $arr[$key]["kanri_user_flg"] = $tmpDetail->kanri_user_flg;
            }
            //紐づく体験記の件数
            //コメント数
            $arr[$key]["comment_cnt"] = $this->boardRepo->getCountData(["parent_id" => $val["id"], "block_users" => (!empty($search["block_users"])) ? $search["block_users"] : []]);

        }

        return $arr;
    }

    // 1件数の取得
    public function getItemByIdExp($id, $user_id)
    {
        $arr =  $this->boardRepo->getItemExp(['id'=>$id]);

        //リクエスト投稿者情報の取得
        $tmp = $this->userProfileRepo->getItem(["user_id" => $arr["user_id"]]);
        $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $arr["user_id"]]);
        $arr["image_id"] = $tmp->image_id;
        $arr["nickname"] = $tmp->nickname;
        $arr["kanri_user_flg"] = $tmpDetail->kanri_user_flg;

        //ログイン者がいいね等のボタンを押しているか。
        $where["user_id"] = $user_id;
        $where["experience_id"] = $id;

        $tmp = $this->boardRepo->getItemExperienceUser($where);
        $arr["history"] = 0;
        if(!empty($tmp)){
            $arr["history"] = 1;
        }

        return $arr;

    }

    // 更新
    public function updateItemExp($where, $data, $user_id)
    {
        //いいね等の人数を増やす
        if(isset($data["check1"]) || isset($data["check2"]) || isset($data["check3"]) || isset($data["check4"])){

            if(!$this->boardRepo->updateExpIncrement($where, $data)) return false;

            //押下記録
            $value["user_id"] = $user_id;
            $value["experience_id"] = $where["id"];
            $this->boardRepo->createExperienceUser($value);

        }
        else{
             // 内容更新
            if(!$this->boardRepo->updateItemExp($where, $data)) return false;
        }


        return true;
    }
    // idで削除
    public function deleteItemByIdExp($id)
    {
        return $this->boardRepo->deleteItemExp(['id'=>$id]);
    }

    public function deleteItemExp($where)
    {
        return $this->boardRepo->deleteItemExp($where);
    }

    // 画像の登録
    public function updateOrCreateImageData($Id, $imageData, $status)
    {
        $imageData['status'] = $status;
        if(!$image=$this->boardRepo->updateOrCreateImageData($Id, ['status'=>$status], $imageData)) return false;

        return $image;
    }

    /*
     *  $tweet_id の親達
     *  $tweet_id の子たち
     *
     *  ※子の時
     *   直下の返信は全て表示するが、孫返信に対しての返信は最新の1個のみ。
     *
     *
     */
    public function getTreetList($tweet_id)
    {

        //$tweet_id 情報の取得
        $treeArr["currentArr"] = $this->boardRepo->getItemExp(["id" => $tweet_id]);

        //コメント数
        $treeArr["currentArr"]["comment_cnt"] = $this->boardRepo->getCountData(["parent_id" => $treeArr["currentArr"]["id"]]);
        //投稿者情報
        $tmp = $this->userProfileRepo->getItem(["user_id" => $treeArr["currentArr"]["user_id"]]);
        $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $treeArr["currentArr"]["user_id"]]);
        $treeArr["currentArr"]["image_id"] = $tmp->image_id;
        $treeArr["currentArr"]["nickname"] = $tmp->nickname;
        $treeArr["currentArr"]["kanri_user_flg"] = $tmpDetail->kanri_user_flg;

        //親たち
        $parent_id = $treeArr["currentArr"]["parent_id"];
        $this->getParentData($parent_id);

        //大元のツイート
        $treeArr["tweetArr"] = $this->tweetOya;
        //コメント数
        $treeArr["tweetArr"]["comment_cnt"] = $this->boardRepo->getCountData(["parent_id" => $treeArr["tweetArr"]["id"]]);
        //投稿者情報
        $tmp = $this->userProfileRepo->getItem(["user_id" => $treeArr["tweetArr"]["user_id"]]);
        $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $treeArr["tweetArr"]["user_id"]]);
        $treeArr["tweetArr"]["image_id"] = $tmp->image_id;
        $treeArr["tweetArr"]["nickname"] = $tmp->nickname;
        $treeArr["tweetArr"]["kanri_user_flg"] = $tmpDetail->kanri_user_flg;

        if(!empty($this->oya)){
            $this->oya = array_reverse($this->oya, true);
            $treeArr["parentArr"] = $this->oya;
        }
        else{
            $treeArr["parentArr"] = [];
        }

        foreach($treeArr["parentArr"] as $val){
            //コメント数
            $val["comment_cnt"] = $this->boardRepo->getCountData(["parent_id" => $val["id"]]);
            //投稿者情報
            $tmp = $this->userProfileRepo->getItem(["user_id" => $val["user_id"]]);
            $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $val["user_id"]]);
            $val["image_id"] = $tmp->image_id;
            $val["nickname"] = $tmp->nickname;
            $val["kanri_user_flg"] = $tmpDetail->kanri_user_flg;
        }

        //子たち
        $this->getChildData($tweet_id, $tweet_id);
        $treeArr["childArr"] = [];
        if(!empty($this->child)){
            $treeArr["childArr"] = $this->child;
            foreach($treeArr["childArr"] as $val){
                //コメント数
                $val["comment_cnt"] = $this->boardRepo->getCountData(["parent_id" => $val["id"]]);
                //投稿者情報
                $tmp = $this->userProfileRepo->getItem(["user_id" => $val["user_id"]]);
                $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $val["user_id"]]);
                $val["image_id"] = $tmp->image_id;
                $val["nickname"] = $tmp->nickname;
                $val["kanri_user_flg"] = $tmpDetail->kanri_user_flg;
            }
        }

        return $treeArr;

    }

    private function getParentData($parent_id){


        $arr = $this->boardRepo->getItemExp(["id" => $parent_id]);
        $parent_id = $arr["parent_id"];



        if($parent_id != 0){

            $this->oya[$parent_id] = $arr;
            $this->getParentData($parent_id);

        }
        else{
            $this->tweetOya = $arr;
        }

        return true;
    }

    //子たち
    //この場合、直下の返信は全てを出すが、孫返信は最初1件
    private function getChildData($id, $current_id){

        $orderby="";$limit="";
        if($id != $current_id){
            $orderby = "id ";
            $limit = 1;
        }

        $tmp = $arr = $this->boardRepo->getItemsExp(["parent_id" => $id], $limit, $orderby);
        if(count($tmp) != 0){
            foreach($tmp as $key=>$val){
                $id = $val["id"];

                $this->child[] = $val;
                $this->getChildData($id, $current_id);

            }
        }

        return true;
    }
}
