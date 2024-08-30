<?php namespace App\Repositories\Eloquent;

use App\Repositories\OrderRepositoryInterface;
use App\Repositories\Eloquent\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;


class OrderRepository extends BaseEloquent implements OrderRepositoryInterface
{
    protected $order;

    public function __construct(
        Order $order
    ){
        parent::__construct();
        $this->order = $order;
    }

    // ------------------------------------- basic -------------------------------------
    // 新規作成
    public function createItem(array $data)
    {
        return $this->order->create($data);
    }

    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('orders.id', $search['id']);
        if(in_array('product_name', $keys)) if($search['product_name']!=NULL) $query = $query->where('product_name', 'LIKE', "%".$search['product_name']."%");
        if(in_array('seller_user_id', $keys)) if($search['seller_user_id']!=NULL) $query = $query->where('seller_user_id', $search['seller_user_id']);
        if(in_array('buyer_user_id', $keys)) if($search['buyer_user_id']!=NULL) $query = $query->where('buyer_user_id', $search['buyer_user_id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('buyer_user_id', $search['user_id'])->orWhere('seller_user_id', $search['user_id']);
        if(in_array('like_user_name', $keys)) if($search['like_user_name']!=NULL) $query = $query->where('buyer_name', 'LIKE', "%".$search['like_user_name']."%");
        if(in_array('type', $keys)) if($search['type']!=NULL) $query = $query->where('orders.type', $search['type']);
        if(in_array('auto_flg', $keys)) if($search['auto_flg']!=NULL) $query = $query->where('auto_flg', $search['auto_flg']);
        if(in_array('status', $keys)) if($search['status']!=NULL){
            if ( is_array($search['status'])){
                $query = $query->where(function($query) use($search){
                    if (count($search['status']) == 2){
                        $query->where('orders.status', $search['status'][0])
                            ->orWhere('orders.status', $search['status'][1]);
                    }else{
                        $query->where('orders.status', $search['status'][0])
                            ->orWhere('orders.status', $search['status'][1])
                            ->orWhere('orders.status', $search['status'][2]);
                    }
                });
            }else{
                $query = $query->where('orders.status', $search['status']);
            }
        }
        if(in_array('order_date_from', $keys)) if($search['order_date_from']!=NULL) $query = $query->where('order_date','>=' , $search['order_date_from']);
        if(in_array('order_date_to', $keys)) if($search['order_date_to']!=NULL) $query = $query->where('order_date','<=' , $search['order_date_to']);
        if(in_array('sellkeep_date', $keys)) if($search['sellkeep_date']!=NULL) $query = $query->where('sellkeep_date', '<' ,$search['sellkeep_date']);

        if(in_array('order_year', $keys)) if($search['order_year']!=NULL) $query = $query->whereYear('order_fix_date', '=', $search['order_year']);
        if(in_array('order_month', $keys)) if($search['order_month']!=NULL) $query = $query->whereMonth('order_fix_date', '=', $search['order_month']);
        if(in_array('order_day', $keys)) if($search['order_day']!=NULL) $query = $query->whereDay('order_fix_date', $search['order_day']);

        //配送方法　
        if(in_array('shipping_method', $keys)) if($search['shipping_method']!=NULL) $query = $query->where('shipping_method', $search['shipping_method']);

        //匿名配送　予約番号
        if(in_array('yamato_reserve_no', $keys)) if($search['yamato_reserve_no']!=NULL) $query = $query->where('yamato_reserve_no', 'LIKE', "%".$search['yamato_reserve_no']."%");


        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            $query = $query->orderBy($search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->order;
        $query = $query->select('orders.*', 'products.id as product_id', 'products.product_name','products.price', 'products.auto_flg','products.shipping_method');
        $query = $query->leftJoin('products', 'orders.product_id', '=', 'products.id');

        // ページ
        $perPage = $this->getPerPage($search);

        // 取得するカラムの決定
        $columns = $this->order->getColumns($this->getColumns($search));

        // カラム指定があるかないかで分ける プランの指定がある場合は詳細テーブルもjoin
        if($columns) return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage, $columns) : $this->getSearchQuery($query, $search)->get($columns);
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();

    }

    // 売上リスト取得
    public function getSalesGroupList($search=[])
    {
        // query化
        $query = $this->order;

        // ページ
        $perPage = $this->getPerPage($search);

        $groupQuery = $this->getSalesGroupSearchQuery($query, $search)->get();
        return new LengthAwarePaginator($groupQuery, count($groupQuery), $perPage);
    }

    // 売上リスト取得
    public function getSalesGroupSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // query化
        $query = $query->select(
                    \DB::raw('COUNT(id) AS count'),
                    \DB::raw('SUM(sales_price) AS sum_payment_price'),
                    \DB::raw('SUM(system_charge) AS sum_system_charge'),
                    \DB::raw('SUM(total_price) AS sum_total_price'),
                    \DB::raw('YEAR(order_date) as order_year'),
                    \DB::raw('MONTH(order_date) as order_month'));
        $query = $query->groupBy(\DB::raw('order_year'), \DB::raw('order_month'));
        $query->where('status', "<", "9");

        if(in_array('order_year', $keys)) if($search['order_year']!=NULL) $query = $query->whereYear('order_date', '=', $search['order_year']);
        if(in_array('order_month', $keys)) if($search['order_month']!=NULL) $query = $query->whereMonth('order_date', '=', $search['order_month']);

        $query = $query->orderBy(\DB::raw('YEAR(order_date)'), 'desc');
        $query = $query->orderBy(\DB::raw('MONTH(order_date)'), 'desc');

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));

        return $query;

    }

    // 銀行申請
    public function getPayment()
    {
        // query化
        $query = $this->order;
        $query = $query->select('seller_user_id', 'payment_request_date' ,'seller_name',
                    \DB::raw('COUNT(seller_user_id) AS count'),
                    \DB::raw('SUM(payment_price) AS sum_payment_price'),
                    \DB::raw('SUM(system_charge) AS sum_system_charge'),
                    \DB::raw('SUM(total_price) AS sum_total_price'),
                    \DB::raw('SUM(system_charge) as summed_system_charge'));
        $query = $query->groupBy('seller_user_id', 'payment_request_date', 'seller_name');
        $query = $query->where(function($query) {
            $query->where('status', "5")
                ->orWhere('status', "6");
            });

        $query = $query->orderBy('payment_request_date', 'asc');
        $data = $query->get();

        return $data;
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->order->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        if (array_values($where) === $where) {
            $query = $this->order->where($where);
        }else{
            // 連想配列の場合、whereIn利用を想定
            $query = null;
            $where2 = [];
            $wherein = [];
            foreach ($where as $key => $value) {
                if (is_array($value)){ // 値が配列であればwhereIn対象
                    $wherein[$key] = $value;
                }else{
                    $where2[$key] = $value;
                }
            }
            if ($where2){
                $query = $this->order->where($where2);
            }
            if ($wherein){
                foreach ($wherein as $key => $value) {
                    if ($query){
                        $query->whereIn($key, $value);
                    }else{
                        $query = $this->order->whereIn($key, $value);
                    }
                }
            }
        }
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
        return $this->order->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

    // -------------------------------------  -------------------------------------
    // マイル履歴を新規作成
    public function createMileHistoryByOrderId($orderId, $data)
    {
        // マイルメッセージ取得
        if(!$row = $this->order->where(['id'=>$orderId])->first()) false;

        // 作成
        return $row->mile()->create($data);
    }

    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->product->whereIn('id', $ids)->get();
    }

}
