<?php namespace App\Repositories\Eloquent;

use App\Repositories\OrderDetailRepositoryInterface;
use App\Repositories\Eloquent\Models\OrderDetail;
use App\Repositories\Eloquent\Models\Order;

class OrderDetailRepository extends BaseEloquent implements OrderDetailRepositoryInterface
{
    protected $orderDetail;

    public function __construct(
        OrderDetail $orderDetail,
        Order $order
    ){
        parent::__construct();
        $this->orderDetail = $orderDetail;
        $this->order = $order;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(!empty($search['year']) && empty($search['month'])) {

            $query = $query->where('created_at', '>=', $search['year']);

        } elseif(!empty($search['year']) && !empty($search['month'])) {

            $query = $query->where('created_at', '>=', $search['year'].'-'.$search['month']);
        }

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
        $query = $this->orderDetail;

        // ページ
        $perPage = $this->getPerPage($search);

        // 取得するカラムの決定
        $columns = $this->orderDetail->getColumns($this->getColumns($search));

        // カラム指定があるかないかで分ける
        if($columns) return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage, $columns) : $this->getSearchQuery($query, $search)->get($columns);
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->orderDetail->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->orderDetail->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->orderDetail->where($where);
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
        return $this->orderDetail->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }


    // -------------------------------------  -------------------------------------
    // 新規作成
    public function createItemByOrderId($orderId, $data)
    {
        // オーダー取得
        $orderRow = $this->order->where('id', $orderId)->first();

        // 作成
        return $orderRow->orderDetails()->create($data);
    }

    //取引の評価
    public function getEvaluation($id, $type){

        $query = $this->orderDetail;

        $query = $query->select(\DB::raw('count(seller_user_id) as total'));
        $query = $query->where('seller_user_id', $id);
        $query = $query->where('seller_evaluation', $type);

        return $query->get();

    }

}
