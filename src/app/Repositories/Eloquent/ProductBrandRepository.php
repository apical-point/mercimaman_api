<?php namespace App\Repositories\Eloquent;

use App\Repositories\ProductBrandRepositoryInterface;
use App\Repositories\Eloquent\Models\ProductBrand;

class ProductBrandRepository extends BaseEloquent implements ProductBrandRepositoryInterface
{
    protected $productBrand;

    public function __construct(
        ProductBrand $productBrand
    ){
        parent::__construct();
        $this->productBrand = $productBrand;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->productBrand->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

         if(in_array('brand', $keys)) if($search['brand']!=NULL) $query = $query->where('brand',"LIKE",  "%".$search['brand']."%");

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
        $query = $this->productBrand;

        // ページ
        $perPage = $this->getPerPage($search);

        // 取得するカラムの決定
        $columns = $this->productBrand->getColumns($this->getColumns($search));

        // カラム指定があるかないかで分ける
        if($columns) return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage, $columns) : $this->getSearchQuery($query, $search)->get($columns);
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();

    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->productBrand->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->productBrand->where($where);
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
        return $this->productBrand->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

    // -------------------------------------  -------------------------------------
    // 特定の親の子供の個数を返す
    public function getCountNumByParentid($parentid)
    {
        return $this->productBrand->where('parentid', $parentid)->get()->count();
    }

}
