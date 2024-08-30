<?php namespace App\Repositories\Eloquent;

use App\Repositories\InquiryRepositoryInterface;
use App\Repositories\Eloquent\Models\Inquiry;

class InquiryRepository extends BaseEloquent implements InquiryRepositoryInterface
{
    protected $inquiry;

    public function __construct(
        Inquiry $inquiry
    ){
        parent::__construct();
        $this->inquiry = $inquiry;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->inquiry->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
         if(in_array('inquiry_flg', $keys)) if($search['inquiry_flg']!=NULL) $query = $query->where('inquiry_flg', $search['inquiry_flg']);
         if(in_array('demand_flg', $keys)) if($search['demand_flg']!=NULL) $query = $query->where('demand_flg', $search['demand_flg']);

         \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

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
        $query = $this->inquiry;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->inquiry->where($where)->first();
    }

    //
    public function getItems(array $where, $take=0, $orderByRaw='')
    {

        $query = $this->inquiry->where($where);

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
        return $this->inquiry->where($where)->delete();
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
        return  $this->inquiry->find($id);
    }
}
