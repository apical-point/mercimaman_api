<?php namespace App\Repositories\Eloquent;


use App\Repositories\Eloquent\Models\Tweet;
use App\Repositories\Eloquent\Models\TweetCheck;

use Illuminate\Support\Facades\Log;
use App\Repositories\BoardRepositoryInterface;
use App\Repositories\TweetRepositoryInterface;

class TweetRepository extends BaseEloquent implements TweetRepositoryInterface
{


    public function __construct(
        Tweet $tweet,
        TweetCheck $tweetCheck
        ){
        parent::__construct();
        $this->tweet = $tweet;
        $this->tweetCheck = $tweetCheck;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('tweet', $keys)) if($search['tweet']!=NULL) $query = $query->where('tweet', 'LIKE', "%".$search['tweet']."%");
        if(in_array('tweet_flg', $keys)) if($search['tweet_flg']!=NULL) $query = $query->where('tweet_flg', $search['tweet_flg']);
        if(in_array('parent_id', $keys)) if($search['parent_id']!=NULL) $query = $query->where('parent_id', $search['parent_id']);
        if(in_array('block_users', $keys)) if($search['block_users']!=NULL) $query = $query->whereNotIn('user_id', $search['block_users']); //表示しない

        //退会していない人の情報を取得
        $query = $query->whereHas('user', function($query) {

        });

        //ニックネームや名前で検索
        if(in_array('name', $keys)) if($search['name']!=NULL){
            $item = $search['name'];
            $query = $query->whereHas('userDetail', function($query) use($item, $keys) {
                    $query->where(function($query) use($item){
                        $query->where(\DB::raw('CONCAT(last_name, first_name)'), 'LIKE', "%".$item."%");
                    });
            });
            $query = $query->whereHas('userProfile', function($query) use($item, $keys) {
                $query->orwhere(function($query) use($item){
                    $query->orwhere('nickname', 'LIKE', "%".$item."%");
                });
            });
         }

         //コンディションが同じユーザーのツイート取得
         if(in_array('condition', $keys)) if($search['condition']!=NULL){
             $item = $search['condition'];
             $query = $query->whereHas('userProfile', function($query) use($item, $keys) {
                 $query->where(function($query) use($item){
                     $query->where('condition', $item);
                 });
             });

         }

        // -------------------------------- 並び替え --------------------------------
          $query = $query->orderBy("id","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->tweet;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->tweet->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->tweet->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->tweet->where($where);
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
        return $this->tweet->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------

    //条件に対してのカウント数を取得
    public function getCountData($search=[])
    {
        // query化
        $query = $this->tweet;
        return $this->getSearchQuery($query, $search)->count();
    }

    /*
     * いいね、えらい、わかる、の人数更新処理
     *
     * $data  は　以下のいずれかが引数となってくる。
     *   $data["check1"]=1;
     *   $data["check2"]=2;
     *   $data["check3"]=3;
     *
     */
    public function updateIncrement($where, array $data)
    {
        if(empty($item=$this->getItem($where)))  return false;
        $k = key($data);

        return $item->increment($k, 1);

    }


    //いいね等のボタン押下記録
    public function createCheckUser($data)
    {
        return $this->tweetCheck->create($data);
    }

    // 1件数の取得
    public function getItemCheckUser(array $where)
    {
        return $this->tweetCheck->where($where)->first();
    }

    //指定ユーザーの各ツイートリアクション合計数
    public function getItemCheckUserSum(array $where)
    {
        $query = $this->tweet;
        $tmp["check1_sum"] = $this->getSearchQuery($query, $where)->sum("check1");
        $tmp["check2_sum"] = $this->getSearchQuery($query, $where)->sum("check2");
        $tmp["check3_sum"] = $this->getSearchQuery($query, $where)->sum("check3");

        return $tmp;
    }



}
