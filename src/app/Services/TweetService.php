<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\TweetRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\UserProfileRepositoryInterface;

//use App\Http\Controllers\Api\BoardController;

class TweetService extends Bases\BaseService
{
    // リポジトリ
    protected $tweetRepo;
    protected $userRepo;

    const tweet_flg_commnet =2;

    public function __construct(
        TweetRepositoryInterface $tweetRepo,
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        UserProfileRepositoryInterface $userProfileRepo
    ) {
        // リポジトリ
        $this->tweetRepo = $tweetRepo;
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->userProfileRepo = $userProfileRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->tweetRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        $arr =  $this->tweetRepo->getList($search);
        foreach($arr as $key=>$val){
            //コメント数
            $arr[$key]["comment_cnt"] = $this->tweetRepo->getCountData(["parent_id" => $val["id"], "block_users" => (!empty($search["block_users"])) ? $search["block_users"] : []]);

            //リクエスト投稿者情報の取得
            $tmp = $this->userProfileRepo->getItem(["user_id" => $val["user_id"]]);
            $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $val["user_id"]]);

            $arr[$key]["image_id"] = $tmp->image_id;
            $arr[$key]["nickname"] = $tmp->nickname;
            $arr[$key]["kanri_user_flg"] = $tmpDetail->kanri_user_flg;

            

            //ログイン者がいいね等のボタンを押しているか。
            $arr[$key]["history"] = 0;
            if(!empty($search["login_user_id"])){
                $where["user_id"] = $search["login_user_id"];
                $where["tweet_id"] = $val["id"];
                $tmp = $this->tweetRepo->getItemCheckUser($where);


                if(!empty($tmp)){
                    $arr[$key]["history"] = 1;
                }
            }

        }

        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->tweetRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->tweetRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data, $user_id)
    {

        //カウントアップ
        //いいね等の人数を増やす
        if(isset($data["check1"]) || isset($data["check2"]) || isset($data["check3"])){

            if(!$this->tweetRepo->updateIncrement($where, $data)) return false;

            //押下記録
            $value["user_id"] = $user_id;
            $value["tweet_id"] = $where["id"];
            if(isset($data["check1"])){
                $value["check"] = $data["check1"];
            }
            elseif(isset($data["check2"])){
                $value["check"] = $data["check2"];
            }
            elseif(isset($data["check3"])){
                $value["check"] = $data["check3"];
            }

            $this->tweetRepo->createCheckUser($value);

        }
        else{

            // 更新
            if(!$this->tweetRepo->updateItem($where, $data)) return false;

            // 取得
            //if(!$admin = $this->getItem($where)) return false;
        }

        return $this->getItem($where);


    }

    // 削除
    public function deleteItem($where)
    {
        return $this->tweetRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {


        return $this->tweetRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->tweetRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id, $search)
    {
        $arr =  $this->getItem(['id'=>$id]);

        //投稿者情報の取得
        $tmp = $this->userProfileRepo->getItem(["user_id" => $arr["user_id"]]);
        $arr["image_id"] = $tmp->image_id;
        $arr["nickname"] = $tmp->nickname;

        $tmp = $this->userRepo->getItem(["id" => $arr["user_id"]]);
        $arr["email"] = $tmp->email;

        $tmp = $this->userDetailRepo->getItem(["user_id" => $arr["user_id"]]);
        $arr["name"] = $tmp->last_name.$tmp->first_name;
        $arr["kanri_user_flg"] = $tmp->kanri_user_flg;

        //コメント数
        $arr["comment_cnt"] = $this->tweetRepo->getCountData(["parent_id" => $id, "block_users" => (!empty($search["block_users"])) ? $search["block_users"] : []]);

        return $arr;

    }

    private function getParentData($parent_id){


        $arr = $this->tweetRepo->getItem(["id" => $parent_id]);
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

        $tmp = $arr = $this->tweetRepo->getItems(["parent_id" => $id], $limit, $orderby);
        if(count($tmp) != 0){
            foreach($tmp as $key=>$val){
                $id = $val["id"];

                $this->child[] = $val;
                $this->getChildData($id, $current_id);

            }
        }

        return true;
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
        $treeArr["currentArr"] = $this->tweetRepo->getItem(["id" => $tweet_id]);
        //コメント数
        $treeArr["currentArr"]["comment_cnt"] = $this->tweetRepo->getCountData(["parent_id" => $treeArr["currentArr"]["id"]]);
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
        $treeArr["tweetArr"]["comment_cnt"] = $this->tweetRepo->getCountData(["parent_id" => $treeArr["tweetArr"]["id"]]);
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
            $val["comment_cnt"] = $this->tweetRepo->getCountData(["parent_id" => $val["id"]]);
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
                $val["comment_cnt"] = $this->tweetRepo->getCountData(["parent_id" => $val["id"]]);
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


    /*
     * 指定ユーザーのツイートリアクション総数を取得する
     */
    public function getUserSum($user_id){


        return $this->tweetRepo->getItemCheckUserSum(["user_id"=>$user_id]);

    }





}
