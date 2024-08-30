<?php namespace App\Repositories\Eloquent;

use App\Repositories\PointRepositoryInterface;
use App\Repositories\Eloquent\Models\Point;
use Illuminate\Support\Facades\Log;

class PointRepository extends BaseEloquent implements PointRepositoryInterface
{
    protected $point;

    public function __construct(
        Point $point
    ){
        parent::__construct();
        $this->point = $point;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->point->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('point_type', $keys)) if($search['user_id']!=NULL) $query = $query->where('point_type', $search['point_type']);
        if(in_array('expiration_date', $keys)) if($search['expiration_date']!=NULL) $query = $query->where('expiration_date','>=' , $search['expiration_date']);
        if(in_array('point_date', $keys)) if($search['point_date']!=NULL) $query = $query->where('point_date', $search['point_date']);

        if(in_array('expiration_date_list', $keys)){
            if($search['expiration_date_list']!=NULL){
                $query = $query->where(function($query)use ($search) {
                    $query = $query->where('expiration_date','>=' , $search['expiration_date_list']);
                    $query = $query->orWhere('expiration_date',NULL);
                });
            }
        }



        if(!empty($search['order_by'])) {
            $query = "id asc";
        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }

        //\Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->point;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    //ユーザーの保持ポイント
    public function getUsersSum($id){

        //有効期限内のデータを取得
        $query = $this->point;

        $query = $query->select(\DB::raw('SUM(point - use_point) as total'));
        $query = $query->where('user_id', $id)->Where('use_flg', "0")->Where('expiration_date', ">=", date('Y-m-d'));
        $query = $query->where('point', ">",0);

        return $query->get();

    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->point->where($where)->first();
    }

    //
    public function getItems(array $where, $take=0, $orderByRaw='')
    {

        $query = $this->point->where($where);

        if($take)  $query->take($take);
        if($orderByRaw)  $query->orderByRaw($orderByRaw);
        \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));
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
        return $this->point->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // idで1件の取得
    public function getItemById($id)
    {
        return  $this->point->find($id);
    }

}
