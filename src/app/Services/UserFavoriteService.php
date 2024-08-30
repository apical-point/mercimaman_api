<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\UserFavoriteRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\UserProfileRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;

class UserFavoriteService extends Bases\BaseService
{
    // リポジトリ
    protected $userFavoriteRepo;
    protected $userRepo;
    protected $userDetailRepo;
    protected $userProfileRepo;
    protected $productRepo;

    public function __construct(
        UserFavoriteRepositoryInterface $userFavoriteRepo,
        ProductRepositoryInterface $productRepo,
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        UserProfileRepositoryInterface $userProfileRepo
    ) {
        // リポジトリ
        $this->userFavoriteRepo = $userFavoriteRepo;
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->userProfileRepo = $userProfileRepo;
        $this->productRepo = $productRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->userFavoriteRepo->createItem($data);
    }

    // 複数取得
    //指定ユーザーのお気に入り商品、フォロー、フォロワー一覧の取得に使用
    public function getList($search=[])
    {


        if($search["type"] == 1){//お気に入り商品の一覧作成
            $arr=  $this->userFavoriteRepo->getList($search);
            foreach($arr as $key=>$val){
                //気になる商品の取得
                $where = [];
                $where["id"] = $val["product_id"];
                $arr[$key]["product"] = $this->productRepo->getItem($where);
            }
        }
        else{
            //------- フォロー、フォロワー -----
            if($search["follow_type"] == 1){
                //フォロー一覧
                $arr =  $this->userFavoriteRepo->getList($search);
                $arr = $this->getUserItem($arr, $search["follow_type"]);
            }
            else{
                //フォロワー一覧
                $data["type"] = $search["type"];
                $data["follow_id"] = $search["user_id"];//フォロワー
                $arr =  $this->userFavoriteRepo->getList($data);
                $arr = $this->getUserItem($arr, $search["follow_type"]);
            }
         }

        return $arr;
    }

    protected function getUserItem($arr, $follow_type){


        foreach($arr as $key=>$val){
            //指定ID者がフォローしている人の情報取得
            $where = [];
            if($follow_type == 1){
                $where["user_id"] = $val["follow_id"];
            }
            else{
                $where["user_id"] = $val["user_id"];
            }

            $arr[$key]["user_detail"] = $this->userDetailRepo->getItem($where);
            $arr[$key]["user_profile"] = $this->userProfileRepo->getItem($where);

        }

        return $arr;
    }


    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->userFavoriteRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->userFavoriteRepo->getItem($where);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->userFavoriteRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->userFavoriteRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->userFavoriteRepo->deleteItems($where);
    }


    // --------------------------- id系 ---------------------------
    public function getfollowSum($id)
    {
        return $this->userFavoriteRepo->getfollowSum($id);
    }

    public function getfollowerSum($id)
    {
        return $this->userFavoriteRepo->getfollowerSum($id);
    }

    public function getfavoriteSum($id)
    {
        return $this->userFavoriteRepo->getfavoriteSum($id);
    }


}
