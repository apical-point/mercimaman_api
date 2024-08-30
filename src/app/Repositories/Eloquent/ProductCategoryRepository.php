<?php namespace App\Repositories\Eloquent;

use App\Repositories\ProductCategoryRepositoryInterface;
use App\Repositories\Eloquent\Models\ProductCategory;

class ProductCategoryRepository extends BaseEloquent implements ProductCategoryRepositoryInterface
{
    protected $productCategory;

    public function __construct(
        ProductCategory $productCategory
    ){
        parent::__construct();

        $this->productCategory = $productCategory;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('parentid', $keys)) if($search['parentid']!=NULL) $query = $query->where('parentid', $search['parentid']);
        if(in_array('cflag', $keys)) if($search['cflag']!=NULL) $query = $query->where('cflag', $search['cflag']);
        if(in_array('parentid', $keys)) if($search['parentid']!=NULL) $query = $query->where('parentid', $search['parentid']);
        if(in_array('product_category_name', $keys)) if($search['product_category_name']!=NULL) $query = $query->where('product_category_name', 'LIKE', "%".$search['product_category_name']."%");


        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderByRaw('v_order asc');

        \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->productCategory;

        // ページ
        $perPage = $this->getPerPage($search);

        // 取得するカラムの決定
        $columns = $this->productCategory->getColumns($this->getColumns($search));

        // カラム指定があるかないかで分ける
        if($columns) return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage, $columns) : $this->getSearchQuery($query, $search)->get($columns);
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->productCategory->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->productCategory->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->productCategory->where($where);
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
        return $this->productCategory->where($where)->delete();
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
        return $this->productCategory->where('parentid', $parentid)->get()->count();
    }


}
