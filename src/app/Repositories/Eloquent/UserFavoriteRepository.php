<?php namespace App\Repositories\Eloquent;

use App\Repositories\UserFavoriteRepositoryInterface;
use App\Repositories\Eloquent\Models\UserFavorite;
use App\Repositories\Eloquent\Models\User;
use Illuminate\Support\Facades\Log;

class UserFavoriteRepository extends BaseEloquent implements UserFavoriteRepositoryInterface
{
    protected $userFavorite;

    public function __construct(
        UserFavorite $userFavorite,
        User $user
        ){
        parent::__construct();
        $this->userFavorite = $userFavorite;
        $this->user = $user;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('type', $keys)) if($search['type']!=NULL) $query = $query->where('type', $search['type']);
        if(in_array('follow_id', $keys)) if($search['follow_id']!=NULL) $query = $query->where('follow_id', $search['follow_id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);

        if(in_array('type', $keys)){
            if($search['type'] == 1){
                $query = $query->whereHas('product', function($query) {

                });
            }

            //自分がフォローしている人
            if($search['type'] == 2 && isset($search['user_id'])){
                $query = $query->whereHas('userFollow', function($query) {

                });
            }
            //自分をフォローしてくれてる人
            if($search['type'] == 2 && isset($search['follow_id'])){
                $query = $query->whereHas('userFollower', function($query) {

                });
            }
        }


        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("id","DESC");

        /*
        if(!empty($search['order_by'])) {
            //$query = $query->orderBy(, $search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }
        */

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->userFavorite;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 新規作成
    public function createItem(array $data)
    {
        return $this->userFavorite->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->userFavorite->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->userFavorite->where($where);
        if($take)  $query->take($take);
        if($orderByRaw)  $query->orderByRaw($orderByRaw);

        return $query->get();
    }

    // 更新
    public function updateItem(array $where, array $data)
    {
        if(empty($item=$this->getItem($where)))  return false;

        return $item->fill($data)->save();
    }

    // 複数の削除
    public function deleteItems(array $where)
    {
        return $this->userFavorite->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------


    // -------------------------------------  -------------------------------------
    // useridをキーにユーザー詳細を新規作成
    public function createUserFavoriteByUserId($userId, array $data)
    {
        // ユーザーの取得
        if(empty($item=$this->user->where(['id'=>$userId])->first()))  return false;

        // ユーザー詳細の作成
        return $item->userFavorite()->create($data);
    }

    // useridをキーにユーザー詳細を新規更新
    public function updateUserFavoriteByUserId($userId, array $data)
    {
        // 詳細の取得
        if(empty($Profil=$this->getItem(['user_id'=>$userId])))  return false;

        // 詳細の更新
        return $this->updateItem(['id'=>$Profil->id], $data);
    }

    //フォロー合計
    public function getfollowSum($id){

        $query = $this->userFavorite;

        $search["user_id"] = $id;
        $search["type"] = 2;
        return $this->getSearchQuery($query, $search)->count();

        //$query = $query->select(\DB::raw('count(user_id) as total'));
        //$query = $query->where('user_id', $id);
        //$query = $query->where('type', "2");

        //return $query->get();
    }

    //フォロワー合計
    public function getfollowerSum($id){

        $query = $this->userFavorite;

        $search["follow_id"] = $id;
        $search["type"] = 2;
        return $this->getSearchQuery($query, $search)->count();

        //$query = $query->select(\DB::raw('count(follow_id) as total'));
        //$query = $query->where('follow_id', $id);
        //$query = $query->where('type', "2");

        //return $query->get();
    }

    //お気に入り合計
    public function getfavoriteSum($id){

        $query = $this->userFavorite;

        $search["user_id"] = $id;
        $search["type"] = 1;
        return $this->getSearchQuery($query, $search)->count();

        //$query = $query->select(\DB::raw('count(user_id) as total'));
        //$query = $query->where('user_id', $id);
        //$query = $query->where('type', "1");

        //return $query->get();

    }



}
