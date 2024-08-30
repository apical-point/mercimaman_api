<?php namespace App\Repositories\Eloquent;

use App\Repositories\ProductRepositoryInterface;
use App\Repositories\Eloquent\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductRepository extends BaseEloquent implements ProductRepositoryInterface
{
    protected $product;

    public function __construct(
        Product $product
    ){
        parent::__construct();
        $this->product = $product;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        //ステータス
        if(in_array('status', $keys)) if($search['status']!=NULL){
            if ( is_array($search['status'])){
                    $query = $query->where(function($query) use($search){
                        if (count($search['status']) == 2){
                            $query->where('products.status', $search['status'][0])
                                ->orWhere('products.status', $search['status'][1]);
                        }else{
                            $query->where('products.status', $search['status'][0])
                                ->orWhere('products.status', $search['status'][1])
                                ->orWhere('products.status', $search['status'][2]);
                        }
                    });
            }else{
                $query = $query->where('products.status', $search['status']);
            }
        }

        //キーワード
        if(in_array('like_name', $keys) && $search['like_name']!=NULL) {
            $name = $search['like_name'];
            $query = $query->where('product_name', 'LIKE', "%$name%")->orwhere('detail', 'LIKE', "%$name%");
        }

        if(in_array('taste', $keys)) if($search['taste']!=NULL) $query = $query->where('products.taste', $search['taste']);
        if(in_array('brand', $keys)) if($search['brand']!=NULL) $query = $query->where('products.brand', 'LIKE', "%".$search['brand']."%");
        if(in_array('category', $keys)) if($search['category']!=NULL) $query = $query->where('products.category', $search['category']);
        if(in_array('product_category1_id', $keys)) if($search['product_category1_id']!=NULL) $query = $query->where('products.product_category1_id', $search['product_category1_id']);
        if(in_array('product_category2_id', $keys)) if($search['product_category2_id']!=NULL) $query = $query->where('products.product_category2_id', $search['product_category2_id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('products.user_id', $search['user_id']);
        if(in_array('open_flg', $keys)) if($search['open_flg']!=NULL) $query = $query->where('products.open_flg', $search['open_flg']);
        if(in_array('size', $keys)) if($search['size']!=NULL) $query = $query->where('products.size', $search['size']);

        if(in_array('pricefrom', $keys)) if($search['pricefrom']!=NULL) $query = $query->where('products.price','>=' , $search['pricefrom']);
        if(in_array('priceto', $keys)) if($search['priceto']!=NULL) $query = $query->where('products.price','<=' , $search['priceto']);

        if(in_array('condition', $keys)) if($search['condition']!=NULL) $query = $query->where('products.condition',$search['condition']);
        if(in_array('shipping_charges', $keys)) if($search['shipping_charges']!=NULL) $query = $query->where('products.shipping_charges', $search['shipping_charges']);

        if(in_array('auto_flg', $keys)) if($search['auto_flg']!=NULL) $query = $query->where('products.auto_flg',$search['auto_flg']);
        if(in_array('inappropriate', $keys)) if($search['inappropriate']!=NULL) $query = $query->where('products.inappropriate',$search['inappropriate']);
        //if(in_array('product_array', $keys)) if($search['product_array']!=NULL) $query = $query->whereIN('id',['63','43']);
        //表示しない
        if(in_array('non_id', $keys)) if($search['non_id']!=NULL) $query = $query->where('products.id', '!=', $search['non_id']);
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('products.id', '=', $search['id']);

        if(in_array('buyer_user_id', $keys)) if($search['buyer_user_id']!=NULL) $query = $query->where('orders.buyer_user_id', $search['buyer_user_id']);


        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            // $query = $query->orderBy(, $search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }
        \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        $query = $this->product;

        if (isset($search['favorite_id'])){
            $user_id = $search['favorite_id'];
            $query = $query->select('products.*',\DB::Raw('IFNULL( `user_favorites`.`product_id` , 0 ) as favorite'))
                            ->leftJoin('user_favorites', function ($query) use($user_id) {
                                    $query->on('user_favorites.product_id', '=', 'products.id')
                                                ->where('user_favorites.user_id', '=', $user_id)->where('user_favorites.type', '=', '1');});
        }else{
            $query = $this->product;
            $query = $query->select('products.*', 'orders.id as order_id', 'orders.buyer_user_id','orders.buyer_name','orders.buyer_prefecture_id','orders.buyer_zip',
                                        'orders.buyer_address1','orders.buyer_address2','orders.buyer_building','orders.order_date','orders.tradeend_date',
                                        'order_details.seller_evaluation','order_details.seller_comment','order_details.buyer_evaluation','order_details.buyer_comment');
            $query = $query->leftJoin('orders', function ($query) {
                        $query->on('orders.product_id', '=', 'products.id')->where('orders.status', '!=', "9");});
            $query = $query->leftJoin('order_details', 'order_details.order_id', '=', 'orders.id');
        }

        // ページ
        $perPage = $this->getPerPage($search);

        // 取得するカラムの決定
        $columns = $this->product->getColumns($this->getColumns($search));

        // カラム指定があるかないかで分ける
        if($columns) return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage, $columns) : $this->getSearchQuery($query, $search)->get($columns);
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->product->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->product->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->product->where($where);
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
        return $this->product->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;

        return $item->delete();
    }

    // ------------------------------------- その他関数 -------------------------------------
    // 画像データの新規作成
    public function createImageByProductIdAndImageData($productId, $imageData)
    {
        // 商品取得
        if(!$productRow = $this->getItem(['id'=>$productId])) return false;

        // 画像作成
        return $productRow->images()->create($imageData);
    }

    // 画像データの新規作成or更新
    public function updateOrCreateImageByProductIdAndImageData($productId, $where, $imageData)
    {
        // 商品取得
        if(!$productRow = $this->getItem(['id'=>$productId])) return false;

        // 画像作成
        return $productRow->images()->updateOrCreate($where, $imageData);
    }

    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->product->whereIn('id', $ids)->get();
    }


}
