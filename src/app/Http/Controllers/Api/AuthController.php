<?php namespace App\Http\Controllers\Api;

// ベース
// use App\Http\Controllers\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\UserService;
use App\Services\UserDetailService;
use App\Services\UserProfileService;
use App\Services\UserFavoriteService;
use App\Services\OrderService;
use App\Services\OrderPaymentService;
use App\Services\ProductService;
use App\Services\ContentMessageService;
use App\Services\ProductMessageService;
use App\Services\SiteConfigService;
use App\Services\PointService;
use App\Services\MessageService;
use App\Services\MailService;
use App\Services\FileService;
use App\Services\UpFileService;


// モデル
use App\Repositories\Eloquent\Models\Order;

// バリデート
use App\Validators\Api\AuthValidator;

// メール
use App\Mails\Api\AuthMail;

class AuthController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/user/';
    private static $saveFileUrl = 'user/';

    // サービス
    protected $userService;
    protected $userDetailService;
    protected $userProfileService;
    protected $userFavoriteService;
    protected $orderService;
    protected $orderPaymentService;
    protected $productService;
    protected $contentMessageService;
    protected $productMessageService;
    protected $siteConfigService;
    protected $pointService;
    protected $messageService;
    protected $mailService;
    protected $fileService;
    protected $upFileService;

    // バリデート
    protected $authValidator;

    public function __construct(
        // サービス
        UserService $userService,
        UserDetailService $userDetailService,
        UserProfileService $userProfileService,
        UserFavoriteService $userFavoriteService,
        OrderService $orderService,
        OrderPaymentService $orderPaymentService,
        ProductService $productService,
        ContentMessageService $contentMessageService,
        ProductMessageService $productMessageService,
        SiteConfigService $siteConfigService,
        PointService $pointService,
        MessageService $messageService,
        MailService $mailService,
        FileService $fileService,
        UpFileService $upFileService,

        // バリデート
        AuthValidator $authValidator

    ){
        parent::__construct();

        // サービス
        $this->userService = $userService;
        $this->userDetailService = $userDetailService;
        $this->userProfileService = $userProfileService;
        $this->userFavoriteService = $userFavoriteService;
        $this->orderService = $orderService;
        $this->orderPaymentService = $orderPaymentService;
        $this->productService = $productService;
        $this->contentMessageService = $contentMessageService;
        $this->productMessageService = $productMessageService;
        $this->siteConfigService = $siteConfigService;
        $this->pointService = $pointService;
        $this->messageService = $messageService;
        $this->mailService = $mailService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;


        // バリデート
        $this->authValidator = $authValidator;

    }

    // 仮登録
    public function entry(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->authValidator->entry($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {

            // 仮登録済みのメールアドレスが存在していればそのデータを取得
            if (!$newUser = $this->userService->getItem(['email' => $inputData['email'], 'status' => 0])) {
                // 仮登録
                if(!$newUser=$this->userService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
            }

            // パラメータ作成
            $param = $this->userService->getRandomParam($newUser->id);

            // 登録期限の取得
            $registLimit = $this->userService->getRegistLimit(config('const.site.ENTRY_LIMIT_HOUR'));

            // パラメータの更新
            if(!$this->userService->updateItemById($newUser->id, ['status' => 0, 'param' => $param, 'regist_limit' => $registLimit])) throw new Exception(__('messages.faild_create'));

            // 再取得
            $newUser = $this->userService->getItemById($newUser->id);

            // 登録者にメールを送信する
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail('emails.auth.entry', $newUser->email, '['.config('const.site.SITE_NAME').'] 仮登録ありがとうございます', $newUser)) throw new Exception(__('messages.faild_send_mail'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newUser->only(['email']), __('messages.success_entry'));
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    //イベントユーザー会員新規登録
    public function registEventUser(Request $request)
    {

        // 入力データの取得
        $inputData = $request->all();
        // DB操作
        DB::beginTransaction();
        try {


            // 仮登録済みのメールアドレスが存在していればそのデータを削除
            if ($newUser = $this->userService->getItem(['email' => $inputData['email'], 'status' => 0])) {
                if(!$this->userService->deleteItem(['email' => $inputData['email'], 'status' => 0]))throw new Exception(__('messages.faild_create'));
            }
            $in["email"] = $inputData["email"];
            $in["user_type"] = $inputData["user_type"];
            if(!$newUser=$this->userService->createItem($in)) throw new Exception(__('messages.faild_create'));

            // パスワードの更新
            if(!$this->userService->updatePasswordById($newUser->id, $inputData['password'])) throw new Exception(__('messages.faild_regist'));

            // パラメータ作成
            $param = $this->userService->getRandomParam($newUser->id);
            // パラメータの更新
            if(!$this->userService->updateItemById($newUser->id, ['param' => $param])) throw new Exception(__('messages.faild_create'));

            // 本登録済みに変更
            if(!$this->userService->updateMainRegistById($newUser->id)) throw new Exception(__('messages.faild_regist'));

            // パラメーターのリセット
            //if(!$this->userService->resetParam($userRow->id)) throw new Exception(__('messages.faild_regist'));

            // ユーザー詳細の登録
            if(!$this->userDetailService->createItemByUserId($newUser->id, $inputData)) throw new Exception(__('messages.faild_regist'));

            // ユーザープロフィールの登録
            //if(!$this->userProfileService->createItemByUserId($userRow->id, $inputData)) throw new Exception(__('messages.faild_regist'));

            // 再取得
            $userRow = $this->userService->getItemById($newUser->id);


            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($userRow , __('messages.success_regist_entry'));

        } catch (Exception $e) {

            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }


    // 新規登録
    public function regist(Request $request, $param)
    {
        // パラメーターで取得
        if(!$userRow = $this->userService->getItem(['param'=>$param])) return $this->sendErrorResponse([], __('messages.not_entry'));

        // すでに本登録済みの場合
        if($userRow->is_main_regist) return $this->sendErrorResponse([], __('messages.faild_already_registered'));

        // 仮登録期限が過ぎていた場合
        if(!$this->userService->isRegistLimit($userRow->regist_limit)) return $this->sendErrorResponse([], __('messages.the_temporary_registration_deadline_has_passed'));

        // 入力データの取得
        $inputData = $request->all();

        // バリデート

        if($val=$this->authValidator->regist($userRow->id, $inputData)) return $this->sendValidateErrorResponse($val);

        //チェックのみの場合はここで返す
        if ($inputData['chkonly']){
            return $this->sendResponse();
        }

        // DB操作
        DB::beginTransaction();
        try {

            // パスワードの更新
            if(!$this->userService->updatePasswordById($userRow->id, $inputData['password'])) throw new Exception(__('messages.faild_regist'));

            // 本登録済みに変更
            if(!$this->userService->updateMainRegistById($userRow->id)) throw new Exception(__('messages.faild_regist'));


            // パラメーターのリセット
            if(!$this->userService->resetParam($userRow->id)) throw new Exception(__('messages.faild_regist'));

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->userService->updateOrCreateImageData($userRow->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));

                $inputData['identification'] = "1";

            }

            // 画像があれば保存
            if(!empty($inputData['up_sub_file'])){

                // 画像ファイルの保存
                $createFilePaths = $this->fileService->saveImageByBase64($inputData['up_sub_file'], self::$saveFileDir );

                // 画像ファイルのデータ取得
                $upFileData = $this->upFileService->getFileData($createFilePaths, self::$saveFileUrl );

                // 画像ファイルのデータをデータベースに保存する
                if(!$this->userService->updateOrCreateImageData($userRow->id, $upFileData, "0")) throw new Exception(__('messages.faild_create'));

                $inputData['identification'] = "1";
            }


            // ユーザー詳細の登録
            if(!$this->userDetailService->createItemByUserId($userRow->id, $inputData)) throw new Exception(__('messages.faild_regist'));

            // ユーザープロフィールの登録
            if(!$this->userProfileService->createItemByUserId($userRow->id, $inputData)) throw new Exception(__('messages.faild_regist'));

            // 再取得
            $userRow = $this->userService->getItemById($userRow->id);

            $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();
            
            $expiration_date = $siteConfigList[16]["value"];

            //登録ポイントの付与
            $regist_point_id = config('const.point_id.REGIST_POINT_ID')-1;
            $data['point_detail'] = $siteConfigList[$regist_point_id]["description"];
            $data['point'] = $siteConfigList[$regist_point_id]["value"];

            $data['user_id'] = $userRow->id;
            $data['point_date'] = date("Y-m-d");
            $date = date_create(date("Y-m-d"));
            $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
            if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));

            //お友達紹介
            if (!empty($inputData['referral_code'])){
                $intro_point_id = config('const.point_id.INTRO_POINT_ID')-1;
                $data['point_detail'] = $siteConfigList[$intro_point_id]["description"];
                $data['point'] = $siteConfigList[$intro_point_id]["value"];

                $data['user_id'] = $userRow->id;
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));

                $data['point_detail'] = $siteConfigList[$intro_point_id]["description"];
                $intro_point = $this->siteConfigService->getItem(['key_name'=>'intro_to_point']);
                $data['point'] = $intro_point->value;

                //$data['point'] = config('const.point.5.point');
                $data['user_id'] = $inputData['referral_code'];
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
            }

            // 登録者にはメールを送信する。
            $maildata['name'] = $inputData['last_name'] . $inputData['first_name'];
            //出品者へのメール
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail_transaction($userRow->id, $userRow->email, 1, $maildata)) throw new Exception(__('messages.faild_send_mail'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($userRow , __('messages.success_regist_entry'));

        } catch (Exception $e) {

            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }


    // 自分のデータ取得
    public function getMyData(Request $request)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 再取得
        $userRow = $this->userService->getItemById($userData->id);

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail','userProfile']);

        // 返す
        return $this->sendResponse($userRow);
    }


    // StripeIdの処理
    public function setStripeId(Request $request) {
        // ユーザーデータの取得
        $userData = $request->user();

        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->authValidator->setStripeId($userData->id, $inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザー詳細更新
            if(!$this->userDetailService->updateItemByUserId($userData->id, $inputData)) throw new Exception(__('messages.faild_update'));

            // 再取得
            $userRow = $this->userService->getItemById($userData->id);

            // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
            $userRow->loadMissing(['userDetail']);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($userRow, __('messages.success_update_auth'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


    // バリデーション
    public function validateEmail(Request $request) {
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->authValidator->authEmail($inputData)) return $this->sendValidateErrorResponse($val);

        // 返す
        return $this->sendResponse([], __('messages.success_validateEmail'));
    }

    // パスワードの更新
    public function updatePassword(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // ハッシュされたパスワードを取得
        if(!$hashedPassword = $this->userService->getHashedPasswordById($userData->id)) return $this->sendErrorResponse([], __('messages.faild_update'));

        // バリデート
        if($val=$this->authValidator->updatePassword($inputData, $hashedPassword)) return $this->sendValidateErrorResponse($val);

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {
            // パス変処理
            if(!$this->userService->updatePasswordById($userData->id, $inputData['password'])) throw new Exception(__('messages.faild_update'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_update_password'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 退会処理
    public function withdrawal(Request $request) {

        $inputData = $request->all();

        // ユーザーデータの取得
        //$userData = $request->user();
        // 再取得
        $userRow = $this->userService->getItemById($inputData['user_id']);

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail','userProfile','userFavorite']);
        $user = $userRow->toArray();

        // バリデート
        //$inputData['user_id'] = $userData['id'];
        if($val=$this->authValidator->withdrawal($inputData, $user)) return $this->sendValidateErrorResponse($val);


        // DB操作
        DB::beginTransaction();
        try {

            // ステータス更新
            $inputData['status'] = "9";
            $inputData['email'] = "*" . $userRow->email;
            if(!$this->userService->updateItemById($user['id'], $inputData)) throw new Exception(__('messages.faild_update'));

            // 退会理由更新
            if(!$this->userDetailService->updateItemByUserId($user['id'], $inputData)) throw new Exception(__('messages.faild_update'));

            // ユーザーお気に入り削除 データは物理削除
            if(!empty($user['user_favorite'])){
                if(!$this->userFavoriteService->deleteItems(['user_id'=>$user['id']])) throw new Exception(__('messages.faild_withdrawal'));
            }

            // 商品を非表示に変更する
            $data = $this->productService->getItems(['user_id'=>$user['id']]);
            $data =  $data->toArray();
            foreach($data as $value){
                if(!$this->productService->updateItem(['id'=>$value['id']],['open_flg'=>0])) throw new Exception(__('messages.faild_withdrawal'));
            }

            // 商品のメッセージ
            $data = $this->productMessageService->getItems(['user_id'=>$user['id']]);
            $data =  $data->toArray();
            foreach($data as $value){
                if(!$this->productMessageService->updateItem(['id'=>$value['id']],['open_flg'=>0])) throw new Exception(__('messages.faild_withdrawal'));
            }

            // コンテンツのメッセージ
            $data = $this->contentMessageService->getItems(['user_id'=>$user['id']]);
            $data =  $data->toArray();
            foreach($data as $value){
                if(!$this->contentMessageService->updateItem(['id'=>$value['id']],['open_flg'=>0])) throw new Exception(__('messages.faild_withdrawal'));
            }

            // チャットを非表示に変更する
            $datafrom = $this->messageService->getItems(['user_from_id'=>$user['id']]);
            $datato = $this->messageService->getItems(['user_to_id'=>$user['id']]);
            $datafrom =  $datafrom->toArray();
            foreach($datafrom as $value){
                if(!$this->messageService->updateItem(['id'=>$value['id']],['open_flg'=>0])) throw new Exception(__('messages.faild_withdrawal'));
            }
            $datato =  $datato->toArray();
            foreach($datato as $value){
                if(!$this->messageService->updateItem(['id'=>$value['id']],['open_flg'=>0])) throw new Exception(__('messages.faild_withdrawal'));
            }

            //売上金申請
            $data = $this->orderService->getItems(['seller_user_id'=>$user['id'],'status'=>4]);
            $data =  $data->toArray();
            if(count($data) > 0){
                $cnt = 0;
                $sales_price = 0;
                $total_price = 0;
                $system_charge = 0;

                $data =  $data->toArray();
                foreach($data as $value){
                    $total_price = $total_price + $value['total_price'];
                    $sales_price = $sales_price + $value['sales_price'];
                    $system_charge = $system_charge + $value['system_charge'];
                    $cnt++;
                    if(!$this->orderService->updateItem(['id'=>$value['id']],['status'=>6])) throw new Exception(__('messages.faild_withdrawal'));
                }

                //銀行振込手数料
                $bank_transfer_fee = $this->siteConfigService->getItem(['key_name'=>'bank_transfer_fee']);
                $bank_price = $sales_price - $bank_transfer_fee->value;

                $data = [];
                $data['user_id'] = $userData->id;
                $data['count'] = $cnt;
                $data['bank_price'] = $bank_price;
                $data['total_price'] = $sales_price;
                $data['sales_price'] = $total_price;
                $data['system_charge'] = $system_charge;
                $data['payment_request_date'] = date("Y-m-d");

                if(!$orderhistoryRow = $this->orderPaymentService->createItem($data)) return $this->sendNotFoundErrorResponse();
            }

            // 登録者にはメールを送信する。
            $maildata['name'] = $user['user_detail']['last_name'] . $user['user_detail']['first_name'];
            //出品者へのメール
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail_transaction($user['id'], $user['email'], 2, $maildata)) throw new Exception(__('messages.faild_send_mail'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_withdrawal'));

        } catch (Exception $e) {

            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // パラメータでエントリーユーザー取得
    public function getEntryUserByParam(Request $request, $param) {
        // パラメーターで取得
        if(!$userRow = $this->userService->getItem(['param'=>$param])) return $this->sendErrorResponse([], __('messages.not_entry'));

        // すでに本登録済みの場合
        if($userRow->is_main_regist) return $this->sendErrorResponse([], __('messages.faild_already_registered'));

        // 仮登録期限が過ぎていた場合
        if(!$this->userService->isRegistLimit($userRow->regist_limit)) return $this->sendErrorResponse([], __('messages.the_temporary_registration_deadline_has_passed'));

        // 返す
        return $this->sendResponse($userRow);
    }

    public function sendResetPasswordMail(Request $request) {
        // 入力データの取得
        $inputData = $request->all();

        // キーのチェック
        $this->requestKeyCheck($request);

        // バリデート
        if($val=$this->authValidator->authEmailOnly($inputData)) return $this->sendValidateErrorResponse($val);

        // パラメーターでユーザー情報を取得
        // 該当するメールアドレスがなくてもメッセージではメールの送信を通知する
        //if(!$userRow = $this->userService->getItem(['email' => $inputData['email'], 'status' => 1])) return $this->sendResponse([], __('messages.success_send_reset_password_mail'));

        $userRow = $this->userService->getItems(['email' => $inputData['email'], 'status' => 1], 0 , "created_at DESC" );
        if(count($userRow) == 0) return $this->sendResponse([], __('messages.success_send_reset_password_mail'));

        // DB操作
        DB::beginTransaction($userRow[0]);
        try {
            // パラメータをセット
            if(!($item=$this->userService->setResetPasswordParam($userRow[0]->id))) return $this->sendErrorResponse([],__('messages.faild_create'));

            // 再取得
            $newUser = $this->userService->getItemById($userRow[0]->id);

            // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
            $newUser->loadMissing(['userDetail']);

            // メールを送信する
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail('emails.auth.reset_password', $newUser->email, '['.config('const.site.SITE_NAME').'] パスワードの再設定', $newUser)) throw new Exception(__('messages.faild_send_mail'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_send_reset_password_mail'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // パラメータに該当するユーザーがいるかチェック
    public function getResetPasswordUserByParam(Request $request, $param) {
        // 入力データの取得
        $inputData = $request->all();

        // キーのチェック
        $this->requestKeyCheck($request);

        // パラメーターで取得
        if(!$userRow = $this->userService->getItem(['param' => $param, 'status' => 1])) return $this->sendErrorResponse([], __('messages.invalid_url'));

        // 期限が過ぎていた場合
        if(!$this->userService->isRegistLimit($userRow->regist_limit)) return $this->sendErrorResponse([], __('messages.the_reset_password_deadline_has_passed'));

        // 返す
        return $this->sendResponse($userRow);
    }

    public function resetPassword(Request $request, $param) {
        // 入力データの取得
        $inputData = $request->all();

        // キーのチェック
        $this->requestKeyCheck($request);

        // バリデート
        if($val=$this->authValidator->resetPassword($inputData)) return $this->sendValidateErrorResponse($val);

        // パラメーターで取得
        if(!$userRow = $this->userService->getItem(['param' => $param, 'status' => 1])) return $this->sendErrorResponse([], __('messages.invalid_url'));

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {
            // パス変処理
            if(!$this->userService->updatePasswordById($userRow->id, $inputData['password'])) throw new Exception(__('messages.faild_update'));

            // パラメーターのリセット
            if(!$this->userService->resetParam($userRow->id)) throw new Exception(__('messages.faild_update'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_reset_password'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // メールアドレス変更用のメール送信
    public function sendChangeEmailMail(Request $request) {
        // 入力データの取得
        $inputData = $request->all();

        // キーのチェック
        $this->requestKeyCheck($request);

        // ユーザーデータの取得
        $userData = $request->user();

        // バリデート
        if($val=$this->authValidator->updateEmail($userData->id, $inputData)) return $this->sendValidateErrorResponse($val);

        $temporaryEmail = $inputData['email'];

        // DB操作
        DB::beginTransaction();
        try {
            // パラメータと仮メールアドレスをセット
            if(!($item=$this->userService->setChangeEmailParam($userData->id, $temporaryEmail))) return $this->sendErrorResponse([],__('messages.faild_create'));

            // 再取得
            $newUser = $this->userService->getItemById($userData->id);

            // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
            $newUser->loadMissing(['userDetail']);

            $newUser->toArray();

            // メールを送信する
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail('emails.auth.change_email', $newUser->email, '['.config('const.site.SITE_NAME').'] メールアドレスの変更', $newUser)) throw new Exception(__('messages.faild_send_mail'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_send_change_email_mail'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // メールアドレス変更
    public function updateEmail(Request $request, $param) {
        // 入力データの取得
        $inputData = $request->all();

        // パラメーターとユーザーIDで仮のメールアドレスを取得
        if(!$userRow = $this->userService->getItem(['param' => $param, 'status' => 1])) return $this->sendErrorResponse([], __('messages.invalid_url'));
        $user = $userRow->toArray();
        $newEmail = $user['temporary_email'];

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {
            // メールアドレス変更処理
            if(!$this->userService->updateEmailById($user['id'], $newEmail)) throw new Exception(__('messages.faild_update'));

            // パラメーターのリセット
            if(!$this->userService->resetParam($user['id'])) throw new Exception(__('messages.faild_update'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_update_email'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

}
