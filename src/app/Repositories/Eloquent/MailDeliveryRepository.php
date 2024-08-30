<?php namespace App\Repositories\Eloquent;

use App\Repositories\MailDeliveryRepositoryInterface;
use App\Repositories\Eloquent\Models\MailDelivery;

class MailDeliveryRepository extends BaseEloquent implements MailDeliveryRepositoryInterface
{
    protected $mailDelivery;

    public function __construct(
        MailDelivery $mailDelivery
    ){
        parent::__construct();
        $this->mailDelivery = $mailDelivery;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->mailDelivery->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

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
        $query = $this->mailDelivery;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->mailDelivery->where($where)->first();
    }

    //
    public function getItems(array $where, $take=0, $orderByRaw='')
    {

        $query = $this->mailDelivery->where($where);

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
        return $this->mailDelivery->where($where)->delete();
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
        return  $this->mailDelivery->find($id);
    }
}
