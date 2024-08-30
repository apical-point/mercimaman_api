<?php namespace App\Repositories\Eloquent;


use App\Repositories\Eloquent\Models\TopSlideData;


use Illuminate\Support\Facades\Log;
use App\Repositories\TopSlideDataRepositoryInterface;

class TopSlideDataRepository extends BaseEloquent implements TopSlideDataRepositoryInterface
{


    public function __construct(
        TopSlideData $topSlideData
        ){
        parent::__construct();
        $this->topSlideData = $topSlideData;


    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('view_flg', $keys)) if($search['view_flg']!=NULL) $query = $query->where('view_flg', $search['view_flg']);
        if(in_array('eventpage_flg', $keys)) if($search['eventpage_flg']!=NULL) $query = $query->where('eventpage_flg', $search['eventpage_flg']);
        if(in_array('v_order', $keys)) if($search['v_order']!=NULL) $query = $query->where('v_order', $search['v_order']);

        // -------------------------------- 並び替え --------------------------------
        //$query = $query->orderBy("id","ASC");
        $query = $query->orderBy("v_order","ASC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->topSlideData;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->topSlideData->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->topSlideData->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->topSlideData->where($where);
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
        return $this->topSlideData->where($where)->delete();
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

/*    // $flg:1(UP)　2(DOWN)
    public function getvOrderData($v_order, $flg){

	    if($flg == 1){
	        $ord = "asc";
	        $togo = ">";
	    }
	    else{
	        $ord = "desc";
	        $togo = "<";
	    }

        $query = $this->topSlideData;

        $query = $query->select('top_slide_datas.*');
        $query = $query->where('v_order', $togo, $v_order);
        $query = $query->orderBy("v_order",$ord);

        return $query->get();

	    $value[] = $v_order;

	    $stmt = $this->db->prepare($sql);
	    $stmt->execute($value);
	    $rs = $stmt->fetchAll();
	    $Arr=array();
	    for($i=0;$i<count($rs);$i++){
	        foreach ($rs[$i] as $key => $val){
	            $data{$key} = rtrim($val);
	        }
	        array_push($Arr,$data);
	    }
	    return $Arr[0];
	}*/
}
