<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Exception;

// サービス
use App\Services\OrderService;
use App\Services\OrderDetailService;
use App\Services\OrderPaymentService;
use App\Services\ProductService;
use App\Services\UserService;
use App\Services\PointService;
use App\Services\ChargeService;
use App\Services\SiteConfigService;
use App\Services\PrefectureService;
use App\Services\MailService;
use App\Services\NotifyService;

// バリデート
use App\Validators\Api\OrderValidator;

//Model
use App\Repositories\Eloquent\Models\Order;

class OrderController extends Bases\ApiBaseController
{
    // サービス
    protected $orderService;
    protected $orderDetailService;
    protected $orderPaymentService;
    protected $productService;
    protected $userService;
    protected $pointService;
    protected $chargeService;
    protected $SiteConfigService;
    protected $prefectureService;
    protected $mailService;
    protected $notifyService;

    // リクエスト
    protected $request;

    // バリデート
    protected $orderValidator;

    // メール
    protected $orderMail;

    public function __construct(
        // リクエスト
        Request $request,

        // オーダーサービス
        OrderService $orderService,
        OrderDetailService $orderDetailService,
        OrderPaymentService $orderPaymentService,
        ProductService $productService,
        UserService $userService,
        PointService $pointService,
        ChargeService $chargeService,
        SiteConfigService $siteConfigService,
        PrefectureService $prefectureService,
        MailService $mailService,
        NotifyService $notifyService,
        // バリデート
        OrderValidator $orderValidator

        // メール
        //OrderMail $orderMail
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // リクエスト
        $this->request = $request;

        // サービス
        $this->orderService = $orderService;
        $this->orderDetailService = $orderDetailService;
        $this->orderPaymentService = $orderPaymentService;
        $this->productService = $productService;
        $this->userService = $userService;
        $this->pointService = $pointService;
        $this->chargeService = $chargeService;
        $this->siteConfigService = $siteConfigService;
        $this->prefectureService = $prefectureService;
        $this->mailService = $mailService;
        $this->notifyService = $notifyService;

        // バリデート
        $this->orderValidator = $orderValidator;

        // メール
    //    $this->orderMail = $orderMail;
    }

    // リスト
    public function index(Request $request) {
        // データの取得
        $inputData = $request->all();

        try {
            // リスト取得
            $planList = $this->orderService->getList($inputData);

            // 返す
            return $this->sendResponse($planList);
        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件取得
    public function show(Request $request, $id) {
        // db操作
        try {
            // リスト取得
            if(!$orderRow = $this->orderService->getItem(['id'=>$id])) return $this->sendNotFoundErrorResponse();

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $orderRow->loadMissing(['orderDetails', 'prefecture']);

            // 返す
            return $this->sendResponse($orderRow);
        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


    // 購入処理
    public function store(Request $request) {
        // データの取得
        $inputData = $request->all();

        // 商品取得
        $productRows = $this->productService->getItem(['id'=>$inputData['product_id']]);
        $productRows = $productRows->toArray();

        // なければエラー
        if(count($productRows) == 0) return $this->sendErrorResponse();

        // 購入ユーザーデータの取得
        $userData =$request->user();
        if(!$buyer_userRow = $this->userService->getItemByID($userData->id)) return $this->sendNotFoundErrorResponse();
        $buyer_userRow->loadMissing(['userDetail']);
        $buyer_userRow = $buyer_userRow->toArray();
        if(!$pointRow = $this->pointService->getUsersSum($userData->id)) return $this->sendNotFoundErrorResponse();
        $pointRow = $pointRow->toArray();

        // バリデート
        if($val=$this->orderValidator->store($inputData, $productRows, $pointRow)) return $this->sendValidateErrorResponse($val);

        // 出品ユーザーデータの取得
        if(!$seller_userRow = $this->userService->getItemByID($productRows['user_id'])) return $this->sendNotFoundErrorResponse();
        $seller_userRow->loadMissing(['userDetail']);
        $seller_userRow = $seller_userRow->toArray();

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        //$productRows->loadMissing(['productCategory', 'mainImage']);

        // db操作
        DB::beginTransaction();
        try {

            //初回購入の場合ポイント付与
            $pointRow = $this->orderService->getItem(['buyer_user_id'=>$userData->id]);
            if (empty($pointRow)){
                $data['point_type'] = "4";
                $data['point_detail'] = config('const.point.4.info');
                $data['point'] = config('const.point.4.point');
                $data['user_id'] = $userData->id;
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+3 month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
            }

            // 注文データと注文詳細の作成
            $orderDate = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));
            $orderData = [
                'seller_user_id' => $seller_userRow['id'],
                'buyer_user_id' => $buyer_userRow['id'],
                'product_id' => $inputData['product_id'],
                'total_price' => $productRows['price'],
                'system_charge' => $productRows['system_charge'],
                'sales_price' => $productRows['price'] - $productRows['system_charge'],
                'payment_price' => $productRows['price'] - $inputData['point'],
                'point' => $inputData['point'],
                'status' => 1,
                'charge_id' => 0,
                'seller_zip' =>  $seller_userRow['user_detail']['zip'],
                'seller_prefecture_id' => $seller_userRow['user_detail']['prefecture_id'],
                'seller_address1' => $seller_userRow['user_detail']['address1'],
                'seller_address2' => $seller_userRow['user_detail']['address2'],
                'seller_building' => $seller_userRow['user_detail']['building'],
                'seller_name' => $seller_userRow['user_detail']['last_name'] . $seller_userRow['user_detail']['first_name'],
                'seller_email' => $seller_userRow['email'],
                'seller_tel' => $seller_userRow['user_detail']['tel'],
                'buyer_zip' => $buyer_userRow['user_detail']['zip'],
                'buyer_prefecture_id' => $buyer_userRow['user_detail']['prefecture_id'],
                'buyer_address1' => $buyer_userRow['user_detail']['address1'],
                'buyer_address2' => $buyer_userRow['user_detail']['address2'],
                'buyer_building' => $buyer_userRow['user_detail']['building'],
                'buyer_name' => $buyer_userRow['user_detail']['last_name'] . $buyer_userRow['user_detail']['first_name'],
                'buyer_email' => $buyer_userRow['email'],
                'buyer_tel' => $buyer_userRow['user_detail']['tel'],
                'order_date' => $orderDate->format('Y-m-d'),
                'sellkeep_date' => $orderDate->modify('+3 month')->format('Y-m'),
            ];

            // 注文の作成
            if(!$orderRow = $this->orderService->createItem($orderData)) return false;

            // 決済
            if ($productRows['price'] - $inputData['point'] > 0){
                $orderInfo = " 注文番号：" . $orderRow->id . " 商品番号：" . $inputData['product_id'];
                if(!$rtn = $this->chargeService->chargeOrder($orderRow->id, $buyer_userRow['id'], $orderInfo)) throw new Exception(__('messages.failed_charge'));
            }

            // 決済OKの場合　商品のステータスを変更する
            if(!$this->productService->updateItem(["id"=>$inputData['product_id']], ["status"=>"3"])) throw new Exception(__('messages.failed_charge'));

            //ポイントの使用
            if ($inputData['point'] > "0"){

                $point_type = 7;
                $data['point_type'] = $point_type;
                $data['point_detail'] = config("const.point.$point_type.info");
                $data['point'] = "-".$inputData['point'];
                $data['user_id'] = $inputData["user_id"];
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));


                $where = [];
                $where['expiration_date'] = date('Y-m-d');
                $where['use_flg'] = 0;
                $where['user_id'] = $buyer_userRow['id'];
                $where['per_page'] = "-1";
                $where['order_by_raw'] = "id asc";
                $pointRows = $this->pointService->getList($where);
                $pointRows = $pointRows->toArray();

                $point = $inputData['point'];

                foreach($pointRows as $value){
                    $current_point = $value['point'] - $value['use_point'];
                    if ($point >= $current_point){
                        $point = $point - $current_point;
                        $data = [];
                        $data['use_point'] = $value['use_point'] + $current_point;
                        $data['use_flg'] = "1";
                        $data['use_date'] = date('Y-m-d');
                        if(!$this->pointService->updateItem(["id"=>$value['id']], $data)) throw new Exception(__('messages.failed_charge'));
                    }else{
                        $data = [];
                        $data['use_point'] = $value['use_point'] + $point;
                        $data['use_flg'] = ($data['use_point'] == $value['point']) ? "1" : "0";
                        $data['use_date'] = date('Y-m-d');
                        $point = 0;
                        if(!$this->pointService->updateItem(["id"=>$value['id']], $data)) throw new Exception(__('messages.failed_charge'));
                    }
                    if ($point <= 0) break;
                }
            }


            //匿名発送時のヤマトへの登録
            if ($productRows['shipping_method'] == "1" || $productRows['shipping_method'] == "2" || $productRows['shipping_method'] == "3" ) {

                //都道府県の取得
                $prefecture_seller = $this->prefectureService->getItem(["id"=>$seller_userRow['user_detail']['prefecture_id']]);
                $prefecture_buyer = $this->prefectureService->getItem(["id"=>$buyer_userRow['user_detail']['prefecture_id']]);

                //予約パスワード
                $password="";
                for($i=0;$i<8;$i++){
                    $password.=mt_rand(0,9);
                }

                $data = [];

                $key = date('dvmHYis');
                $day = date('Y-m-d\TH:i:s.vP');
                $cd = base64_encode( hash('sha256', config('const.site.YAMATO_API_KEY') . $key, false));

                $data['auth_key'] = $key;
                $data['auth_cd'] = $cd;
                $data['companyId'] = config('const.site.YAMATO_COMPANY_KEY');
                $data['iraiDatetime'] = $day;
                $data['clientIp'] = '';

                $data['tradingId'] = $orderRow->id;
                $data['reservePwd'] = $password;
                $data['anonymityFlg'] = "1";
                switch ($productRows['shipping_method']){   //伝票区分
                case '1':
                    $data['invoiceKb'] = "13";
                    break;
                case '2':
                    $data['invoiceKb'] = "12";
                    break;
                case '3':
                    $data['invoiceKb'] = "01";
                    break;
                }
                $data['payKb'] = "1";
                $data['dstKb'] = "";
                $data['dstTel1'] = substr($buyer_userRow['user_detail']['tel'], 0, strlen($buyer_userRow['user_detail']['tel']) - 8);
                $data['dstTel2'] = substr($buyer_userRow['user_detail']['tel'], -8, 4);
                $data['dstTel3'] = substr($buyer_userRow['user_detail']['tel'], -4);
                $data['dstZipCd'] = str_replace("-", '' , $buyer_userRow['user_detail']['zip']);
                $data['dstAddress1'] = $prefecture_buyer->prefecture_name;
                $data['dstAddress2'] = mb_convert_kana($buyer_userRow['user_detail']['address1'],"KAN");
                $data['dstAddress3'] = mb_convert_kana($buyer_userRow['user_detail']['address2'],"KAN");
                $data['dstAddress4'] = mb_convert_kana($buyer_userRow['user_detail']['building'],"KAN");
                $data['dstLastNm'] = $buyer_userRow['user_detail']['last_name'];
                $data['dstFirstNm'] = $buyer_userRow['user_detail']['first_name'];

                $data['srcTel1'] = substr($seller_userRow['user_detail']['tel'], 0, strlen($buyer_userRow['user_detail']['tel']) - 8);
                $data['srcTel2'] = substr($seller_userRow['user_detail']['tel'], -8, 4);
                $data['srcTel3'] = substr($seller_userRow['user_detail']['tel'], -4);
                $data['srcZipCd'] = str_replace("-", '' , $seller_userRow['user_detail']['zip']);
                $data['srcAddress1'] = $prefecture_seller->prefecture_name;
                $data['srcAddress2'] = mb_convert_kana($seller_userRow['user_detail']['address1'],"KAN");
                $data['srcAddress3'] = mb_convert_kana($seller_userRow['user_detail']['address2'],"KAN");
                $data['srcAddress4'] = mb_convert_kana($seller_userRow['user_detail']['building'],"KAN");
                $data['srcLastNm'] = $seller_userRow['user_detail']['last_name'];
                $data['srcFirstNm'] = $seller_userRow['user_detail']['first_name'];
                $data['baggDesc2'] = mb_substr($productRows['product_name'], 0, 17);
                $data['shukaFlg'] = "0";

                //ヤマトAPI
                $response = $this->yamto_api($data, config('const.site.YAMATO_API_URL_REGIST'));
                //Log::debug($response);

                if($response['responseHeader']['rtnCd'] != "0" ) throw new Exception(__('messages.faild_yamato_regist'));
                //予約番号更新
                if(!$this->orderService->updateItem(['id' => $orderRow->id], ['yamato_password' => $password, 'yamato_reserve_no'=> $response['invoiceInfoRegistOutput']['tradingInfo']['reserveNo']]))  throw new Exception(__('messages.faild_create'));
            }


            //メール送信
            $maildata['product_name'] = $productRows['product_name'];
            $maildata['price'] = $productRows['price'];
            $maildata['buyer_zip'] = $buyer_userRow['user_detail']['zip'];
            $maildata['buyer_address1'] = $buyer_userRow['user_detail']['address1'];
            $maildata['buyer_address2'] = $buyer_userRow['user_detail']['address2'];
            $maildata['buyer_building'] = $buyer_userRow['user_detail']['building'];
            $maildata['name'] = $buyer_userRow['user_detail']['last_name'] . $buyer_userRow['user_detail']['first_name'];
            $maildata['order_id'] = $orderRow->id;

            // 購入完了後にはメールを送信する。
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail_transaction($buyer_userRow['id'], $buyer_userRow['email'], 6, $maildata)) throw new Exception(__('messages.faild_send_mail'));
            }

            $maildata['name'] = $seller_userRow['user_detail']['last_name'] . $seller_userRow['user_detail']['first_name'];
            //出品者へのメール
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail_transaction($seller_userRow['id'], $seller_userRow['email'], 3, $maildata)) throw new Exception(__('messages.faild_send_mail'));

            }

            //Push通知　購入確定
            $arr[] = $seller_userRow['id'];
            $this->notifyService->sendNotify(NotifyService::KIND_PRODUCT_BUY, $arr, $inputData);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_order_create'));

        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


    //注文データ更新
    public function update(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // 商品の取得
        if(!$orderRow = $this->orderService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // バリデート
        if($val=$this->orderValidator->update($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            $inputData['id'] = $inputData['order_id'];
            $inputData['status'] = $inputData['order_status'];
            if(!$this->orderService->updateItem(["id"=>$inputData['order_id']], $inputData)) throw new Exception(__('messages.faild_update'));

            //詳細の更新
            $orderDetailRow = $this->orderDetailService->getItem(["order_id"=>$inputData['order_id']]);

            if(!empty($orderDetailRow)){
                $inputData['order_id'] = $inputData['order_id'];
                $inputData = $inputData;
                if(!$this->orderDetailService->updateItem(["order_id"=>$inputData['order_id']], $inputData)) throw new Exception(__('messages.faild_update'));
            } else{
                $data= $inputData;
                $data['seller_user_id'] = $orderRow->seller_user_id;
                $data['buyer_user_id'] = $orderRow->buyer_user_id;
                if(!$this->orderDetailService->createItemByOrderId($inputData['order_id'], $data)) throw new Exception(__('messages.faild_update'));
            }

            //注文情報のステータス更新
            $productRow = $this->productService->getItem(['id'=>$inputData['product_id']]);

            $data= [];
            $data['status'] = $inputData['product_status'];
            $data['id'] = $inputData['product_id'];
            if(!$this->productService->updateItemById($inputData['product_id'], $data)) throw new Exception(__('messages.faild_update'));

            //キャンセルの場合、ポイントを戻す
            /*if ($inputData['order_status'] == "9" && $orderRow->point > 0){
                //登録ポイントの付与
                $data['point_type'] = "8";
                $data['point_detail'] = config('const.point.8.info');
                $data['point'] = $orderRow->point;
                $data['user_id'] = $orderRow->buyer_user_id;
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+3 month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
            }*/

            //キャンセルの場合、ヤマトの情報を削除する
            if ($inputData['order_status'] == "9" && $productRow['shipping_method'] <= "3"){
                $data = [];

                $key = date('dvmHYis');
                $day = date('Y-m-d\TH:i:s.vP');
                $cd = base64_encode( hash('sha256', config('const.site.YAMATO_API_KEY') . $key, false));

                $data['auth_key'] = $key;
                $data['auth_cd'] = $cd;
                $data['companyId'] = config('const.site.YAMATO_COMPANY_KEY');
                $data['iraiDatetime'] = $day;
                $data['clientIp'] = '';
                $data['tradingId'] = $orderRow->id;

                //ヤマトAPI
                if(!$this->yamto_api($data, config('const.site.YAMATO_API_URL_CANCEL'))) return false;
            }

            // 受取完了メールを送信する。
            if ($inputData['order_status'] == "3" ){
                if(config('const.site.MAIL_SEND_FLG')) {
                    $productRow = $productRow->toArray();
                    $orderRow = $orderRow->toArray();
                    $maildata['product_name'] = $productRow['product_name'];
                    $maildata['name'] = $orderRow['seller_name'];

                    // 出品者にはメールを送信する。
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($orderRow['seller_user_id'], $orderRow['seller_email'], 4, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }
            }else if($inputData['order_status'] == "4"){
                // 取引完了メールを送信する。
                if(config('const.site.MAIL_SEND_FLG')) {
                    //メール送信
                    $productRow = $productRow->toArray();
                    $orderRow = $orderRow->toArray();
                    $maildata['product_name'] = $productRow['product_name'];
                    $maildata['name'] = $orderRow['buyer_name'];

                    // 購入完了後にはメールを送信する。
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($orderRow['buyer_user_id'], $orderRow['buyer_email'], 9, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }
            }else if( $inputData['order_status'] == "9"){

                //キャンセル処理
                if(config('const.site.MAIL_SEND_FLG')) {

                    //メール送信
                    $productRow = $productRow->toArray();
                    $orderRow = $orderRow->toArray();
                    $maildata['product_name'] = $productRow['product_name'];
                    $maildata['name'] = $orderRow['seller_name'];

                    // 出品者にはメールを送信する。
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($orderRow['seller_user_id'], $orderRow['seller_email'], 10, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }

                    $maildata['name'] = $orderRow['buyer_name'];
                    // 購入完了後にはメールを送信する。
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($orderRow['buyer_user_id'], $orderRow['buyer_email'], 11, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }

                    $maildata['product_name'] = $productRow['product_name'];
                    $maildata['order_id'] = $orderRow['id'];
                    $maildata['product_id'] = $productRow['id'];

                    // 管理者にメールを送信する
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail('emails.order.cancel', config('const.site.SITE_ADMIN_EMAIL'), '['.config('const.site.SITE_NAME').'] 商品がキャンセルされました', $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($orderRow);

        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 売り上げリスト
    public function sellList(Request $request)
    {
        // データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        try {
            // リスト取得
            $orderList = $this->orderService->getList($inputData);
            $orderData = $orderList->toArray();

            $total = 0;
            foreach($orderData['data'] as $value){
                $total = $total + $value['sales_price'];
            }

            // 返す
            return $this->sendResponse(array('sales_total'=>$total));

            } catch (Exception $e) {

            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 月・店舗別売り上げリスト詳細取得 ※未使用
    public function salesDetails(Request $request) {
        // データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->orderValidator->salesDetails($inputData)) return $this->sendValidateErrorResponse($val);

        try {
            //$inputData['status'] = 4;

            // リスト取得
            $orderList = $this->orderService->getSalesGroupDetails($inputData);

            // 返す
            return $this->sendResponse($orderList);
        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件削除
    public function destroy(Request $request, $id)
    {
        // 注文データの取得
        if(!$orderRow = $this->orderService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // DB操作
        DB::beginTransaction();
        try {

            // 削除
            $where['id'] = $id;
            if(!$this->orderService->deleteItem(['id' => $id])) throw new Exception(__('messages.faild_delete'));

            //　詳細情報削除
            if(!$this->orderDetailService->deleteItem(['order_id' => $id])) throw new Exception(__('messages.faild_delete'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse(true);

        } catch (Exception $e) {

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 売上ステータスの更新
    public function updateStatus(Request $request) {

        $userData = $request->user();

        // データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {

            $rtn = true;
            if ($inputData[0]['status'] == "5" ){

                $cnt = 0;
                $sales_price = 0;
                $total_price = 0;
                $system_charge = 0;

                // ステータスを更新する
                foreach($inputData as $value){
                    $orderRow = $this->orderService->getItem(['id'=>$value['id']]);
                    $total_price = $total_price + $orderRow->total_price;
                    $sales_price = $sales_price + $orderRow->sales_price;
                    $system_charge = $system_charge + $orderRow->system_charge;
                    $postage = $system_charge + $orderRow->postage;
                    $cnt++;
                }

                //銀行振込手数料
                $bank_transfer_fee = $this->siteConfigService->getItem(['key_name'=>'bank_transfer_fee']);

                //申請データの作成
                $bank_price = $sales_price - $bank_transfer_fee->value;

                $data = [];
                $data['user_id'] = $userData->id;
                $data['count'] = $cnt;
                $data['bank_price'] = $bank_price;
                $data['total_price'] = $total_price;
                $data['sales_price'] = $sales_price;
                $data['system_charge'] = $system_charge;
                $data['postage'] = $postage;
                $data['payment_request_date'] = $inputData[0]['payment_request_date'];

                if(!$orderhistoryRow = $this->orderPaymentService->createItem($data)) return $this->sendNotFoundErrorResponse();

                // ステータスを更新する
                foreach($inputData as $value){
                    $data = [];
                    $data['id'] = $value['id'];
                    $data['status'] = $value['status'];
                    $data['payment_id'] = $orderhistoryRow->id;
                    $data['payment_request_date'] = $value['payment_request_date'];
                    if(!$orderRow = $this->orderService->updateItem(["id"=>$value['id']], $data)) return $this->sendNotFoundErrorResponse();
                }

                //メール送信
                if ($inputData[0]['status'] == "5" ){
                    //ユーザー情報取得
                    if(!$userRow = $this->userService->getItemByID($userData->id)) return $this->sendNotFoundErrorResponse();
                    $userRow->loadMissing(['userDetail']);
                    $userRow = $userRow->toArray();

                    if(config('const.site.MAIL_SEND_FLG')) {
                        $maildata['date'] = $inputData[0]['payment_request_date'];
                        $maildata['cnt'] = count($inputData);
                        $maildata['name'] = $userRow['user_detail']['last_name'] . $userRow['user_detail']['first_name'];
                        $maildata['bank_price'] = $bank_price;
                        // 売上銀行振込申請メール
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($userRow['id'], $userRow['email'], 12, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                        }
                    }
                }

            }else if ($inputData[0]['status'] == "6" ){

                // ステータスを更新する
                $rtn = [];
                foreach($inputData as $value){
                    $paymentdata = $this->orderPaymentService->getItem(["id"=>$value['id']]);
                    $data = [];
                    $data['csv_flg'] = "1";
                    $data['payment_csv_date'] = date("Y-m-d");
                    if(!$orderhistoryRow = $this->orderPaymentService->updateItem(["id"=>$value['id']], $data)) return $this->sendNotFoundErrorResponse();

                    $orderdata = $this->orderService->getItems(["payment_id"=>$value['id']]);
                    foreach($orderdata as $item){
                        $data = [];
                        $data['id'] = $item['id'];
                        $data['status'] = "7";
                        $data['payment_csv_date'] = date("Y-m-d");
                        if(!$orderhistoryRow = $this->orderService->updateItem(["id"=>$item['id']], $data)) return $this->sendNotFoundErrorResponse();
                    }
                    //csvデータ
                    //csvの出力データ作成
                    if(!$userRow = $this->userService->getitem(["id"=>$paymentdata->user_id])) return $this->sendNotFoundErrorResponse();
                    $userRow->loadMissing(['userDetail']);
                    $userRow = $userRow->toArray();
                    $userRow['bank_price'] = $paymentdata->bank_price;
                    $rtn[] = $userRow;

                }

            }else if ($inputData[0]['status'] == "7" ){

                // ステータスを更新する
                $rtn = [];
                foreach($inputData as $value){
                    $paymentdata = $this->orderPaymentService->getItem(["id"=>$value['id']]);

                    $data = [];
                    $data['csv_flg'] = "2";
                    $data['banktransfer_date'] = date("Y-m-d");
                    if(!$orderhistoryRow = $this->orderPaymentService->updateItem(["id"=>$value['id']], $data)) return $this->sendNotFoundErrorResponse();

                    $orderdata = $this->orderService->getItems(["payment_id"=>$value['id']]);
                    foreach($orderdata as $item){
                        $data = [];
                        $data['id'] = $item['id'];
                        $data['status'] = "8";
                        $data['banktransfer_date'] = date("Y-m-d");
                        if(!$orderhistoryRow = $this->orderService->updateItem(["id"=>$item['id']], $data)) return $this->sendNotFoundErrorResponse();
                    }

                    //ユーザーに振込通知を出す
                    if(!$userRow = $this->userService->getitem(["id"=>$paymentdata->user_id])) return $this->sendNotFoundErrorResponse();
                    $userRow->loadMissing(['userDetail']);
                    $userRow = $userRow->toArray();

                    if(config('const.site.MAIL_SEND_FLG')) {
                        $maildata['date'] = date("Y-m-d");
                        $maildata['price'] = $paymentdata->bank_price;
                        $maildata['name'] = $userRow['user_detail']['last_name'] . $userRow['user_detail']['first_name'];
                        // 売上銀行振込申請メール
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($userRow['id'], $userRow['email'], 13, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                        }
                    }

                }
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($rtn);

        } catch (Exception $e) {

            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }



    public function orderUpdPostage(Request $request) {

        $userData = $request->user();

        // データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->orderValidator->updatePostage($inputData)) return $this->sendValidateErrorResponse($val);

        try {

            //送料更新
            foreach($inputData as $key=>$val){
                //出品者受取金額の変更
                $tmp = $this->orderService->getItemById($key);
                $sales_price = $tmp["total_price"]-$tmp["system_charge"]-$val;
                $this->orderService->updateItem(["id"=>$key],["postage"=>$val,"sales_price"=>$sales_price]);
            }


        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }

        return $this->sendResponse([]);

}

    // 取引評価
    public function getEvaluation(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();


        $data = [];
        // 取得
        if(!$orderRow = $this->orderDetailService->getEvaluation($id, 1)) return $this->sendNotFoundErrorResponse();

        $data['good'] = $orderRow[0]['total'];

        if(!$orderRow = $this->orderDetailService->getEvaluation($id, 2)) return $this->sendNotFoundErrorResponse();
        $data['normal'] = $orderRow[0]['total'];

        if(!$orderRow = $this->orderDetailService->getEvaluation($id, 3)) return $this->sendNotFoundErrorResponse();
        $data['bad'] = $orderRow[0]['total'];

        // 返す
        return $this->sendResponse($data);

    }

    // 月・店舗別検索機能付き売り上げリスト
    public function adminsalesList(Request $request) {
        // データの取得
        $inputData = $request->all();
        // バリデート
    //    if($val=$this->orderValidator->salesList($inputData)) return $this->sendValidateErrorResponse($val);

        try {

            // リスト取得
            $orderList = $this->orderService->getSalesGroupList($inputData);

            // 返す
            return $this->sendResponse($orderList);

        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


    // 月・店舗別検索機能付き売り上げリスト
    public function salesList(Request $request) {
        // データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->orderValidator->salesList($inputData)) return $this->sendValidateErrorResponse($val);

        try {

            // リスト取得
            $orderList = $this->orderService->getSalesGroupList($inputData);

            // 返す
            return $this->sendResponse($orderList);

        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 銀行申請
    public function getpayment(Request $request) {
        // データの取得
        $inputData = $request->all();

        try {
            // リスト取得
            $orderList = $this->orderPaymentService->getList($inputData);

            // 返す
            return $this->sendResponse($orderList);

        } catch (Exception $e) {
            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


    //ヤマトとのAPI
    public function yamto_api($data, $url){

        Log::debug($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // POSTする値
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                // 実行結果を文字列で返す
        $xml = curl_exec($ch);
        Log::debug(curl_error($ch));
        curl_close($ch);
        $xmlObj = simplexml_load_string($xml);
        $json = json_encode($xmlObj);
        $response = json_decode($json, true);

        Log::debug($response);

        //$response = array( 'invoiceInfoRegistOutput'=> array( 'tradingInfo'=> array ( 'reserveDt' =>'2021-08-25T16:50:15.000+09:00', 'reserveNo'=>'3777337122')),
        // 'responseHeader'=>array('rtnCd'=>'0','rtnDatetime'=>'3777337122'));

        //return  $rtn;
        return  $response;
    }


}
