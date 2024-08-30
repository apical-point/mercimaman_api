<?php namespace App\Repositories\Eloquent;

use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\Eloquent\Models\UserDetail;
use App\Repositories\Eloquent\Models\User;

class UserDetailRepository extends BaseEloquent implements UserDetailRepositoryInterface
{
    protected $userDetail;

    public function __construct(
        UserDetail $userDetail,
        User $user
        ){
        parent::__construct();
        $this->userDetail = $userDetail;
        $this->user = $user;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('email', $keys)) if($search['email']!=NULL) $query = $query->where('email', 'LIKE', "%".$search['email']."%");

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            // $query = $query->orderBy(, $search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->user;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->userDetail->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->userDetail->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->userDetail->where($where);
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
        return $this->userDetail->where($where)->delete();
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
    public function createUserDetailByUserId($userId, array $data)
    {
        // ユーザーの取得
        if(empty($item=$this->user->where(['id'=>$userId])->first()))  return false;

        // ユーザー詳細の作成
        return $item->userDetail()->create($data);
    }

    // useridをキーにユーザー詳細を新規更新
    public function updateUserDetailByUserId($userId, array $data)
    {
        // 詳細の取得
        if(empty($detail=$this->getItem(['user_id'=>$userId])))  return false;

        // 詳細の更新
        return $this->updateItem(['id'=>$detail->id], $data);
    }




    // ------------------------------------- / -------------------------------------

}
