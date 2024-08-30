<?php namespace App\Repositories\Eloquent;

use App\Repositories\AdvertisementRepositoryInterface;
use App\Repositories\Eloquent\Models\Advertisement;

class AdvertisementRepository extends BaseEloquent implements AdvertisementRepositoryInterface
{
    protected $advertisement;

    public function __construct(
        Advertisement $advertisement
        ){
        parent::__construct();
        $this->advertisement = $advertisement;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('open_flg', $keys)) if($search['open_flg']!=NULL) $query = $query->where('open_flg', $search['open_flg']);
        if(in_array('company', $keys)) if($search['company']!=NULL) $query = $query->where('company', 'LIKE', '%'.$search['company'].'%');
        if(in_array('advertisement_name', $keys)) if($search['advertisement_name']!=NULL) $query = $query->where('advertisement_name', 'LIKE', '%'.$search['advertisement_name'].'%');
        if(in_array('type', $keys)) if($search['type']!=NULL) $query = $query->where('type', $search['type']);
        if(in_array('term_from', $keys)) if($search['term_from']!=NULL) $query = $query->where('term', '>=', $search['term_from']);
        if(in_array('term_to', $keys)) if($search['term_to']!=NULL) $query = $query->where('term', '<=', $search['term_to']);
        if(in_array('term', $keys)) if($search['term']!=NULL) $query = $query->where('term', $search['term']);

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
        // query化
        $query = $this->advertisement;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->advertisement->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->advertisement->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->advertisement->where($where);
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
        return $this->advertisement->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------

    // 画像データの新規作成or更新
    public function updateOrCreateImageData($Id, $where, $imageData)
    {
        // 商品取得
        if(!$contentRow = $this->getItem(['id'=>$Id])) return false;

        // 画像作成
        return $contentRow->images()->updateOrCreate($where, $imageData);
    }


}
