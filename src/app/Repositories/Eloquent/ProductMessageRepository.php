<?php namespace App\Repositories\Eloquent;

use App\Repositories\ProductMessageRepositoryInterface;
use App\Repositories\Eloquent\Models\ProductMessage;

class ProductMessageRepository extends BaseEloquent implements ProductMessageRepositoryInterface
{
    protected $productMessage;

    public function __construct(
        ProductMessage $productMessage
    ){
        parent::__construct();
        $this->productMessage = $productMessage;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->productMessage->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
         if(in_array('created_at', $keys)) if($search['created_at']!=NULL) $query = $query->where("created_at", $search['created_at']);
         if(in_array('product_id', $keys)) if($search['product_id']!=NULL) $query = $query->where("product_id" , $search['product_id']);
         if(in_array('open_flg', $keys)) if($search['open_flg']!=NULL) $query = $query->where("open_flg" , $search['open_flg']);
         if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where("product_messages.user_id" , $search['user_id']);

         \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        // -------------------------------- 並び替え --------------------------------
        if(in_array('order_by_raw', $keys)){
            $query = $query->orderByRaw($search["order_by_raw"]);
        }
        else{
            $query = $query->orderByRaw("id ASC");
        }

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->productMessage;
        $query = $query->select('product_messages.*','users.status','user_profiles.nickname','user_profiles.image_id');
        $query = $query->leftjoin("users",'users.id','=','product_messages.user_id');
        $query = $query->leftjoin("user_profiles",'user_profiles.user_id','=','product_messages.user_id');

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->productMessage->where($where)->first();
    }

    //
    public function getItems(array $where, $take=0, $orderByRaw='')
    {

        $query = $this->productMessage->where($where);

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
        return $this->productMessage->where($where)->delete();
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
        return  $this->productMessage->find($id);
    }
}
