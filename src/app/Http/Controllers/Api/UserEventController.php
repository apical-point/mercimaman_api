<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\UserEventService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\ChargeService;
use App\Services\PointService;
use App\Services\OrderPaymentService;
use App\Services\SiteConfigService;
use App\Services\UserProfileService;
use App\Services\UserDetailService;
use App\Services\BlockService;
use App\Services\NgWordsService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class UserEventController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/content/';
    private static $saveFileUrl = 'content/';

    // サービス
    protected $userEventService;
    protected $userService;
    protected $mailService;
    protected $fileService;
    protected $upFileService;
    protected $chargeService;
    protected $pointService;
    protected $SiteConfigService;
    protected $orderPaymentService;
    protected $userProfileService;
    protected $userDetailService;
    protected $blockService;
    protected $ngWordsService;

    public function __construct(
        // サービス
        UserEventService $userEventService,
        UserService $userService,
        UserProfileService $userProfileService,
        UserDetailService $userDetailService,
        MailService $mailService,
        FileService $fileService,
        UpFileService $upFileService,
        PointService $pointService,
        ChargeService $chargeService,
        OrderPaymentService $orderPaymentService,
        SiteConfigService $siteConfigService,
        BlockService $blockService,
        NgWordsService $ngWordsService
    ){
        parent::__construct();

        // サービス
        $this->userEventService = $userEventService;
        $this->userService = $userService;
        $this->userProfileService = $userProfileService;
        $this->userDetailService = $userDetailService;

        $this->mailService = $mailService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;
        $this->chargeService = $chargeService;
        $this->pointService = $pointService;
        $this->orderPaymentService = $orderPaymentService;
        $this->siteConfigService = $siteConfigService;
        $this->blockService = $blockService;
        $this->ngWordsService = $ngWordsService;
    }

    //一覧取得
    public function index(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        //$userData = $request->user();

        try {

            //ブロックユーザーの取得
            if(!empty($inputData['login_user_id'])){
                $block_response = $this->blockService->getItems(['user_id' => $inputData['login_user_id']])->toArray();
                $block_array = array_column($block_response, 'to_user_id');

                $block_response_to = $this->blockService->getItems(['to_user_id' => $inputData['login_user_id']])->toArray();
                $block_array_to = array_column($block_response_to, 'user_id');

                $inputData['block_users'] = array_merge($block_array, $block_array_to);

            }

            //検索情報取得
            $arr = $this->userEventService->getList($inputData);
            $arr->loadMissing(['mainImage']);

            return $this->sendResponse($arr);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }


    // 指定したIDの情報を返す
    // 自身の情報を取得する場合はgetMyData()を利用
    public function show(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        $user = $this->userEventService->getItemById(["id" => $id]);
        $user->loadMissing(['mainImage']);


        if(!$user){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }


        //イベント投稿ユーザーのimage_id
        if($user->user_id != 0){

            //会員種別
            $userArr = $this->userService->getItemById($user->user_id);
            if($userArr->user_type == 0){
                $userProf = $this->userProfileService->getItem(["user_id"=>$user->user_id]);
                $user->image_id = $userProf->image_id;
            }

            $user->email = $userArr->email;

        }
        else{
            $user->email = config('const.site.SITE_ADMIN_EMAIL');
        }

        // 返す
        return $this->sendResponse($user, __('messages.success'));

    }

    //参加イベント登録処理
    public function store(Request $request)
    {


        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        $val = [];
        $v = Validator::make($inputData, ValidateCheckArray::$userEvent);

        if ($v->fails()) $val = $v->errors()->toArray();

        $response=$this->ngWordsService->checkNgWords(['word' => $inputData['event_name']]);
        if(!$response['success']){
            return $this->sendErrorResponse(['含まれてはいけない単語が含まれています。「'.$response['data'].'」'],  __('含まれてはいけない単語が含まれています。「'.$response['data'].'」'));
        }

        //画像のチェック
        if (!isset($inputData["up_file"]) || (isset($inputData["up_file"]) && $inputData["up_file"] == "") ){
            $val['up_file'] = __('messages.event_file');
        }
        if(!empty($val)) return $this->sendValidateErrorResponse($val);

        //if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->userEventService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
            \Log::debug($inputData);
            // メイン画像があれば保存
            if(!empty($inputData['up_file'])) {
                \Log::debug("画像？");
                foreach($inputData['up_file'] as $key=>$val){
                    // メイン画像ファイルの保存
                    $createMainFilePath = $this->fileService->saveImageByBase64($val, self::$saveFileDir);
                    // メイン画像ファイルのデータ取得
                    $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);
                    // メイン画像ファイルのデータをデータベースに保存する
                    //$status = ($key == 1) ? 1 : 0;
                    $wh["name"] =  $upMainFileData["name"];
                    $upMainFileData["v_order"] = $key;
                    if(!$this->userEventService->updateOrCreateImageData($newData->id, $upMainFileData, $wh)) throw new Exception(__('messages.faild_create'));
                }

/*
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->userEventService->updateOrCreateImageData($newData->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
*/
            }

            $newData->loadMissing(['mainImage']);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newData);

        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

     //指定IDを更新する
     public function update(Request $request, $id)
    {


        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // バリデート
        $v = Validator::make($inputData, ValidateCheckArray::$userEvent);

        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            $arr = $this->userEventService->updateItem(['id'=>$id], $inputData);

            \Log::debug($inputData);
            // 削除のチェックがあれば削除を行う。
            if(!empty($inputData["del"])){
                foreach($inputData["del"] as $key =>$val){
                    if(!$this->upFileService->deleteItem(["id" => $key])) throw new Exception(__('messages.faild_update'));
                    $this->fileService->deleteImage($val);//ファイルの削除
                }
            }
\Log::debug($inputData);
            // メイン画像があれば保存
            if(!empty($inputData['up_file'])) {

                foreach($inputData['up_file'] as $key=>$val){
                    // メイン画像ファイルの保存
                    $createMainFilePath = $this->fileService->saveImageByBase64($val, self::$saveFileDir);
                    // メイン画像ファイルのデータ取得
                    $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);
                    // メイン画像ファイルのデータをデータベースに保存する
                    //$wh["status"] = ($key == 1) ? 1 : 0;
                    if(!empty($inputData['image_id'][$key])){
                        $wh["id"] = $inputData['image_id'][$key];
                    }
                    else{
                        $wh["name"] =  $upMainFileData["name"];
                    }
                    $upMainFileData["v_order"] =  $key;
                    if(!$this->userEventService->updateOrCreateImageData($id, $upMainFileData, $wh)) throw new Exception(__('messages.faild_create'));
                }

/*
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->userEventService->updateOrCreateImageData($id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
*/
            }
            // コミット
            DB::commit();

            $arr->loadMissing(['mainImage']);

            // 返す
            return $this->sendResponse($arr, __('messages.success'));

        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

     //削除
     public function destroy(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         try {

             if(!$this->userEventService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));


             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse();
         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }
     }

     //応募処理  決済が無くなったので現在は未使用
     public function userEventStore(Request $request){

         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         $userData =$request->user();

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();

             // 登録処理
             if(!$newData=$this->chargeService->createUserEvent($inputData, $userData->id)) throw new Exception(__('messages.faild_create_event_oubo'));

             //ポイントの使用
             if ($inputData['point'] > "0"){

                 $point_type = config('const.point_id.PRESENT_POINT_ID')-1;
                 $data['point_type'] = $point_type;
                 $data['point_detail'] = $siteConfigList[$point_type]["description"];
                 $data['point'] = "-".$inputData['point'];
                 $data['user_id'] = $userData->id;
                 $data['point_date'] = date("Y-m-d");
                 $date = date_create(date("Y-m-d"));
                 if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));

                 $where = [];
                 $where['expiration_date'] = date('Y-m-d');
                 $where['use_flg'] = 0;
                 $where['user_id'] = $userData->id;
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
                         if(!$this->pointService->updateItem(["id"=>$value['id']], $data)) throw new Exception(__('messages.faild_create_event_oubo'));
                     }else{
                         $data = [];
                         $data['use_point'] = $value['use_point'] + $point;
                         $data['use_flg'] = ($data['use_point'] == $value['point']) ? "1" : "0";
                         $data['use_date'] = date('Y-m-d');
                         $point = 0;
                         if(!$this->pointService->updateItem(["id"=>$value['id']], $data)) throw new Exception(__('messages.faild_create_event_oubo'));
                     }
                     if ($point <= 0) break;
                 }
             }

             //---決済処理----
             \Log::debug("決済開始");
             if(!$this->chargeService->chargeUserEvent($inputData, $userData->id)){
                 \Log::debug("応募決済エラーのためrollBack");
                 throw new Exception(__('messages.failed_charge_event_oubo'));
             }

             // コミット
             DB::commit();

             //イベント登録者へのメール
             if(config('const.site.MAIL_SEND_FLG')) {

                //参加申込者情報
                 $userProfileArr = $this->userProfileService->getItem(["user_id" => $userData["id"]]);
                 $maildata['nickname'] = $userProfileArr->nickname;

                 //イベント情報取得
                 $eventArr = $this->userEventService->getItem(["id"=>$inputData["id"]]);

                 $maildata["event_name"] = $eventArr->event_name;

                 //イベント登録者の情報取得
                 if($eventArr->user_id != 0){
                    $eventUserRow = $this->userService->getItemById($eventArr->user_id);
                    $email = $eventUserRow->email;

                    if($eventUserRow->user_type == 1){
                        //イベント会員は名前が無いのでメールアドレスに。
                        $maildata["name"] = $eventUserRow->email;
                    }
                    else{
                        $eventDetailUserRow = $this->userDetailService->getItem(["user_id"=>$eventArr->user_id]);
                        $maildata["name"] = $eventDetailUserRow->last_name.$eventDetailUserRow->first_name;
                    }

                  }
                 else{
                     //運営
                     $email = config('const.site.SITE_ADMIN_EMAIL');
                     $maildata["name"] = "運営";
                 }

                 $mail_no = 42;
//                 if(!$this->mailService->sendMail_transaction($eventArr->user_id, $email, $mail_no, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                 $this->mailService->sendMail_transaction($eventArr->user_id, $email, $mail_no, $maildata);

                 //-----運営にも送る-------
                 //運営送付用
                 if($eventArr->user_id != 0){
                    $user_id = 0;
                     $email = config('const.site.SITE_ADMIN_EMAIL');
                     $maildata["name"] = "運営";
                     //if(!$this->mailService->sendMail_transaction($user_id, $email, $mail_no, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                     $this->mailService->sendMail_transaction($user_id, $email, $mail_no, $maildata);
                 }
             }


             // 返す
             return $this->sendResponse($newData);

         } catch (Exception $e) {
             Log::debug("エラー");
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }


     }


     //一覧取得
     public function getEventMemberList(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // ユーザーデータの取得
         //$userData = $request->user();

         try {

             //検索情報取得
             $arr = $this->userEventService->getEventMemberList($inputData);

             return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }
     //参加者　指定IDを更新する
     public function userEventMemberUpdate(Request $request, $id)
     {

         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         // バリデート

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

            $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();
            
            $expiration_date = $siteConfigList[16]["value"];

            if(!empty($inputData["status"]) && $inputData["status"] == 99){
                $arr = $this->chargeService->cancelUserEvent($id);

                //使用したポイントがあれば戻す
                if ($inputData['point'] > "0"){
                    $point_type = 16;
                    $data['point_type'] = $point_type;
                    $data['point_detail'] = 'イベントキャンセルにおけるポイント返還';
                    $data['point'] = $inputData['point'];
                    $data['user_id'] = $inputData["user_id"];
                    $data['point_date'] = date("Y-m-d");
                    $date = date_create(date("Y-m-d"));
                    $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
                    if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
                }
            }
            else{
            $arr = $this->userEventService->updateEventMemberItem(['id'=>$id], $inputData);
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($arr, __('messages.success'));

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

     // 売上ステータスの更新
     public function updateStatus(Request $request) {
\Log::debug("イベント売上ステータス変更");
         $userData = $request->user();

         // データの取得
         $inputData = $request->all();
\Log::debug($inputData);
         // DB操作
         DB::beginTransaction();
         try {

             $rtn = true;
             if ($inputData[0]['pay_status'] == "5" ){

                 $cnt = 0;
                 $sales_price = 0;
                 $total_price = 0;
                 $system_charge = 0;

                 // ステータスを更新する
                 foreach($inputData as $value){
                     $orderRow = $this->userEventService->getEventMemberItem(['id'=>$value['id']]);
                     $total_price = $total_price + $orderRow->pay_price;
                     $sales_price = $sales_price + $orderRow->price;
                     $system_charge = $system_charge + $orderRow->system_price;
                     //$postage = $system_charge + $orderRow->postage;
                     $cnt++;
                 }

                 //銀行振込手数料
                 $bank_transfer_fee = $this->siteConfigService->getItem(['key_name'=>'bank_transfer_fee']);

                 //申請データの作成
                 $bank_price = $total_price - $bank_transfer_fee->value;

                 $data = [];
                 $data['user_id'] = $userData->id;
                 $data['count'] = $cnt;
                 $data['bank_price'] = $bank_price;
                 $data['total_price'] = $total_price;
                 $data['sales_price'] = $sales_price;
                 $data['system_charge'] = $system_charge;
                 //$data['postage'] = $postage;
                 $data['payment_request_date'] = $inputData[0]['payment_request_date'];
                 $data['payment_flg'] = 1;//イベント売上
\Log::debug($data);
\Log::debug("orderPaymentService");
                 if(!$orderhistoryRow = $this->orderPaymentService->createItem($data)) return $this->sendNotFoundErrorResponse();

                 // ステータスを更新する
                 \Log::debug("ステータス更新");
                 foreach($inputData as $value){
                     $data = [];
                     $data['id'] = $value['id'];
                     $data['pay_status'] = $value['pay_status'];
                     $data['payment_id'] = $orderhistoryRow->id;
                     $data['payment_request_date'] = $value['payment_request_date'];

                     if(!$orderRow = $this->userEventService->updateEventMemberItem(["id"=>$value['id']], $data)) return $this->sendNotFoundErrorResponse();
                 }
                 \Log::debug("メール送信");
                 //メール送信
                 if ($inputData[0]['pay_status'] == "5" ){
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
                 \Log::debug("メール送信完了");
             }

             else if ($inputData[0]['pay_status'] == "6" ){

                 // ステータスを更新する
                 $rtn = [];
                 foreach($inputData as $value){
                     \Log::debug($value);
                     $paymentdata = $this->orderPaymentService->getItem(["id"=>$value['id']]);
                     $data = [];
                     $data['csv_flg'] = "1";
                     $data['payment_csv_date'] = date("Y-m-d");
                     if(!$orderhistoryRow = $this->orderPaymentService->updateItem(["id"=>$value['id']], $data)) return $this->sendNotFoundErrorResponse();

                     $orderdata = $this->userEventService->getEventMemberItems(["payment_id"=>$value['id']]);
                     \Log::debug($orderdata);
                     foreach($orderdata as $item){
                         $data = [];
                         $data['id'] = $item->id;
                         $data['pay_status'] = "7";
                         $data['payment_csv_date'] = date("Y-m-d");
                         if(!$orderhistoryRow = $this->userEventService->updateEventMemberItem(["id"=>$item['id']], $data)) return $this->sendNotFoundErrorResponse();
                     }
                     //csvデータ
                     //csvの出力データ作成
                     if(!$userRow = $this->userService->getitem(["id"=>$paymentdata->user_id])) return $this->sendNotFoundErrorResponse();
                     $userRow->loadMissing(['userDetail']);
                     $userRow = $userRow->toArray();
                     $userRow['bank_price'] = $paymentdata->bank_price;
                     $rtn[] = $userRow;

                 }

             }

             else if ($inputData[0]['pay_status'] == "7" ){

                 // ステータスを更新する
                 $rtn = [];
                 foreach($inputData as $value){
                     $paymentdata = $this->orderPaymentService->getItem(["id"=>$value['id']]);

                     $data = [];
                     $data['csv_flg'] = "2";
                     $data['banktransfer_date'] = date("Y-m-d");
                     if(!$orderhistoryRow = $this->orderPaymentService->updateItem(["id"=>$value['id']], $data)) return $this->sendNotFoundErrorResponse();

                     $orderdata = $this->userEventService->getEventMemberItems(["payment_id"=>$value['id']]);
                     foreach($orderdata as $item){
                         $data = [];
                         $data['id'] = $item['id'];
                         $data['pay_status'] = "8";
                         $data['banktransfer_date'] = date("Y-m-d");
                         if(!$orderhistoryRow = $this->userEventService->updateEventMemberItem(["id"=>$item['id']], $data)) return $this->sendNotFoundErrorResponse();
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

     //管理側　指定年の各月の手数料売上取得
     public function getEventSales(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         try {

             //検索情報取得
             $arr = $this->userEventService->getEventSales($inputData);

             return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }

/*
     //ちょっとイレギュラーだが・・TOPページスライダー画像（もともとイベントのバナーの箇所に入れる）
      public function updateSlider(Request $request)
     {

         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

              // 削除のチェックがあれば削除を行う。
             if(!empty($inputData["del"])){
                 foreach($inputData["del"] as $key =>$val){
                     if(!$this->upFileService->deleteItem(["id" => $key])) throw new Exception(__('messages.faild_update'));
                     $this->fileService->deleteImage($val);//ファイルの削除
                 }
             }
             \Log::debug("TopSlider");
             \Log::debug($inputData);
             // 画像保存
             if(!empty($inputData['up_file'])) {

                 foreach($inputData['up_file'] as $key=>$val){
                     // メイン画像ファイルの保存
                     $createMainFilePath = $this->fileService->saveImageByBase64($val, self::$saveFileDir);
                     // メイン画像ファイルのデータ取得
                     $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);
                     // メイン画像ファイルのデータをデータベースに保存する

                     $upMainFileData["up_file_able_id"] = "-1";
                     if(!$this->upFileService->createItem($upMainFileData)) throw new Exception(__('messages.faild_create'));

                 }
             }
             // コミット
             DB::commit();

             //$arr->loadMissing(['mainImage']);

             // 返す
             return $this->sendResponse([], __('messages.success'));

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

     public function indexSlider(Request $request)
     {

         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         try {
             \Log::debug("indexSlider");
             \Log::debug($inputData);
             //検索情報取得
             $arr = $this->upFileService->getItems($inputData);

             return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }


     }

*/


}
