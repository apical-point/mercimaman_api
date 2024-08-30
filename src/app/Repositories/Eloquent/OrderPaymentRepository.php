<?php namespace App\Repositories\Eloquent;

use App\Repositories\OrderPaymentRepositoryInterface;
use App\Repositories\Eloquent\Models\OrderPayment;

class OrderPaymentRepository extends BaseEloquent implements OrderPaymentRepositoryInterface
{
    protected $OrderPayment;

    public function __construct(
        OrderPayment $OrderPayment
    ){
        parent::__construct();
        $this->OrderPayment = $OrderPayment;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
         if(in_array('csv_flg', $keys)) if($search['csv_flg']!=NULL) $query = $query->where('csv_flg', $search['csv_flg']);
         if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('order_payments.user_id', $search['user_id']);
         if(in_array('banktransfer_date_from', $keys)) if($search['banktransfer_date_from']!=NULL) $query = $query->where('banktransfer_date',  '>=', $search['banktransfer_date_from']);
         if(in_array('banktransfer_date_to', $keys)) if($search['banktransfer_date_to']!=NULL) $query = $query->where('banktransfer_date',  '<=', $search['banktransfer_date_to']);
         if(in_array('like_name', $keys)) if($search['like_name']!=NULL) $query = $query->where(\DB::raw('CONCAT(last_name, first_name)'), 'LIKE', "%".$search['like_name']."%");

         if(in_array('payment_flg', $keys)) if($search['payment_flg']!=NULL) $query = $query->where('payment_flg', $search['payment_flg']);


         \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);

        }else {
            $query = $query->orderByRaw('id desc');
        }

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->OrderPayment;
        $query = $query->select('order_payments.*', 'user_details.last_name', 'user_details.first_name');
        $query = $query->leftJoin('user_details', 'user_details.user_id', '=', 'order_payments.user_id');

        // ページ
        $perPage = $this->getPerPage($search);

        // 取得するカラムの決定
        $columns = $this->OrderPayment->getColumns($this->getColumns($search));

        // カラム指定があるかないかで分ける
        if($columns) return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage, $columns) : $this->getSearchQuery($query, $search)->get($columns);
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->OrderPayment->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->OrderPayment->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->OrderPayment->where($where);
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
        return $this->OrderPayment->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

}
