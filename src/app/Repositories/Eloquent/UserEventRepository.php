<?php namespace App\Repositories\Eloquent;


use App\Repositories\Eloquent\Models\UserEvent;
use App\Repositories\Eloquent\Models\UserEventMember;

use Illuminate\Support\Facades\Log;
use App\Repositories\UserEventRepositoryInterface;

class UserEventRepository extends BaseEloquent implements UserEventRepositoryInterface
{


    public function __construct(
        UserEvent $userEvent,
        UserEventMember $userEventMember
        ){
        parent::__construct();
        $this->userEvent = $userEvent;
        $this->userEventMember = $userEventMember;

    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('no_admin', $keys)) if($search['no_admin']!=NULL) $query = $query->where('user_id', "!=", 0);
        if(in_array('event_name', $keys)) if($search['event_name']!=NULL) $query = $query->where('event_name', 'LIKE', "%".$search['event_name']."%");
        if(in_array('event_date', $keys)) if($search['event_date']!=NULL) $query = $query->where('event_date', 'LIKE', $search['event_date']."%");
        if(in_array('status', $keys)) if($search['status']!=NULL) $query = $query->where('status', $search['status']);
        if(in_array('open_event_date', $keys)) if($search['open_event_date']!=NULL) $query = $query->where('event_date', ">", $search['open_event_date']);
        if(in_array('end_event_date', $keys)) if($search['end_event_date']!=NULL) $query = $query->where('event_date', "<", $search['end_event_date']);
        if(in_array('block_users', $keys)) if($search['block_users']!=NULL) $query = $query->whereNotIn('user_id', $search['block_users']); //表示しない

        if(in_array('pref', $keys)) if($search['pref']!=NULL) $query = $query->where('pref', $search['pref']);
        if(in_array('admit_status', $keys)) if($search['admit_status']!=NULL) $query = $query->where('admit_status', $search['admit_status']);

        if(in_array('keyword', $keys)) if($search['keyword'] != NULL){
            $item = $search['keyword'];
            $query->where(function($query)use($item){
                $query = $query->where('event_name', 'LIKE', "%".$item."%")
                ->orwhere('event_detail', 'LIKE', "%".$item."%")
                ->orwhere('place', 'LIKE', "%".$item."%");
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
        $query = $this->userEvent;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->userEvent->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->userEvent->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->userEvent->where($where);
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
        return $this->userEvent->where($where)->delete();
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


    // イベント参加登録
    public function createEventMemberItem($data)
    {
        return $this->userEventMember->create($data);
    }

    // 1件数の取得
    public function getEventMemberItem($where)
    {
        return $this->userEventMember->where($where)->first();
    }

    public function getEventMemberItems($where, $take=0, $orderByRaw='')
    {
        $query = $this->userEventMember->where($where);
        if($take)  $query->take($take);
        if($orderByRaw)  $query->orderByRaw($orderByRaw);

        return $query->get();
    }

    // 更新
    public function updateEventMemberItem($where, $data)
    {
        if(empty($item=$this->getEventMemberItem($where)))  return false;

        return $item->fill($data)->save();
    }

    // 検索
    public function getEventMemberSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('id', $keys)) if($search['id']!=NULL) $query = $query->where('id', $search['id']);
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('user_id', $search['user_id']);
        if(in_array('user_event_id', $keys)) if($search['user_event_id']!=NULL) $query = $query->where('user_event_id', $search['user_event_id']);
        if(in_array('status', $keys)) if($search['status']!=NULL) $query = $query->where('status', $search['status']);
        if(in_array('attend_date', $keys)) if($search['attend_date']!=NULL) $query = $query->where('attend_date', 'LIKE',$search['attend_date']."%");


        if(in_array('pay_status', $keys)) if($search['pay_status']!=NULL){
            if ( is_array($search['pay_status'])){
                $query = $query->where(function($query) use($search){
                    if (count($search['pay_status']) == 2){
                        $query->where('pay_status', $search['pay_status'][0])
                        ->orWhere('pay_status', $search['pay_status'][1]);
                    }else{
                        $query->where('pay_status', $search['pay_status'][0])
                        ->orWhere('pay_status', $search['pay_status'][1])
                        ->orWhere('pay_status', $search['pay_status'][2]);
                    }
                });
            }else{
                $query = $query->where('pay_status', $search['pay_status']);
            }
        }

        //user_event
        if(in_array('event_date', $keys)) if($search['event_date']!=NULL){
            $item = $search['event_date'];
            $query = $query->whereHas('userEvent', function($query) use($item, $keys) {
                $query->where(function($query) use($item){
                    $query->where('event_date',">=",$item);
                });
            });
        }
        if(in_array('end_event_date', $keys)) if($search['end_event_date']!=NULL){
            $item = $search['end_event_date'];
            $query = $query->whereHas('userEvent', function($query) use($item, $keys) {
                $query->where(function($query) use($item){
                    $query->where('event_date',"<",$item);
                });
            });
        }

        //イベント作成者
        if(in_array('event_user_id', $keys)) if($search['event_user_id']!=NULL){
            $item = $search['event_user_id'];
            $query = $query->whereHas('userEvent', function($query) use($item, $keys) {
                $query->where(function($query) use($item){
                    $query->where('user_id',$item);
                });
            });
        }

        // -------------------------------- 並び替え --------------------------------
        $query = $query->orderBy("id","DESC");

        Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getEventMemberList($search=[])
    {
        // query化
        $query = $this->userEventMember;

        // ページ
        $perPage = $this->getPerPage($search);

        return $perPage!==-1 ? $this->getEventMemberSearchQuery($query, $search)->paginate($perPage) : $this->getEventMemberSearchQuery($query, $search)->get();
    }


}
