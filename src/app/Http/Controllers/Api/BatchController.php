<?php namespace App\Http\Controllers\Api;

// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\OrderDetailService;
use App\Services\OrderPaymentService;
use App\Services\UserService;
use App\Services\SiteConfigService;
use App\Services\NewsService;
use App\Services\MailService;

class BatchController extends ApiBaseController
{
    // サービス
    protected $productService;
    protected $orderService;
    protected $orderDetailService;
    protected $orderPaymentService;
    protected $userService;
    protected $SiteConfigService;
    protected $newsService;
    protected $mailService;

    // リクエスト
    protected $request;

    public function __construct(
        Request $request,

        // サービス
        ProductService $productService,
        OrderService $orderService,
        OrderDetailService $orderDetailService,
        OrderPaymentService $orderPaymentService,
        UserService $userService,
        SiteConfigService $siteConfigService,
        NewsService $newsService,
        MailService $mailService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // リクエスト
        $this->request = $request;

        // サービス
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->orderDetailService = $orderDetailService;
        $this->orderPaymentService = $orderPaymentService;
        $this->userService = $userService;
        $this->siteConfigService = $siteConfigService;
        $this->newsService = $newsService;
        $this->mailService = $mailService;

    }

    //日時処理
    public function daily(Request $request) {

        $error = [];

        $non_shipping = $this->siteConfigService->getItem(['key_name'=>'non_shipping']);
        $shipping_end = $this->siteConfigService->getItem(['key_name'=>'shipping_end']);
        $trade_end = $this->siteConfigService->getItem(['key_name'=>'trade_end']);

        //未発送商品
        $where['status'] = "1";
        $day = $non_shipping->value;
        $orderRows = $this->orderService->getItems($where);

        foreach($orderRows as $value){
            $productRow = $this->productService->getItem(['id'=>$value['product_id']]);
            //発送の目安より日数が経過していたら通知
            $target_day = $value['order_date'];
            $non_shipping = $day + $productRow->shipping_day;
            $senddate = date("Y-m-d",strtotime($target_day . "+$non_shipping day"));
            if ($senddate <= date("Y-m-d")) {
                try {
                    if(config('const.site.MAIL_SEND_FLG')) {
                        $maildata['name'] = $value['seller_name'];
                        $maildata['product_name'] = $productRow->product_name;
                        $maildata['order_date'] = $value['order_date'];
                        Log::debug($maildata);
                        // 未発送メール
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($value['seller_user_id'], $value['seller_email'], 16, $maildata))
                                                $error[] = "メール送信エラー16:" . $value['seller_user_id'];
                        }
                    }
                } catch (Exception $e) {
                    Log::debug($e);
                }
            }
        }


        //未受取商品
        $where['status'] = "2";
        $orderRows = $this->orderService->getItems($where);
        $day = $shipping_end->value;
        foreach($orderRows as $value){
            //受取未登録
            $target_day = $value['shipping_date'];
            $senddate = date("Y-m-d",strtotime($target_day . "+$day day"));
            if ($senddate < date("Y-m-d")){
                DB::beginTransaction();
                try {
                    //ステータスの更新
                    $data = [];
                    $data['id'] = $value['id'];
                    $data['status'] = "3";
                    $data['arrival_date'] = date("Y-m-d");
                    if(!$orderhistoryRow = $this->orderService->updateItem(["id"=>$value['id']], $data)) $error[] = "status 更新エラー:" . $value['id'];

                    //取引評価登録
                    $orderDetailRow = $this->orderDetailService->getItem(["order_id"=>$value['id']]);
                    $data = [];
                    $data['id'] = $value['id'];
                    $data['seller_user_id'] = $value['seller_user_id'];
                    $data['buyer_user_id'] = $value['buyer_user_id'];
                    $data['seller_evaluation'] = "1";
                    if(!empty($orderDetailRow)){
                        if(!$this->orderDetailService->updateItem(["order_id"=>$value['id']], $data)) $error[] = "評価更新エラー:" . $value['id'];
                    }else{
                        if(!$this->orderDetailService->createItemByOrderId($value['id'], $data)) $error[] = "評価更新エラー:" . $value['id'];
                    }

                    //注文情報のステータス更新
                    $productRow = $this->productService->getItem(['id'=>$value['product_id']]);

                    $data= [];
                    $data['status'] = "5";
                    if(!$this->productService->updateItemById($value['product_id'], $data)) $error[] = "商品更新エラー:" . $value['product_id'];

                    if(config('const.site.MAIL_SEND_FLG')) {
                        //購入者へのメール
                        $maildata['name'] = $value['buyer_name'];
                        $maildata['product_name'] = $productRow->product_name;
                        //
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($value['buyer_user_id'], $value['buyer_email'], 17, $maildata))
                                                $error[] = "メール送信エラー17:" . $value['buyer_user_id'];
                        }

                        $maildata['name'] = $value['seller_name'];
                        $maildata['product_name'] = $productRow->product_name;
                        // 未発送メール
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($value['seller_user_id'], $value['seller_email'], 4, $maildata))
                                                $error[] = "メール送信エラー4:" . $value['seller_user_id'];
                        }
                    }

                    DB::commit();
                } catch (Exception $e) {
                    Log::debug($e);
                    DB::rollBack();
                }

            }
        }

        //引完了商品
        $where['status'] = "3";
        $orderRows = $this->orderService->getItems($where);
        $trade_end = $trade_end->value;

        foreach($orderRows as $value){

            //取引完了未登録
            $target_day = $value['arrival_date'];
            $senddate = date("Y-m-d",strtotime($target_day . "+$trade_end day"));
            if ($senddate < date("Y-m-d")){
                DB::beginTransaction();
                try {
                    //ステータスの更新
                    $data = [];
                    $data['id'] = $value['id'];
                    $data['status'] = "4";
                    $data['tradeend_date'] = date("Y-m-d");
                    if(!$orderhistoryRow = $this->orderService->updateItem(["id"=>$value['id']], $data)) $error[] = "status 更新エラー:" . $value['id'];

                    //取引評価登録
                    $orderDetailRow = $this->orderDetailService->getItem(["order_id"=>$value['id']]);
                    $data = [];
                    $data['id'] = $value['id'];
                    $data['buyer_evaluation'] = "1";
                    if(!empty($orderDetailRow)){
                        if(!$this->orderDetailService->updateItem(["order_id"=>$value['id']], $data)) $error[] = "評価更新エラー:" . $value['id'];
                    }else{
                        if(!$this->orderDetailService->createItemByOrderId($value['id'], $data)) $error[] = "評価更新エラー:" . $value['id'];
                    }

                    //注文情報のステータス更新
                    $productRow = $this->productService->getItem(['id'=>$value['product_id']]);

                    $data= [];
                    $data['status'] = "6";
                    if(!$this->productService->updateItemById($value['product_id'], $data)) $error[] = "商品更新エラー:" . $value['product_id'];

                    if(config('const.site.MAIL_SEND_FLG')) {

                        $maildata['name'] = $value['seller_name'];
                        $maildata['product_name'] = $productRow->product_name;
                        // 完了メール
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($value['seller_user_id'], $value['seller_email'], 18, $maildata))
                                                $error[] = "メール送信エラー18:" . $value['seller_user_id'];
                        }

                        $maildata['name'] = $value['buyer_name'];
                        $maildata['product_name'] = $productRow->product_name;
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($value['buyer_user_id'], $value['buyer_email'], 9, $maildata))
                                                $error[] = "メール送信エラー9:" . $value['buyer_user_id'];
                        }
                    }

                    DB::commit();
                } catch (Exception $e) {
                    Log::debug($e);
                    DB::rollBack();
                }
            }

        }

        return $this->sendResponse($error);

    }

    //月次処理
    public function monthly(Request $request) {

        $error = [];

        //銀行振込手数料
        $bank_transfer_fee = $this->siteConfigService->getItem(['key_name'=>'bank_transfer_fee']);

        //売上の自動申請
        $date =  date('Y-m');
        $transfer = $bank_transfer_fee ->value;

        $where['status'] = "4";
        $where['per_page'] = "-1";
        $where['sellkeep_date'] = $date;
        $where['order_by_raw'] = 'seller_user_id asc';
        $orderRows = $this->orderService->getList($where);
        $orderRows = $orderRows->toArray();
        $cnt = 0;
        $sales_price = 0;
        $total_price = 0;
        $system_charge = 0;
        $order_id = [];
        $count = count($orderRows);

        for ($i = 0; $i < $count; $i++){
            $cnt = $cnt + 1;
            $sales_price = $sales_price + $orderRows[$i]['sales_price'];
            $total_price = $total_price + $orderRows[$i]['sales_price'];
            $system_charge = $system_charge + $orderRows[$i]['sales_price'];
            $order_id[] = $orderRows[$i]['id'];

            //最後の行か？
            if ( $i == $count - 1){
                $rtn = $this->_storePayment($orderRows[$i], $cnt, $sales_price, $total_price,$system_charge , $transfer, $order_id );
                if ($rtn !== false) $error[] = $rtn;
                break;
            }

            if ($orderRows[$i]['seller_user_id'] != $orderRows[$i + 1]['seller_user_id']){
                $rtn = $this->_storePayment($orderRows[$i], $cnt, $sales_price, $total_price,$system_charge , $transfer,$order_id );
                if ($rtn !== false) $error[] = $rtn;
                $cnt = 0;
                $sales_price = 0;
                $total_price = 0;
                $system_charge = 0;
                $order_id = [];
            }

        }

        //お知らせの削除
        $date =  date('Y-m-d', strtotime("-3 month"));

        $where = [];
        $where['open_date_to'] = $date;
        $where['per_page'] = "-1";
        $newsRows = $this->newsService->getList($where);
        $newsRows = $newsRows->toArray();

        if (count($newsRows) > 0){
            DB::beginTransaction();

            try {
                if(!$this->newsService->datedelete($date)) $error[] = "お知らせ削除エラー";
                DB::commit();
            } catch (Exception $e) {
                Log::debug($e);
                DB::rollBack();
            }
        }

        return $this->sendResponse($error);

    }

    //申請処理
    private function  _storePayment($old_seller, $cnt, $sales_price, $total_price,$system_charge, $bank_transfer_fee, $order_id){

        $error = false;

        DB::beginTransaction();
        try {

            //ユーザー情報を取得する
            if(!$userRow = $this->userService->getItem(['id'=>$old_seller['seller_user_id']])) return $this->sendNotFoundErrorResponse();
            $userRow->loadMissing(['userDetail']);
            $user = $userRow->toArray();
            if (is_null($user['user_detail']['bank_code'])){
                if(config('const.site.MAIL_SEND_FLG')) {
                    $maildata['name'] = $old_seller['seller_name'];
                    if(!$this->mailService->sendMail_transaction($old_seller['seller_user_id'], $old_seller['seller_email'], 20, $maildata))
                                        return "status 更新エラー:" . $old_seller['id'];
                }
            }else{

                $data = [];
                $data['user_id'] = $old_seller['seller_user_id'];
                $data['count'] = $cnt;
                $bank_price = $sales_price - $bank_transfer_fee;
                $data['bank_price'] = $bank_price;
                $data['total_price'] = $sales_price;
                $data['sales_price'] = $total_price;
                $data['system_charge'] = $system_charge;
                $data['payment_request_date'] =  date('Y-m-d');

                if(!$orderPaymentyRow = $this->orderPaymentService->createItem($data)) return "status 更新エラー:" . $old_seller['id'];

                //ステータスの更新
                foreach($order_id as $value){
                    $data = [];
                    $data['id'] = $value;
                    $data['status'] = "6";
                    $data['payment_request_date'] = date("Y-m-d");
                    $data['payment_id'] = $orderPaymentyRow->id;
                    if(!$orderhistoryRow = $this->orderService->updateItem(["id"=>$value], $data)) return "status 更新エラー:" . $old_seller['id'];
                }

                if(config('const.site.MAIL_SEND_FLG')) {
                    $maildata['name'] = $old_seller['seller_name'];
                    $maildata['bank_price'] = $bank_price;
                    $maildata['cnt'] = $cnt;

                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($old_seller['seller_user_id'], $old_seller['seller_email'], 19, $maildata))
                                            return "status 更新エラー:" . $old_seller['id'];
                    }
                }

            }

            DB::commit();

        }catch (Exception $e) {
            Log::debug($e);
            DB::rollBack();
            $error = "エラー4:" . $old_buyer['id'];
        }

        return $error;

    }

}
