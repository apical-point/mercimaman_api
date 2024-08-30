<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\File;

// サービス
use App\Services\UserService;
use App\Services\OrderService;
use App\Library\Stripe;
use App\Repositories\UserEventRepositoryInterface;

//モデル
use App\Repositories\Eloquent\Models\Order;

class ChargeService extends Bases\BaseService {

    public function __construct(
        UserEventRepositoryInterface $userEventRepo,
        OrderService $orderService,
        UserService $userService
    ) {
        // サービス
        $this->orderService = $orderService;
        $this->userService = $userService;
        $this->userEventRepo = $userEventRepo;
    }

    // 決済
    public function chargeOrder($orderId, $userId, $orderInfo) {
        // 再取得
        $userRow = $this->userService->getItemById($userId);

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail']);

        // リスト取得
        if(!$orderRow = $this->orderService->getItem(['id' => $orderId, 'buyer_user_id' => $userId])) return false;

        // 変数を作成
        $args = [];
        $args['price'] = $orderRow->payment_price;
        $args['description'] = config('const.site.STRIPE_DESCRIPTION_STORE') . $orderInfo;
        $args['stripeId'] = $userRow->userDetail->stripe_id;
        $args['statement_descriptor'] = "Merci!Maman";

        // 決済を実行
        $res = Stripe::chargeStripe($args);
        $data = [];

        if ($res === false || (is_array($res) && $res['result'] == false)){
            return false; //決済NG
        }else{
            $data['charge_id'] = $res['chargeId']; //チャージID
        }

        //更新
        if(!$this->orderService->updateItem(['id' => $orderId], $data)) return false;

        // 返す
        return true;
    }


    // イベント参加　応募処理
    public function createUserEvent($inputData, $userId) {


        $userRow = $this->userService->getItemById($userId);

        //イベント情報取得
        $eventArr = $this->userEventRepo->getItem(["id"=>$inputData["id"]]);

        //イベント登録者情報から手数料計算
        $rate = 0;
        if($eventArr->user_id != 0){
            $eventUserRow = $this->userService->getItemById($eventArr->user_id);
            if($eventUserRow->user_type == 1){
                $rate = config("const.site.USER_EVENT_CHARGE");
            }
            else{
                $rate = config("const.site.USER_CHARGE");
            }
        }

        //応募登録
        $data = [];
        $data["user_event_id"] = $inputData["id"];
        if(!empty($inputData["point"])) $data["point"] = $inputData["point"];
        $data["user_id"] = $userId;
        $data["attend_date"] = date("Y-m-d H:i:s");

        $data["price"] = $eventArr->event_price;
        $data["system_price"] = $eventArr->event_price*$rate;
        $data["pay_price"] = $data["price"]-$data["system_price"];
        //$data["event_user_id"] = $eventArr->user_id;

        if(!$row = $this->userEventRepo->createEventMemberItem($data)) return false;

        return true;

    }

    //イベント参加　決済処理
    public function chargeUserEvent($inputData, $userId) {

        $userRow = $this->userService->getItemById($userId);

        //イベント情報取得
        $eventArr = $this->userEventRepo->getItem(["id"=>$inputData["id"]]);

        //-----申込情報
        $data = [];
        $data["user_event_id"] = $inputData["id"];
        $data["user_id"] = $userId;
        $data["status"] = "0";
        $eventMemberArr = $this->userEventRepo->getEventMemberItems($data);

        if(empty($userRow) || empty($eventArr) || empty($eventMemberArr)){
            return false;
        }


        //--- ポイント利用
        $point = 0;
        if(!empty($inputData["point"])) $point = $inputData["point"];
        $stripe_price = $eventArr->event_price - $point;
        if($stripe_price > 0){

            $info = "申込No".$eventMemberArr[0]->id;

            $args = [];
            $args['price'] = $stripe_price;
            $args['description'] = config('const.site.STRIPE_DESCRIPTION_STORE'). $info;
            $args['stripeId'] = $userRow->userDetail->stripe_id;
            $args['statement_descriptor'] = "Merci!Maman";
            // 決済を実行
            \Log::debug($args);
            $res = Stripe::chargeStripe($args);
            $indata = [];
            $wh = [];
            if ($res['chargeId'] == NULL){
                \Log::debug("決済NG");
                return false; //決済NG
            }else{
                $indata['charge_id'] = $res['chargeId']; //チャージID
            }
        }

        //決済後のupdate
        $indata["pay_flg"] = 1;
        $indata["status"] = 1;
        $wh["id"] = $eventMemberArr[0]->id;
        $this->userEventRepo->updateEventMemberItem($wh, $indata);

        return true;

    }



    // イベント参加　応募と決済　現在未使用。
    //応募と決済を一緒にやっていたが、応募の他ポイント処理も入ったので、応募と決済を分ける処理とする。
    public function __chargeUserEvent($inputData, $userId) {


        $userRow = $this->userService->getItemById($userId);

        //イベント情報取得
        $eventArr = $this->userEventRepo->getItem(["id"=>$inputData["id"]]);

        //イベント登録者情報から手数料計算
        $rate = 0;
        if($eventArr->user_id != 0){
            $eventUserRow = $this->userService->getItemById($eventArr->user_id);
            if($eventUserRow->user_type == 1){
                $rate = config("const.site.USER_EVENT_CHARGE");
            }
            else{
                $rate = config("const.site.USER_CHARGE");
            }
        }

        //応募登録
        $data = [];
        $data["user_event_id"] = $inputData["id"];
        if(!empty($inputData["point"])) $data["point"] = $inputData["point"];
        $data["user_id"] = $userId;
        $data["attend_date"] = date("Y-m-d H:i:s");

        $data["price"] = $eventArr->event_price;
        $data["system_price"] = $eventArr->event_price*$rate;
        $data["pay_price"] = $data["price"]-$data["system_price"];
        //$data["event_user_id"] = $eventArr->user_id;

        if(!$row = $this->userEventRepo->createEventMemberItem($data)) return false;;

        //----決済----


        //-----申込情報
        $data = [];
        $data["user_event_id"] = $inputData["id"];
        $data["user_id"] = $userId;
        $data["status"] = "0";
        $eventMemberArr = $this->userEventRepo->getEventMemberItems($data);

        //--- ポイント利用
        $point = 0;
        if(!empty($inputData["point"])) $point = $inputData["point"];
        $stripe_price = $eventArr->event_price - $point;
        if($stripe_price > 0){

            $info = "申込No".$eventMemberArr[0]->id;

            $args = [];
            $args['price'] = $stripe_price;
            $args['description'] = config('const.site.STRIPE_DESCRIPTION_STORE'). $info;
            $args['stripeId'] = $userRow->userDetail->stripe_id;
            $args['statement_descriptor'] = "Merci!Maman";
            // 決済を実行
            $res = Stripe::chargeStripe($args);
            $indata = [];
            $wh = [];
            if ($res['chargeId'] == NULL){
                \Log::debug("決済NG");
                return false; //決済NG
            }else{
                $indata['charge_id'] = $res['chargeId']; //チャージID
           }
        }

        //決済後のupdate
        $indata["pay_flg"] = 1;
        $indata["status"] = 1;
        $wh["id"] = $eventMemberArr[0]->id;
        $this->userEventRepo->updateEventMemberItem($wh, $indata);

        return true;

    }





    // イベント参加　キャンセル
    public function cancelUserEvent($eventMemberId) {

        \Log::debug("キャンセル処理");
        \Log::debug($eventMemberId);

        //イベント参加情報取得
        $eventArr = $this->userEventRepo->getEventMemberItem(["id"=>$eventMemberId]);

        \Log::debug($eventArr->charge_id);
/*
 * 返金処理を手動で行うとの事で、システムでの決済キャンセルは行わない。
        if(!empty($eventArr->charge_id)){
            $res = Stripe::refundChargeStripe($eventArr->charge_id);

            \Log::debug($res);

            if ($res === false || (is_array($res) && $res['result'] == false)){
                \Log::debug("決済NG");
                return false; //キャンセルNG
            }
        }
*/
        //キャンセル後のupdate
        $data["status"] = 99;
        $wh["id"] = $eventArr->id;
        $this->userEventRepo->updateEventMemberItem($wh, $data);

        return true;

    }


}
