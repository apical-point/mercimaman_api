<?php namespace App\Repositories\Eloquent;

use App\Repositories\MessageRepositoryInterface;
use App\Repositories\Eloquent\Models\Message;
use Illuminate\Support\Facades\Log;


class MessageRepository extends BaseEloquent implements MessageRepositoryInterface
{
    protected $message;

    public function __construct(
        Message $message
        ){
        parent::__construct();
        $this->message = $message;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        if ( in_array('user_from_1', $keys) && $search['user_from_1']!=NULL && in_array('user_from_2', $keys) && $search['user_from_2']!=NULL){
            $query = $query->where(function($query) use($search){
                    $query->Where('user_from_id', '=', $search['user_from_1'])
                        ->Where('user_to_id', '=', $search['user_to_1']);
                 })->orwhere(function($query) use($search){
                     $query->Where('user_from_id', '=', $search['user_from_2'])
                        ->Where('user_to_id', '=', $search['user_to_2']);
                 });
        }
        if(in_array('open_flg', $keys)) if($search['open_flg']!=NULL) $query = $query->where('open_flg', $search['open_flg']);
        if(in_array('created_at', $keys)) if($search['created_at']!=NULL) $query = $query->where("messages.created_at","LIKE",  "%".$search['created_at']."%");
        if(in_array('msg', $keys)) if($search['msg']!=NULL) $query = $query->whereNotNull('message');

        if ( in_array('user_id', $keys) && $search['user_id']!=NULL){
            $query = $query->where(function($query) use($search){
                    $query->Where('user_from_id', '=', $search['user_id']);
                 })->orwhere(function($query) use($search){
                     $query->Where('user_to_id', '=', $search['user_id']);
                 });
        }
        if ( in_array('nickname', $keys) && $search['nickname']!=NULL){
            $query = $query->where(function($query) use($search){
                    $query->Where('a.nickname', 'LIKE', '%'.$search['nickname'].'%');
                 })->orwhere(function($query) use($search){
                     $query->Where('b.nickname', 'LIKE', '%'.$search['nickname'].'%');
                 });
        }

        if(in_array('user_from_id', $keys)) if($search['user_from_id']!=NULL) $query = $query->where('user_from_id', $search['user_from_id']);


        if(in_array('order_by_raw', $keys)){
            $query = $query->orderByRaw($search["order_by_raw"]);
        }
        else{
            $query = $query->orderByRaw('created_at');
        }

        \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->message;
        $query = $query->select('messages.*', 'a.nickname as send_nickname', 'b.nickname as recive_nickname');
        $query = $query->leftJoin('user_profiles as a', 'a.user_id', '=', 'messages.user_from_id');
        $query = $query->leftJoin('user_profiles as b', 'b.user_id', '=', 'messages.user_to_id');

        // ページ
        $perPage = $this->getPerPage($search);

        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->message->create($data);
    }

    // 1件数の取得
    public function getItem(array $where)
    {
        return $this->message->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $messageByRaw='')
    {
        $query = $this->message->where($where);
        if($take)  $query->take($take);
        if($messageByRaw)  $query->messageByRaw($messageByRaw);

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
        return $this->message->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

    /**
     * メッセージ確認情報の削除
     */
    public function deleteConfirmItems(array $where)
    {
        return $this->messageConfirm->where($where)->delete();
    }

    // ------------------------------------- /basic -------------------------------------

    /*
     * 確認者が未確認のメッセージがあるか
     */
    public function hasNoReadMessageUser($userId){

        //$query = $this->messageConfirm;
        $query = $this->messageConfirm->where(["user_id" => $userId]);
        $query->whereNull("confirm_at");

        return $query->count() > 0;

    }

    /**
     * 指定したオーダーに紐づくメッセージを返す。
     * @param int $orderId 対象オーダーのID
     */
    public function getOrderMessages($orderId)
    {
        $query = $this->message->where(['group_id' => $orderId, 'group_kind' => Message::GROUP_KIND_ORDER])->orderby('created_at','desc');
        \Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query->get();
    }

    /**
     * 指定したオーダーに対しユーザーが未読のメッセージがあればtrueを返す
     * @param int $orderId 対象オーダーID
     * @param int $userId 対象ユーザーID
     */
    public function hasNoReadMessage($orderId, $userId)
    {
        $confirm = $this->getConfirmItem(['group_id'=> $orderId, 'group_kind' => Message::GROUP_KIND_ORDER, 'user_id' => $userId]);
        if ($confirm){
            $query = $this->message->where([['group_id', $orderId], ['group_kind', Message::GROUP_KIND_ORDER], ['created_at', '>', $confirm->confirm_at]]);
        }else{
            $query = $this->message->where([['group_id', $orderId], ['group_kind', Message::GROUP_KIND_ORDER]]);
        }

        \Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query->count() > 0;
    }

    /**
     * 指定した関係IDに紐づくメッセージを返す。
     * @param int $relationId 対象関係のID
     */
    public function getIndividualMessages($relationId)
    {
        $query = $this->message->where(['group_id' => $relationId, 'group_kind' => Message::GROUP_KIND_INDIVIDUAL])->orderby('created_at','desc');
        \Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query->get();
    }


    //region 確認日時
    /**
     * 指定したユーザーの最終確認日時を返す。
     * @param int $userId 取得者のユーザーID
     */
    public function getConfirmItem($where)
    {
        $query = $this->message->where($where);
        \Log::debug(print_r($query->toSql(), true)."     ".print_r($query->getBindings(), true));
        return $query->get()->first();
    }

    // 新規作成
    public function createConfirm(array $data)
    {
        return $this->messageConfirm->create($data);
    }


    // 更新
    public function updateConfirm(array $where, array $data)
    {
        if(empty($item=$this->getConfirmItem($where)))  return false;
        return $item->fill($data)->save();
    }


    // 画像データの新規作成
    public function createImageByMessageId($messageId, $imageData)
    {
        // 商品取得
        if(!$row = $this->getItem(['id'=>$messageId])) return false;

        // 画像作成
        return $row->images()->create($imageData);
    }

}
