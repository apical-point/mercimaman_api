<?php namespace App\Repositories\Eloquent;


use App\Repositories\Eloquent\Models\Growth;
use App\Repositories\Eloquent\Models\GrowthAge;
use App\Repositories\Eloquent\Models\UserGrowth;
use Illuminate\Support\Facades\Log;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\GrowthRepositoryInterface;

class GrowthRepository extends BaseEloquent implements GrowthRepositoryInterface
{


    public function __construct(
        Growth $growth,
        GrowthAge $growthAge,
        UserGrowth $userGrowth
        ){
        parent::__construct();
        $this->growth = $growth;
        $this->growthAge = $growthAge;
        $this->userGrowth = $userGrowth;

    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('age_no', $keys)) if($search['age_no']!=NULL) $query = $query->where('age_no', $search['age_no']);
        if(in_array('growth_type', $keys)) if($search['growth_type']!=NULL) $query = $query->where('growth_type', $search['growth_type']);
        if(in_array('detail', $keys)) if($search['detail']!=NULL) $query = $query->where('detail', 'LIKE', "%".$search['detail']."%");
        if(in_array('view_flg', $keys)) if($search['view_flg']!=NULL) $query = $query->where('view_flg', $search['view_flg']);


        // -------------------------------- 並び替え --------------------------------
          $query = $query->orderBy("age_no","ASC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->growth;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->growth->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->growth->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->growth->where($where);
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
        return $this->growth->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------


//------------ 各年齢で出来る事リスト用

    // 検索
    public function getSearchQueryGrowthAge($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('age_no', $keys)) if($search['age_no']!=NULL) $query = $query->where('age_no', $search['age_no']);
        if(in_array('name', $keys)) if($search['name']!=NULL) $query = $query->where('name', 'LIKE', "%".$search['name']."%");


        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("age_no","ASC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getListGrowthAge($search=[])
    {
        // query化
        $query = $this->growthAge;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQueryGrowthAge($query, $search)->paginate($perPage) : $this->getSearchQueryGrowthAge($query, $search)->get();
    }

    // 新規作成
    public function createItemGrowthAge(array $data)
    {
        return $this->growthAge->create($data);
    }

    // 1件数の取得
    public function getItemGrowthAge(array $where)
    {
        return $this->growthAge->where($where)->first();
    }

    // 更新
    public function updateItemGrowthAge(array $where, array $data)
    {
        if(empty($item=$this->getItemGrowthAge($where)))  return false;

        return $item->fill($data)->save();
    }

    // 削除
    public function deleteItemGrowthAge(array $where)
    {
        if(empty($item=$this->getItemGrowthAge($where)))  return false;
        return $item->delete();
    }

    //-------------自分の子供の出来たこと記録---------

    // 検索
    public function getSearchQueryGrowthUser($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('growth_age_id', $keys)) if($search['growth_age_id']!=NULL) $query = $query->where('growth_age_id', $search['growth_age_id']);
        if(in_array('name', $keys)) if($search['name']!=NULL) $query = $query->where('name', $search['name']);
        if(in_array('birth', $keys)) if($search['birth']!=NULL) $query = $query->where('birth', $search['birth']);


        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("age_date","ASC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getListGrowthUser($search=[])
    {
        // query化
        $query = $this->userGrowth;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQueryGrowthUser($query, $search)->paginate($perPage) : $this->getSearchQueryGrowthUser($query, $search)->get();
    }

    // 新規作成
    public function createItemGrowthUser(array $data)
    {
        return $this->userGrowth->create($data);
    }

    // 1件数の取得
    public function getItemGrowthUser(array $where)
    {

        return $this->userGrowth->where($where)->first();
    }

    // 更新
    public function updateItemGrowthUser(array $where, array $data)
    {
        if(empty($item=$this->getItemGrowthUser($where)))  return false;

        return $item->fill($data)->save();
    }

    // 削除
    public function deleteItemGrowthUser(array $where)
    {
        if(empty($item=$this->getItemGrowthUser($where)))  return false;
        return $item->delete();
    }

    public function updateOrCreateGrowthUser(array $where, array $data)
    {

        return $this->userGrowth->updateOrCreate($where,$data);
    }


    // 画像データの新規作成
    public function createImageByGrowth($userGrowthId, $imageData)
    {
        // 取得
        if(!$row = $this->getItemGrowthUser(['id'=>$userGrowthId])) return false;

        // 画像作成
        $where["up_file_able_id"] = $userGrowthId;
        return $row->images()->updateOrCreate($where, $imageData);
    }

}
