<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\GrowthRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\UserProfileRepositoryInterface;

use App\Services\UpFileService;

//use App\Http\Controllers\Api\BoardController;

class GrowthService extends Bases\BaseService
{
    // リポジトリ
    protected $growthRepo;
    protected $userRepo;

    protected $upFileService;

    public function __construct(
        GrowthRepositoryInterface $growthRepo,
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        UserProfileRepositoryInterface $userProfileRepo,
        UpFileService $upFileService
    ) {
        // リポジトリ
        $this->growthRepo = $growthRepo;
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->userProfileRepo = $userProfileRepo;

        $this->upFileService = $upFileService;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->growthRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        $arr =  $this->growthRepo->getList($search);

        //各年齢の出来る事リスト取得
        foreach($arr as $key=>$val){

            $age_no = $val["age_no"];

            $where["age_no"] = $age_no;
            $arr[$key]["childList"] = $this->growthRepo->getListGrowthAge($where);
        }

        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->growthRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->growthRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {
            // 更新
        if(!$this->growthRepo->updateItem($where, $data)) return false;
        return $this->getItem($where);
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->growthRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->growthRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->growthRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        $arr =  $this->getItem(['id'=>$id]);

        return $arr;

    }

//-------------各年齢で出来る事リスト用---------

    // 作成
    public function createItemGrowthAge(array $data, $password='')
    {
        if(!$admin = $this->growthRepo->createItemGrowthAge($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getListGrowthAge($search=[])
    {
        $arr =  $this->growthRepo->getListGrowthAge($search);

        return $arr;
    }

    // 1件数の取得
    public function getItemByIdGrowthAge($id)
    {
        $arr =  $this->getItemGrowthAge(['id'=>$id]);

        return $arr;

    }

    // 1件取得
    public function getItemGrowthAge($where)
    {
        return $this->growthRepo->getItemGrowthAge($where);
    }

    // 更新
    public function updateItemGrowthAge($where, $data)
    {
        // 更新
        if(!$this->growthRepo->updateItemGrowthAge($where, $data)) return false;
        return $this->getItemGrowthAge($where);
    }

    // idで削除
    public function deleteItemByIdGrowthAge($id)
    {
        return $this->growthRepo->deleteItemGrowthAge(['id'=>$id]);
    }

    //-------------自分の子供の出来たこと記録---------

    // 作成
    public function createItemGrowthUser(array $data, $password='')
    {
        if(!$admin = $this->growthRepo->createItemGrowthUser($data)) return false;

        // 返す
        return $admin;
    }

    public function updateOrCreateGrowthUser($where, $data)
    {
        if(!$admin = $this->growthRepo->updateOrCreateGrowthUser($where, $data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getListGrowthUser($search=[], $userData)
    {

        //ログイン者の子供取得
        $where = [];
        if(empty($search["user_id"])){
            $where["user_id"] = $userData["id"];
        }
        else{
            $where["user_id"] = $search["user_id"];
        }
        $tmp = $this->userProfileRepo->getItem($where);

        $cnt=0;
        $arr = [];

        if(!empty($tmp["child_birthday1"])){
            $arr[$cnt]["birthday"] = $tmp["child_birthday1"];
            $arr[$cnt]["gender"] = $tmp["child_gender1"];
            $arr[$cnt]["name"] = $tmp["child_name1"];
            $arr[$cnt]["field_name"] = "child_name1";
        }
        if(!empty($tmp["child_birthday2"])){
            $cnt++;
            $arr[$cnt]["birthday"] = $tmp["child_birthday2"];
            $arr[$cnt]["gender"] = $tmp["child_gender2"];
            $arr[$cnt]["name"] = $tmp["child_name2"];
            $arr[$cnt]["field_name"] = "child_name2";
        }
        if(!empty($tmp["child_birthday3"])){
            $cnt++;
            $arr[$cnt]["birthday"] = $tmp["child_birthday3"];
            $arr[$cnt]["gender"] = $tmp["child_gender3"];
            $arr[$cnt]["name"] = $tmp["child_name3"];
            $arr[$cnt]["field_name"] = "child_name3";
        }
        if(!empty($tmp["child_birthday4"])){
            $cnt++;
            $arr[$cnt]["birthday"] = $tmp["child_birthday4"];
            $arr[$cnt]["gender"] = $tmp["child_gender4"];
            $arr[$cnt]["name"] = $tmp["child_name4"];
            $arr[$cnt]["field_name"] = "child_name4";
        }
        if(!empty($tmp["child_birthday5"])){
            $cnt++;
            $arr[$cnt]["birthday"] = $tmp["child_birthday5"];
            $arr[$cnt]["gender"] = $tmp["child_gender5"];
            $arr[$cnt]["name"] = $tmp["child_name5"];
            $arr[$cnt]["field_name"] = "child_name5";
        }


        //該当年齢の出来ること。
        $where = [];
        $where["age_no"] = $search["age_no"];
        $ageArr = $this->growthRepo->getListGrowthAge($where);


        //該当年齢の成長記録取得
        foreach($arr as $key=>$val){//子供の人数分
            $growthArr[$key] = $val;

            //出来た事取得
            $where = [];
            $where["user_id"] = $userData["id"];
            $where["name"] = $val["field_name"];
            $tmp =  $this->growthRepo->getListGrowthUser($where);

            //出来たこと順に表示させるため、最初に出来たことの配列作成
            foreach($tmp as $k=>$v){//子供が出来た日
                //該当年齢の出来る事
                foreach($ageArr as $kk=>$vv){
                    if($v["growth_age_id"] == $vv["id"]){
                        $growth_id = $v["growth_age_id"];

                        $growthArr[$key]["possible"][$growth_id]["age_date_v"] = date('Y年m月d日', strtotime($v["age_date"]));
                        $growthArr[$key]["possible"][$growth_id]["age_date"] = date('Y/m/d', strtotime($v["age_date"]));

                        $growthArr[$key]["possible"][$growth_id]["name"] = $vv["name"];
                        $growthArr[$key]["possible"][$growth_id]["growth_age_id"] = $vv["id"];
                    }
                }
            }

            //子供がまだ出来ていない事用に内容入れる
            foreach($ageArr as $kk=>$vv){
                        $growth_id = $vv["id"];
                        $growthArr[$key]["possible"][$growth_id]["name"] = $vv["name"];
                        $growthArr[$key]["possible"][$growth_id]["growth_age_id"] = $vv["id"];
            }

        }

/*
        //該当年齢の成長記録取得
        foreach($arr as $key=>$val){//子供の人数分

            $growthArr[$key] = $val;

            foreach($ageArr as $k=>$v){//出来る事

                $where = [];
                $where["user_id"] = $userData["id"];
                $where["growth_age_id"] = $v["id"];
                $where["name"] = $val["field_name"];
                $tmp =  $this->growthRepo->getItemGrowthUser($where);
                $growthArr[$key]["possible"][$k]["age_date"] = "";
                if($tmp){
                    $growthArr[$key]["possible"][$k]["age_date_v"] = date('Y年m月d日', strtotime($tmp["age_date"]));
                    $growthArr[$key]["possible"][$k]["age_date"] = date('Y/m/d', strtotime($tmp["age_date"]));

                    $aaa[$k]["age_date_v"] = date('Y年m月d日', strtotime($tmp["age_date"]));
                    $aaa[$k]["age_date"] = date('Y/m/d', strtotime($tmp["age_date"]));

                }
                $growthArr[$key]["possible"][$k]["name"] = $v["name"];
                $growthArr[$key]["possible"][$k]["growth_age_id"] = $v["id"];

                $aaa[$k]["name"] = $v["name"];
                $aaa[$k]["growth_age_id"] = $v["id"];

            }

            //日付でソート
  //          $SortKey = array_column($aaa, 'age_date');
   //         array_multisort($SortKey, SORT_ASC, $list);


        }
*/
        return $growthArr;
    }

    //指定した子供の成長記録を取得する
    public function getListGrowthUserOne($search=[], $userData)
    {


        //ログイン者の子供取得
        $where = [];
        if(empty($search["user_id"])){
            $where["user_id"] = $userData["id"];
        }
        else{
            $where["user_id"] = $search["user_id"];

            //管理画面用　指定ユーザーのお子様成長情報全て取得
            $growthArr = $this->getListGrowthUserAll($where);
            return $growthArr;
        }

        $tmp = $this->userProfileRepo->getItem($where);

        //指定子供情報
        if($search["name"] == "child_name1"){
            $growthArr["birthday"] = $tmp["child_birthday1"];
            $growthArr["gender"] = $tmp["child_gender1"];
            $growthArr["name"] = $tmp["child_name1"];
        }
        if($search["name"] == "child_name2"){
            $growthArr["birthday"] = $tmp["child_birthday2"];
            $growthArr["gender"] = $tmp["child_gender2"];
            $growthArr["name"] = $tmp["child_name2"];
        }
        if($search["name"] == "child_name3"){
            $growthArr["birthday"] = $tmp["child_birthday3"];
            $growthArr["gender"] = $tmp["child_gender3"];
            $growthArr["name"] = $tmp["child_name3"];
        }
        if($search["name"] == "child_name4"){
            $growthArr["birthday"] = $tmp["child_birthday4"];
            $growthArr["gender"] = $tmp["child_gender4"];
            $growthArr["name"] = $tmp["child_name4"];
        }
        if($search["name"] == "child_name5"){
            $growthArr["birthday"] = $tmp["child_birthday5"];
            $growthArr["gender"] = $tmp["child_gender5"];
            $growthArr["name"] = $tmp["child_name5"];
        }


        //各年齢の出来ること。
        $where = [];
        $where["per_page"] = $search["per_page"];
        $ageArr = $this->growthRepo->getListGrowthAge($where);


        //登録済みの出来る事を出来た順のソートで取得
        $where = [];
        $where["user_id"] = $userData["id"];
        $where["name"] = $search["name"];
        $tmp =  $this->growthRepo->getListGrowthUser($where);

        foreach($tmp as $val){//登録済み
            foreach($ageArr as $k=>$v){//出来る事

                if($val["growth_age_id"] == $v["id"]){//出来たことを最初に入れておく。

                    $growth_age_id = $v["id"];

                    $growthArr["growth"][$growth_age_id] = $val;
                    $growthArr["growth"][$growth_age_id]["possible_name"] = $v["name"];

                    $growthArr["growth"][$growth_age_id]["age_date_v"] = date('Y年m月d日', strtotime($val["age_date"]));
                    $growthArr["growth"][$growth_age_id]["age_date"] = date('Y/m/d', strtotime($val["age_date"]));

                    //写真があれば取得(lodingで上手く取得できないので・・）
                    $where = [];
                    $where["up_file_able_id"] = $val["id"];
                    $where["up_file_able_type"] = "App\Repositories\Eloquent\Models\UserGrowth";
                    $uptmp = $this->upFileService->getItem($where);
                    if($uptmp){
                        $growthArr["growth"][$growth_age_id]["images"] = $uptmp;
                    }

                    break;
                }
            }
        }

        //できない事用
        foreach($ageArr as $kk=>$vv){
            $growth_age_id = $vv["id"];
            $growthArr["growth"][$growth_age_id]["possible_name"] = $vv["name"];
            $growthArr["growth"][$growth_age_id]["growth_age_id"] = $vv["id"];

        }

        return $growthArr;
    }


    /*
     * 指定ユーザーのお子様の成長記録を全て取得（全お子様の全年齢）
     *
     */
    public function getListGrowthUserAll($search){

        $tmp = $this->userProfileRepo->getItem($search);

        //指定子供情報
        if(!empty($tmp["child_birthday1"])){
            $growthArr[0]["birthday"] = $tmp["child_birthday1"];
            $growthArr[0]["gender"] = $tmp["child_gender1"];
            $growthArr[0]["name"] = $tmp["child_name1"];
            $growthArr[0]["field_name"] = "child_name1";
        }
        if(!empty($tmp["child_birthday2"])){
            $growthArr[1]["birthday"] = $tmp["child_birthday2"];
            $growthArr[1]["gender"] = $tmp["child_gender2"];
            $growthArr[1]["name"] = $tmp["child_name2"];
            $growthArr[1]["field_name"] = "child_name2";
        }
        if(!empty($tmp["child_birthday3"])){
            $growthArr[2]["birthday"] = $tmp["child_birthday3"];
            $growthArr[2]["gender"] = $tmp["child_gender3"];
            $growthArr[2]["name"] = $tmp["child_name3"];
            $growthArr[2]["field_name"] = "child_name3";
        }
        if(!empty($tmp["child_birthday4"])){
            $growthArr[3]["birthday"] = $tmp["child_birthday4"];
            $growthArr[3]["gender"] = $tmp["child_gender4"];
            $growthArr[3]["name"] = $tmp["child_name4"];
            $growthArr[3]["field_name"] = "child_name4";
        }
        if(!empty($tmp["child_birthday5"])){
            $growthArr[4]["birthday"] = $tmp["child_birthday5"];
            $growthArr[4]["gender"] = $tmp["child_gender5"];
            $growthArr[4]["name"] = $tmp["child_name5"];
            $growthArr[4]["field_name"] = "child_name5";
        }


        //各年齢の出来ること。
        $where = [];
        $ageArr = $this->growthRepo->getListGrowthAge($where);



        foreach($growthArr as $kk=>$vv){//子供数

            //登録済みの出来る事を出来た順のソートで取得
            $where = [];
            $where["user_id"] = $search["user_id"];
            $where["name"] = $vv["field_name"];
            $tmp =  $this->growthRepo->getListGrowthUser($where);
            foreach($tmp as $val){//子供の出来たこと　ソート的にまずこちらを先に配列に
                foreach($ageArr as $k=>$v){//出来る事

                    if($val["growth_age_id"] == $v["id"]){

                        $growth_age_id = $v["id"];

                        $growthArr[$kk]["growth"][$growth_age_id] = $val;
                        $growthArr[$kk]["growth"][$growth_age_id]["possible_name"] = $v["name"];

                        $growthArr[$kk]["growth"][$growth_age_id]["age_date_v"] = date('Y年m月d日', strtotime($val["age_date"]));
                        $growthArr[$kk]["growth"][$growth_age_id]["age_date"] = date('Y/m/d', strtotime($val["age_date"]));

                        //写真があれば取得(lodingで上手く取得できないので・・）
                        $where = [];
                        $where["up_file_able_id"] = $val["id"];
                        $where["up_file_able_type"] = "App\Repositories\Eloquent\Models\UserGrowth";
                        $uptmp = $this->upFileService->getItem($where);
                        if($uptmp){
                            $growthArr[$kk]["growth"][$growth_age_id]["images"] = $uptmp;
                        }

                        break;
                    }
                }

            }

            //出来ないこと用に。
            foreach($ageArr as $k=>$v){
                $growth_age_id = $v["id"];
                $growthArr[$kk]["growth"][$growth_age_id]["possible_name"] = $v["name"];
                $growthArr[$kk]["growth"][$growth_age_id]["growth_age_id"] = $v["id"];

            }
        }

/*
        foreach($ageArr as $k=>$v){//出来る事

            foreach($growthArr as $kk=>$vv){//子供数


                //登録済みの出来る事を出来た順のソートで取得
                $where = [];
                $where["user_id"] = $search["user_id"];
                $where["name"] = $vv["field_name"];
                $tmp =  $this->growthRepo->getListGrowthUser($where);

                $ari=0;
                foreach($tmp as $val){//登録済み
                    if($val["growth_age_id"] == $v["id"]){
                        $growthArr[$kk]["growth"][$k] = $val;
                        $growthArr[$kk]["growth"][$k]["possible_name"] = $v["name"];

                        $growthArr[$kk]["growth"][$k]["age_date_v"] = date('Y年m月d日', strtotime($val["age_date"]));
                        $growthArr[$kk]["growth"][$k]["age_date"] = date('Y/m/d', strtotime($val["age_date"]));

                        //写真があれば取得(lodingで上手く取得できないので・・）
                        $where = [];
                        $where["up_file_able_id"] = $val["id"];
                        $where["up_file_able_type"] = "App\Repositories\Eloquent\Models\UserGrowth";
                        $uptmp = $this->upFileService->getItem($where);
                        if($uptmp){
                            $growthArr[$kk]["growth"][$k]["images"] = $uptmp;
                        }

                        $ari = 1;
                        break;
                    }
                }


                if($ari == 0){
                    $growthArr[$kk]["growth"][$k]["possible_name"] = $v["name"];
                    $growthArr[$kk]["growth"][$k]["growth_age_id"] = $v["id"];
                    $growthArr[$kk]["growth"][$k]["age_date"] = "";
                }
            }
        }
*/
        return $growthArr;

    }



    // 1件数の取得
    public function getItemByIdGrowthUser($id)
    {
        $arr =  $this->getItemGrowthUser(['id'=>$id]);

        return $arr;

    }

    // 1件取得
    public function getItemGrowthUser($where)
    {
        return $this->growthRepo->getItemGrowthUser($where);
    }

    // 更新
    public function updateItemGrowthUser($where, $data)
    {
        // 更新
        if(!$this->growthRepo->updateItemGrowthUser($where, $data)) return false;
        return $this->getItemGrowthUser($where);
    }

    // idで削除
    public function deleteItemByIdGrowthUser($id)
    {
        return $this->growthRepo->deleteItemGrowthUser(['id'=>$id]);
    }

    // 画像の登録
    public function createImageByGrowth($messageId, $imageData)
    {

        if(!$image=$this->growthRepo->createImageByGrowth($messageId, $imageData)) return false;
        return $image;

    }
}
