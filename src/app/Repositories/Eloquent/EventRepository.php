<?php namespace App\Repositories\Eloquent;


use App\Repositories\Eloquent\Models\Event;
use App\Repositories\Eloquent\Models\EventTopic;

use Illuminate\Support\Facades\Log;
use App\Repositories\EventRepositoryInterface;

class EventRepository extends BaseEloquent implements EventRepositoryInterface
{


    public function __construct(
        Event $event,
        EventTopic $eventTopic
        ){
        parent::__construct();
        $this->event = $event;
        $this->eventTopic = $eventTopic;

    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('month', $keys)) if($search['month']!=NULL) $query = $query->where('month', $search['month']);
        if(in_array('name', $keys)) if($search['name']!=NULL) $query = $query->where('name', 'LIKE', "%".$search['name']."%");
        if(in_array('view_flg', $keys)) if($search['view_flg']!=NULL) $query = $query->where('view_flg', $search['view_flg']);


        // -------------------------------- 並び替え --------------------------------
          $query = $query->orderBy("month","ASC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->event;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->event->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->event->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->event->where($where);
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
        return $this->event->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }
    // ------------------------------------- /basic -------------------------------------


    //イベントトピック用
    // 新規作成
    public function createItemTopic(array $data)
    {
        return $this->eventTopic->create($data);
    }

    // 1件数の取得
    public function getItemsTopic(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->eventTopic->where($where);
        if($take)  $query->take($take);
        if($orderByRaw)  $query->orderByRaw($orderByRaw);

        return $query->get();
    }

    // 複数の削除
    public function deleteItemsTopic(array $where)
    {
        return $this->eventTopic->where($where)->delete();
    }




}
