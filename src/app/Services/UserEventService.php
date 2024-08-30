<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\UserEventRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\UserProfileRepositoryInterface;

//use App\Http\Controllers\Api\BoardController;

class UserEventService extends Bases\BaseService
{
    // リポジトリ
    protected $userEventRepo;
    protected $userRepo;
    protected $userDetailRepo;
    protected $userProfileRepo;

    public function __construct(
        UserEventRepositoryInterface $userEventRepo,
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        UserProfileRepositoryInterface $userProfileRepo

    ) {
        // リポジトリ
        $this->userEventRepo = $userEventRepo;
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->userProfileRepo = $userProfileRepo;

    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->userEventRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        $arr =  $this->userEventRepo->getList($search);
        //参加人数
        foreach($arr as $key=>$val){
            $tmp = $this->getEventMemberItems(["user_event_id" => $val->id, "status" => 1]);
            $arr[$key]["join_member_cnt"] = count($tmp);

            //イベント投稿会員名
            $arr[$key]["user_detail"] = $this->userDetailRepo->getItem(["user_id" => $val->user_id]);
        }


        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->userEventRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {

        $arr = $this->userEventRepo->getItem($where);

        return $arr;
    }

    // 更新
    public function updateItem($where, $data)
    {
            // 更新
        if(!$this->userEventRepo->updateItem($where, $data)) return false;

        return $this->getItem($where);

    }

    // 削除
    public function deleteItem($where)
    {
        return $this->userEventRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {


        return $this->userEventRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->userEventRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        $arr =  $this->getItem(['id'=>$id]);

        return $arr;

    }

    // 画像の登録
    public function updateOrCreateImageData($Id, $imageData, $wh=[])
    {

        //$imageData['status'] = $status;
        //if(!$image=$this->userEventRepo->updateOrCreateImageData($Id, ['status'=>$status], $imageData)) return false;
        if(!$image=$this->userEventRepo->updateOrCreateImageData($Id, $wh, $imageData)) return false;
        return $image;
    }

    //参加メンバー情報　1件
    public function getEventMemberItems($where)
    {
        return $this->userEventRepo->getEventMemberItems($where);
    }

    public function getEventMemberItem($where)
    {
        return $this->userEventRepo->getEventMemberItem($where);
    }

    // メンバー参加情報更新
    public function updateEventMemberItem($where, $data)
    {
        // 更新
        if(!$this->userEventRepo->updateEventMemberItem($where, $data)) return false;

        return $this->getEventMemberItems($where);

    }

    public function getEventMemberList($search=[])
    {

        $arr =  $this->userEventRepo->getEventMemberList($search);
        //イベントと参加者情報取得
        foreach($arr as $key=>$val){

            $arr[$key]["event"] = $this->getItem(["id"=>$val->user_event_id]);
            $arr[$key]["event"]->loadMissing(['mainImage']);


            $arr[$key]["member_detail"] = $this->userProfileRepo->getItem(["user_id" => $val->user_id]);
            $tmpDetail = $this->userDetailRepo->getItem(["user_id" => $val->user_id]);
            $tmpUser = $this->userRepo->getItem(["id" => $val->user_id]);

            $arr[$key]["member_detail"]["name"] = $tmpDetail["last_name"]. $tmpDetail["first_name"];
            $arr[$key]["member_detail"]["email"] = $tmpUser["email"];



        }

        return $arr;
    }

    //指定年の各月の手数料合計
    public function getEventSales($search=[])
    {

        //申込が完了している申込日で検索する
        for($i=1;$i<=12;$i++){

            $data["status"] = 1;
            $data["attend_date"] = $search["year"]."-".sprintf('%02d', $i);
            $arr = $this->userEventRepo->getEventMemberList($data);
            $sales[$i] = 0;
            if(!empty($arr)){
                foreach($arr as $key=>$val){
                    $sales[$i] += $val["system_price"];
                }
            }
         }

        return $sales;


    }
}
