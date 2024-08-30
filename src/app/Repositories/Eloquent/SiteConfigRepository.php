<?php namespace App\Repositories\Eloquent;

use App\Repositories\SiteConfigRepositoryInterface;
use App\Repositories\Eloquent\Models\SiteConfig;

class SiteConfigRepository extends BaseEloquent implements SiteConfigRepositoryInterface
{
    protected $siteConfig;

    public function __construct(
        SiteConfig $siteConfig
    ){
        parent::__construct();
        $this->siteConfig = $siteConfig;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('key_name', $keys)) if($search['key_name']!=NULL) $query = $query->where('key_name', 'LIKE', "%".$search['key_name']."%");
        if(in_array('sort_up', $keys)) if($search['sort_up']!=NULL) $query = $query->where('sort', '>=', $search['sort_up']);
        

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
        $query = $this->siteConfig;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->siteConfig->where($where)->first();
    }

    // // 1件数の取得
    // public function getItems(array $where, $take=0, $orderByRaw='')
    // {
    //     $query = $this->siteConfig->where($where);
    //     if($take)  $query->take($take);
    //     if($orderByRaw)  $query->orderByRaw($orderByRaw);

    //     return $query->get();
    // }

    // 更新
    public function updateItem(array $where, array $data)
    {
        if(empty($item=$this->getItem($where)))  return false;

        return $item->fill($data)->save();
    }


    // // 複数の削除
    // public function deleteItems(array $where)
    // {
    //     return $this->siteConfig->where($where)->delete();
    // }

    // // 削除
    // public function deleteItem(array $where)
    // {
    //     if(empty($item=$this->getItem($where)))  return false;
    //     return $item->delete();
    // }
    // ------------------------------------- /basic -------------------------------------

    // -------------------------------------  -------------------------------------
    // ------------------------------------- / -------------------------------------


}
