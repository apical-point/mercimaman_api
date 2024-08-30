<?php namespace App\Repositories\Eloquent;

use App\Repositories\NewsRepositoryInterface;
use App\Repositories\Eloquent\Models\News;
use Illuminate\Support\Facades\Log;

class NewsRepository extends BaseEloquent implements NewsRepositoryInterface
{
    protected $news;

    public function __construct(
        News $news
        ){
        parent::__construct();
        $this->news = $news;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('public_id', $keys)) if($search['public_id']!=NULL) $query = $query->where('public_id', $search['public_id']);
        if(in_array('news_flg', $keys)) if($search['news_flg']!=NULL) $query = $query->where('news_flg', $search['news_flg']);
        if(in_array('open_flg', $keys)) if($search['open_flg']!=NULL) $query = $query->where('open_flg', $search['open_flg']);
        if(in_array('status', $keys)) if($search['status']!=NULL) $query = $query->where('status', $search['status']);
        if(in_array('open_date', $keys)) if($search['open_date']!=NULL) $query = $query->where('open_date', '>=' , $search['open_date']);
        if(in_array('open_date_to', $keys)) if($search['open_date_to']!=NULL) $query = $query->where('open_date', '<=' , $search['open_date_to']);
        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            // $query = $query->orderBy(, $search['order_by']);

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
        $query = $this->news;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->news->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->news->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->news->where($where);
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
        return $this->news->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------

    // 日付の削除
    public function datedelete($where)
    {
        return $this->news->where('open_date', '<=', $where)->delete();
    }

}
