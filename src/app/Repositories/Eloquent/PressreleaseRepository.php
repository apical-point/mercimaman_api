<?php namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\Models\Pressrelease;

class PressreleaseRepository extends BaseEloquent
{
    protected $PressreleaseRepository;

    public function __construct(
        Pressrelease $pressrelease
    ){
        parent::__construct();
        $this->pressrelease = $pressrelease;
    }

    // ------------------------------------- basic -------------------------------------
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        if(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }

        return $query;
    }


    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {

        // query化
        $query = $this->pressrelease;
        if(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }
        if(!empty($search['view_flg'])) {
            $query = $query->where('view_flg', $search['view_flg']);
        }

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->pressrelease->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->pressrelease->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->pressrelease->where($where);
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

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

}