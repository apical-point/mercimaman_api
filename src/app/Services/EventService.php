<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\EventRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\UserProfileRepositoryInterface;



//use App\Http\Controllers\Api\BoardController;

class EventService extends Bases\BaseService
{
    // リポジトリ
    protected $eventRepo;
    protected $userRepo;


    public function __construct(
        EventRepositoryInterface $eventRepo,
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        UserProfileRepositoryInterface $userProfileRepo
    ) {
        // リポジトリ
        $this->eventRepo = $eventRepo;
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->userProfileRepo = $userProfileRepo;

    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->eventRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        $arr =  $this->eventRepo->getList($search);
        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->eventRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->eventRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

            // 更新
        if(!$this->eventRepo->updateItem($where, $data)) return false;

        //トピックの編集（削除して追加する）
        $topicWhere["event_id"] = $where["id"];

        $this->eventRepo->deleteItemsTopic(["event_id" => $where["id"]]);

        foreach($data["topic"] as $key=>$val){
            if($val != ""){
                $topicData = [];
                $topicData["event_id"] = $where["id"];;
                $topicData["topic"] = $val;
                $topicData["tag"] = $data["tag"][$key];

                $this->eventRepo->createItemTopic($topicData);
            }
        }

        return $this->getItem($where);


    }

    // 削除
    public function deleteItem($where)
    {
        return $this->eventRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {


        return $this->eventRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->eventRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        $arr =  $this->getItem(['id'=>$id]);

        //トピック取得
        $arr["topic"] = $this->eventRepo->getItemsTopic(['event_id'=>$id]);

        return $arr;

    }




}
