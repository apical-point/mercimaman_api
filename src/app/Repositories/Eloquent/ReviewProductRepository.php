<?php namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\Models\ReviewProduct;
use App\Repositories\Eloquent\Models\Review;

class ReviewProductRepository extends BaseEloquent
{
    protected $ReviewProductRepository;

    public function __construct(
        ReviewProduct $ReviewProduct,
        Review $Review
    ){
        parent::__construct();
        $this->ReviewProduct = $ReviewProduct;
        $this->Review = $Review;
    }

    // ------------------------------------- basic -------------------------------------

    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        $query = $query->select('review_product.*');

        // -------------------------------- 検索 --------------------------------

        //キーワード(商品名での検索)
        if(in_array('like_name', $keys) && $search['like_name']!=null && $search['keyword_type'] == 'product_name') {
            $name = $search['like_name'];
            $query = $query->where('product_name', 'LIKE', "%$name%");
        }

        //キーワード(口コミでの検索)
        if(in_array('like_name', $keys) && $search['like_name']!=null && $search['keyword_type'] == 'review'){
            $name = $search['like_name'];
            $query->leftJoin('review', 'review.product_id', '=', 'review_product.id')->where('review.review', 'LIKE', "%$name%");
        }

        if(in_array('category_id', $keys) && $search['category_id']!=NULL) $query = $query->where('category_id', $search['category_id']);

        if(in_array('price_range', $keys) && $search['price_range']!=NULL) $query = $query->where('price_range', $search['price_range']);

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }
        \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function searchReviewProduct($search=[])
    {
        // query化
        $query = $this->ReviewProduct;

        return $this->getSearchQuery($query, $search);
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->ReviewProduct->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->ReviewProduct->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->ReviewProduct->where($where);
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

    // 画像データの新規作成or更新
    public function updateOrCreateImageData($Id, $where, $imageData)
    {
        // 商品取得
        if(!$reviewProductRow = $this->getItem(['id'=>$Id])) return false;

        // 画像作成
        return $reviewProductRow->images()->updateOrCreate($where, $imageData);
    }

    // ------------------------------------- 口コミ関連 -------------------------------------

    // 口コミ削除
    public function deleteReview(array $where)
    {
        if(empty($items=$this->searchReview($where)))  return false;

        foreach($items as $item){

            if(empty($item->delete()))  return false;
        }

        return true;
    }

    // 口コミの取得
    public function getReviewById($where)
    {
        // query化
        $query = $this->Review;

        if($where['id']!=NULL) $query = $query->where('id', $where['id']);

        return $query->first();
    }

    // 口コミの取得
    public function searchReview($where)
    {
        $keys = array_keys($where);

        $query = $this->Review;

        $perPage = $this->getPerPage($where);

        if(in_array('id', $keys) && $where['id']!=NULL) $query = $query->where('id', $where['id']);

        if(in_array('product_id', $keys) && $where['product_id']!=NULL) $query = $query->where('product_id', $where['product_id']);

        if(in_array('block_users', $keys) && $where['block_users']!=NULL) $query = $query->whereNotIn('user_id', $where['block_users']); //表示しない

        if(in_array('order_by_raw', $keys) && $where['order_by_raw']!=NULL) $query = $query->orderByRaw($where['order_by_raw']);

        if(in_array('user_id', $keys) && $where['user_id']!=NULL) $query = $query->where('user_id', $where['user_id']);

        return $perPage!==-1 ? $query->paginate($perPage) : $query->get();

    }

    // 口コミ作成
    public function postReview(array $data)
    {
        return $this->Review->create($data);
    }

}
