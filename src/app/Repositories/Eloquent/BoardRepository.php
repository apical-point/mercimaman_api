<?php namespace App\Repositories\Eloquent;


use App\Repositories\Eloquent\Models\BoardRequest;
use App\Repositories\Eloquent\Models\BoardExperience;
use App\Repositories\Eloquent\Models\BoardExperienceUser;

use Illuminate\Support\Facades\Log;
use App\Repositories\BoardRepositoryInterface;

class BoardRepository extends BaseEloquent implements BoardRepositoryInterface
{


    public function __construct(
        BoardRequest $boardRequest,
        BoardExperience $boardExperience,
        BoardExperienceUser $boardExperienceUser
        ){
        parent::__construct();
        $this->boardRequest = $boardRequest;
        $this->boardExperience = $boardExperience;
        $this->boardExperienceUser = $boardExperienceUser;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('parent_id', $keys)) if($search['parent_id']!=NULL) $query = $query->where('parent_id', $search['parent_id']);
        if(in_array('exp_flg', $keys)) if($search['exp_flg']!=NULL) $query = $query->where('exp_flg', $search['exp_flg']);
        if(in_array('detail', $keys)) if($search['detail']!=NULL) $query = $query->where('detail', 'LIKE', "%".$search['detail']."%");
        if(in_array('block_users', $keys)) if($search['block_users']!=NULL) $query = $query->whereNotIn('user_id', $search['block_users']); //表示しない

        // -------------------------------- 並び替え --------------------------------
          $query = $query->orderBy("id","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->boardRequest;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->boardRequest->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->boardRequest->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->boardRequest->where($where);
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
        return $this->boardRequest->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------


    //======= 体験記 掲示板 =================================

    // 新規作成
    public function createItemExp(array $data)
    {
        return $this->boardExperience->create($data);
    }


    // 1件数の取得
    public function getItemExp(array $where)
    {
        return $this->boardExperience->where($where)->first();
    }

    // 更新
    public function updateItemExp(array $where, array $data)
    {
        if(empty($item=$this->getItemExp($where)))  return false;

        return $item->fill($data)->save();
    }

    /*
     * いいね、や参考になった等の人数更新処理
     *
     * $data  は　以下のいずれかが引数となってくる。
     *   $data["check1"]=1;
     *   $data["check2"]=2;
     *   $data["check3"]=3;
     *   $data["check4"]=4;
     *
     */
    public function updateExpIncrement($where, array $data)
    {
        if(empty($item=$this->getItemExp($where)))  return false;
        $k = key($data);

        return $item->increment($k, 1);

    }


    public function getSearchQueryExp($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('exp_flg', $keys)) if($search['exp_flg']!=NULL) $query = $query->where('exp_flg', $search['exp_flg']);
        if(in_array('parent_id', $keys)) if($search['parent_id']!=NULL) $query = $query->where('parent_id', $search['parent_id']);
        if(in_array('block_users', $keys)) if($search['block_users']!=NULL) $query = $query->whereNotIn('user_id', $search['block_users']); //表示しない
        if(in_array('detail', $keys)){
            if($search['detail']!=NULL && $search['exp_flg'] == 1){
                $query = $query->where('detail', 'LIKE', "%".$search['detail']."%");
            }
            elseif($search['detail']!=NULL && $search['exp_flg'] == 2){
                $query = $query->where(function($query) use($search){
                    $query->Where('title', 'LIKE', '%'.$search['detail'].'%')
                    ->orwhere('detail', 'LIKE', '%'.$search['detail'].'%');
                });
/*
                $query = $query->where(function($query) use($search){
                    $query->Where('title', 'LIKE', '%'.$search['detail'].'%');
                })->orwhere(function($query) use($search){
                    $query->Where('detail', 'LIKE', '%'.$search['detail'].'%');
                });
*/
            }
        }

        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("id","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getListExp($search=[])
    {
        // query化
        $query = $this->boardExperience;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQueryExp($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    //条件に対してのカウント数を取得
    public function getCountData($search=[])
    {
        // query化
        $query = $this->boardExperience;
        return $this->getSearchQuery($query, $search)->count();
    }

    //いいね等のボタン押下記録
    public function createExperienceUser($data)
    {
        return $this->boardExperienceUser->create($data);
    }

    // 1件数の取得
    public function getItemExperienceUser(array $where)
    {
        return $this->boardExperienceUser->where($where)->first();
    }
    // 削除
    public function deleteItemExp(array $where)
    {
        if(empty($item=$this->getItemExp($where)))  return false;
        return $item->delete();
    }

    // 画像データの新規作成or更新
    public function updateOrCreateImageData($Id, $where, $imageData)
    {
        // 商品取得
        if(!$contentRow = $this->getItemExp(['id'=>$Id])) return false;

        // 画像作成
        return $contentRow->images()->updateOrCreate($where, $imageData);
    }

    // 複数件数の取得
    public function getItemsExp(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->boardExperience->where($where);
        if($take)  $query->take($take);
        if($orderByRaw)  $query->orderByRaw($orderByRaw);

        return $query->get();
    }
}
