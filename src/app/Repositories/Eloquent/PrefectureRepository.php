<?php namespace App\Repositories\Eloquent;

use App\Repositories\PrefectureRepositoryInterface;
use App\Repositories\Eloquent\Models\Prefecture;

class PrefectureRepository extends BaseEloquent implements PrefectureRepositoryInterface
{
    protected $prefecture;

    public function __construct(
        Prefecture $prefecture
    ){
        parent::__construct();
        $this->prefecture = $prefecture;
    }

    // ------------------------------------- basic -------------------------------------
    // 新規作成
    public function createItem(array $data)
    {
        return $this->prefecture->create($data);
    }

    // 検索
    public function getSearchQuery($query, $search)
    {
        /* $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        // if(in_array('seller_user_id', $keys)) if($search['seller_user_id']!=NULL) $query = $query->where('seller_user_id', $search['seller_user_id']);
        // if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            // $query = $query->orderBy(, $search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        } */

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[]) {

        // query化
        $query = $this->prefecture;

        // ページ
        $perPage = $this->getPerPage($search);

        $return = $perPage !== -1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
        return $return;
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->prefecture->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->prefecture->where($where);
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
        return $this->prefecture->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;

        return $item->delete();
    }

    // ------------------------------------- その他関数 -------------------------------------
    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->prefecture->whereIn('id', $ids)->get();
    }


}
