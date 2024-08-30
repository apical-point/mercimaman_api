<?php namespace App\Services;

// ulid
// use \Ulid;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


// リポジトリ
use App\Repositories\MessageRepositoryInterface;
use App\Repositories\Eloquent\Models\Message;

class MessageService extends Bases\BaseService
{
    protected $messageRepo;


    public function __construct(
        MessageRepositoryInterface $messageRepo

    ) {
        $this->messageRepo = $messageRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->messageRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->messageRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $messageByRaw='')
    {
        return $this->messageRepo->getItems($where, $take, $messageByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->messageRepo->getItem($where);
    }

    // idで取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // 更新
    public function updateItem($where, $data)
    {
        // ユーザーの更新
        if(!$this->messageRepo->updateItem($where, $data)) return false;

        return true;
    }

    // idで更新
    public function updateItemById($id, $data)
    {
        return $this->messageRepo->updateItem(['id'=>$id], $data);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->messageRepo->deleteItem(['id'=>$id]);
    }

    // --------------------------- /基本的なもの ---------------------------

    /**
     * 最終確認日時を現在時刻に更新する。
     * @param array $where 更新対象
     */
    public function updateConfirmDate(array $where){
        $now = date('Y-m-d');
        if (!$this->messageRepo->updateConfirm($where, ['confirm_date' => $now])){
            return false;
        }
        return true;
    }

    /**
     * 指定したオーダーに紐づくメッセージを返す。
     * @param int $orderId 対象オーダーのID
     * @param int $userId 取得者のユーザーID
     * @param bool $isUpdate true:確認日時を更新する、false:確認日時を更新しない
     */
    public function getOrderMessages($orderId, $userId = 0, $isUpdate = false)
    {
        if ($isUpdate){
            $confim = $this->getOrderConfirm($orderId, $userId);
            $now = date('Y-m-d H:i:s');
            if ($confim){
                if (!$this->updateConfirmDate(['id' => $confim->id])){
                    return false;
                }
            }else{
                if (!$this->createConfirm(['group_id' => $orderId, 'group_kind' => Message::GROUP_KIND_ORDER, 'user_id' => $userId, 'confirm_at' => $now])){
                    return false;
                }
            }
        }
        return $this->messageRepo->getOrderMessages($orderId);
    }

    /**
     * 指定したユーザーの最終確認日時を返す。
     * @param int $userId 取得者のユーザーID
     */
    public function getOrderConfirm($orderId, $userId)
    {
        return $this->messageRepo->getConfirmItem(['group_id' => $orderId, 'group_kind' => Message::GROUP_KIND_ORDER, 'user_id' => $userId]);
    }

    /**
     * 指定したオーダーに対しユーザーが未読のメッセージがあればtrueを返す
     * @param int $orderId 対象オーダーID
     * @param int $userId 対象ユーザーID
     */
    public function hasNoReadMessage($orderId, $userId)
    {
        return $this->messageRepo->hasNoReadMessage($orderId, $userId);
    }


    /**
     * 指定したユーザー間のメッセージ一覧を返す
     * @param int $from 送信者ID
     * @param int $to 受信者ID
     * @param boolean $isUpdate 参照日時更新の有無
     */
    public function getIndividualMessages($from, $to, $isUpdate)
    {
        $relation = $this->individualRelationRepo->getItem(['user_id1' => ($from < $to ? $from : $to), 'user_id2' => ($from > $to ? $from : $to)]);
        if (!$relation){
            $relation = $this->individualRelationRepo->createItem(['user_id1' => ($from < $to ? $from : $to), 'user_id2' => ($from > $to ? $from : $to)]);
        }
        if (!$relation){
            return false;
        }

        if ($isUpdate){
            // 送信者に対応する受信者のアクセス用レコードの参照日時を更新
            if (!$this->individualAccessRepo->updateItem(['relation_id' => $relation->id, 'to_id' => $from],['reference_at' => Date('Y-m-d H:i:s')]));
        }
        return $this->messageRepo->getIndividualMessages($relation->id);
    }

    /**
     * 指定したユーザー間のアクセス情報を返す
     * @param int $from 送信者ID
     * @param int $to 受信者ID
     */
    public function getIndividualAccess($from, $to)
    {
        return $this->individualAccessRepo->getItem(['from_id' => $from, 'to_id' => $to]);
    }

    /**
     * 指定したユーザーの未読個別メッセージの有無を返す
     * @param int $to 受信者ID
     */
    public function hasIndividualNoread($to)
    {
        return $this->individualAccessRepo->hasNoreadIndividualMessage($to);
    }

    /**
     * 指定したオーダーのメッセージをすべて削除
     * @param int $orderId 削除対象オーダーID
     */
    public function deleteOrderMessage($orderId)
    {
        // メッセージの削除
        if (!$this->messageRepo->deleteItems(['group_id' => $orderId, 'group_kind' => Message::GROUP_KIND_ORDER])){
            return false;
        }
        // メッセージ確認情報の削除
        return $this->messageRepo->deleteConfirmItems(['group_id' => $orderId, 'group_kind' => Message::GROUP_KIND_ORDER]);
    }

    // 画像の登録
    public function createImageByMessageId($messageId, $imageData)
    {

        if(!$image=$this->messageRepo->createImageByMessageId($messageId, $imageData)) return false;
        return $image;

    }

}
