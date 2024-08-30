<?php namespace App\Repositories\Eloquent;

use App\Repositories\ContentMessageRepositoryInterface;
use App\Repositories\Eloquent\Models\ContentMessage;
use Illuminate\Support\Facades\Log;

class ContentMessageRepository extends BaseEloquent implements ContentMessageRepositoryInterface
{
    protected $contentMessage;

    public function __construct(
        ContentMessage $contentMessage
        ){
        parent::__construct();
        $this->contentMessage = $contentMessage;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('open_flg', $keys)) if($search['open_flg']!=NULL) $query = $query->where('open_flg',  $search['open_flg']);
        if(in_array('type', $keys)) if($search['type']!=NULL) $query = $query->where('type',  $search['type']);
        if(in_array('content_id', $keys)) if($search['content_id']!=NULL) $query = $query->where('content_id',  $search['content_id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('content_messages.user_id',  $search['user_id']);

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
        $query = $this->contentMessage;
        $query = $query->select('content_messages.*', 'user_profiles.image_id', 'user_profiles.nickname');
        $query = $query->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'content_messages.user_id');

        //$perPage = $this->getPerPage($search);

        return $this->getSearchQuery($query, $search)->get();
        //return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->contentMessage->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->contentMessage->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->contentMessage->where($where);
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
        return $this->contentMessage->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------


}
