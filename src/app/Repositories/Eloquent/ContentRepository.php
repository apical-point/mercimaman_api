<?php namespace App\Repositories\Eloquent;

use App\Repositories\ContentRepositoryInterface;
use App\Repositories\Eloquent\Models\Content;
use App\Repositories\Eloquent\Models\Crossword;
use App\Repositories\Eloquent\Models\Tarot;
use App\Repositories\Eloquent\Models\TarotUser;
use Illuminate\Support\Facades\Log;

class ContentRepository extends BaseEloquent implements ContentRepositoryInterface
{
    protected $content;

    public function __construct(
        Content $content,
        Crossword $crossword,
        Tarot $tarot,
        TarotUser $tarotUser
        ){
        parent::__construct();
        $this->content = $content;
        $this->crossword = $crossword;
        $this->tarot = $tarot;
        $this->tarotUser = $tarotUser;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('theme', $keys)) if($search['theme']!=NULL) $query = $query->where('theme', 'LIKE', "%".$search['theme']."%");
        if(in_array('choicestheme', $keys)) if($search['choicestheme']!=NULL) $query = $query->where('choicestheme', 'LIKE', "%".$search['choicestheme']."%");
        if(in_array('present', $keys)) if($search['present']!=NULL) $query = $query->where('present', 'LIKE', "%".$search['present']."%");

        if(in_array('themedate_start_from', $keys)) if($search['themedate_start_from']!=NULL) $query = $query->where('themedate','>=' , $search['themedate_start_from']);
        if(in_array('themedate_start_to', $keys)) if($search['themedate_start_to']!=NULL) $query = $query->where('themedate','<=' , $search['themedate_start_to']);

        if(in_array('themedate', $keys)) if($search['themedate']!=NULL) $query = $query->where('themedate','<=' , $search['themedate']);
        if(in_array('choicesdate', $keys)) if($search['choicesdate']!=NULL) $query = $query->where('choicesdate','<=' , $search['choicesdate']);
        if(in_array('presentdate', $keys)) if($search['presentdate']!=NULL) $query = $query->where('presentdate','<=' , $search['presentdate']);

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            $query = $query->orderByRaw('themedate desc');
        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }
      \Log::debug(print_r($query->toSql(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->content;

        // ページ
        $perPage = $this->getPerPage($search);
        
        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->content->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->content->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->content->where($where);
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
        return $this->content->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------
    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->content->whereIn('id', $ids)->get();
    }

    // 画像データの新規作成or更新
    public function updateOrCreateImageData($productId, $where, $imageData)
    {
        // 商品取得
        if(!$contentRow = $this->getItem(['id'=>$productId])) return false;

        // 画像作成
        return $contentRow->images()->updateOrCreate($where, $imageData);
    }


    //------ クロスワード -------------------
    // 新規作成
    public function createItemCrossword(array $data)
    {
        return $this->crossword->create($data);
    }


    // 1件数の取得
    public function getItemCrossword(array $where)
    {
        return $this->crossword->where($where)->first();
    }

    // 更新
    public function updateItemCrossword(array $where, array $data)
    {
        if(empty($item=$this->getItemCrossword($where)))  return false;

        return $item->fill($data)->save();
    }

    public function getSearchQueryCrossword($query, $search)
    {
        $keys = array_keys($search);

        Log::debug("クロスワード");
        Log::debug($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('post_date', $keys)) if($search['post_date']!=NULL) $query = $query->where('post_date', $search['post_date']);

        if(in_array('post_date_start_to', $keys)) if($search['post_date_start_to']!=NULL) $query = $query->where('post_date','<=' , $search['post_date_start_to']);

        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("post_date","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getListCrossword($search=[])
    {
        // query化
        $query = $this->crossword;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQueryCrossword($query, $search)->paginate($perPage) : $this->getSearchQueryCrossword($query, $search)->get();
    }


    // 削除
    public function deleteItemCrossword(array $where)
    {
        if(empty($item=$this->getItemCrossword($where)))  return false;
        return $item->delete();
    }


    //------ タロット -------------------
    // 新規作成
    public function createItemTarot(array $data)
    {
        return $this->tarot->create($data);
    }


    // 1件数の取得
    public function getItemTarot(array $where)
    {
        return $this->tarot->where($where)->first();
    }

    // 更新
    public function updateItemTarot(array $where, array $data)
    {
        if(empty($item=$this->getItemTarot($where)))  return false;

        return $item->fill($data)->save();
    }

    public function getSearchQueryTarot($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('post_date', $keys)) if($search['post_date']!=NULL) $query = $query->where('post_date', $search['post_date']);
        if(in_array('post_date_start_to', $keys)) if($search['post_date_start_to']!=NULL) $query = $query->where('post_date','<=' , $search['post_date_start_to']);

        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("post_date","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getListTarot($search=[])
    {
        // query化
        $query = $this->tarot;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQueryTarot($query, $search)->paginate($perPage) : $this->getSearchQueryTarot($query, $search)->get();
    }


    // 削除
    public function deleteItemTarot(array $where)
    {
        if(empty($item=$this->getItemTarot($where)))  return false;
        return $item->delete();
    }


    public function createItemTarotUser(array $data)
    {
        return $this->tarotUser->create($data);
    }


    public function getSearchQueryTarotUser($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('tarot_id', $keys)) if($search['tarot_id']!=NULL) $query = $query->where('tarot_id', $search['tarot_id']);
        if(in_array('user_id', $keys)){
            if($search['user_id']!=NULL){
                $query = $query->where('user_id', $search['user_id'])->orwhere('user_ip_address', $search['user_id']);
            }
        }


        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("id","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getListTarotUser($search=[])
    {
        // query化
        $query = $this->tarotUser;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQueryTarotUser($query, $search)->paginate($perPage) : $this->getSearchQueryTarotUser($query, $search)->get();
    }

}
