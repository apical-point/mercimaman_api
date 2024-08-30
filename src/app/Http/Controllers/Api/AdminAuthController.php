<?php namespace App\Http\Controllers\Api;

// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

// バリデート実行
use App\Validators\Api\AdminAuthValidator;
use App\Services\MailService;

// サービス
use App\Services\AdminerService;

class AdminAuthController extends ApiBaseController
{
    // サービス
    protected $userService;
    protected $adminerService;
    protected $mailService;

    // バリデート
    protected $adminAuthValidator;

    public function __construct (
        // リクエスト
        Request $requerst,

        // サービス
        AdminerService $adminerService,
        MailService $mailService,

        // バリデート
        AdminAuthValidator $adminAuthValidator
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($requerst);

        // サービス
        $this->adminerService = $adminerService;
        $this->mailService = $mailService;

        // バリデート
        $this->adminAuthValidator = $adminAuthValidator;
    }


    // 新規登録
    public function store(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->adminAuthValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザーデータの登録処理
            if(!$newData=$this->adminerService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            // 登録者にはメールを送信する。
            if(config('const.site.MAIL_SEND_FLG')) if(!$this->adminerMail->store($newData->toArray())) throw new Exception(__('messages.faild_create'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newData, __('messages.success_regist'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 自分の情報のみを取得する
    public function getMyData(Request $request)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 取得
        // データの取得
        $item = $this->adminerService->getItemById($userData->id);

        // 返す
        return $this->sendResponse($item, __('messages.success'));
    }

    // 自分の情報のみを更新する
    public function update(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーの取得
        $userData = $request->user();

        // バリデート
        if($val=$this->adminAuthValidator->update($userData->id, $inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザーデータの登録処理
            if(!$this->adminerService->updateItemById($userData->id, $inputData)) throw new Exception(__('messages.faild_create'));

            // 再取得
            $newData = $this->adminerService->getItemById($userData->id);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newData,  __('messages.success_update'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // パスワードの更新
    public function updatePassword(Request $request)
    {
        // データの定義
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // 入力のバリデート
        if($val=$this->adminAuthValidator->updatePassword($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // パス変処理
            if(!$this->adminerService->updatePasswordByIdAndPassword($userData->id, $inputData['password'])) throw new Exception(__('messages.faild_update'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_update'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 自分の情報のみを消去する
    public function withdrawal(Request $request)
    {
        // ユーザーの取得
        $userData = $request->user();

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザーデータの登録処理
            if(!$this->adminerService->deleteItemById($userData->id)) throw new Exception(__('messages.faild_delete'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([], __('messages.success_delete'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // バリデートするだけ
    public function loginCheck(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->adminAuthValidator->loginCheck($inputData)) return $this->sendValidateErrorResponse($val);

        return $this->sendResponse();
    }

    //パスワードのリセット
    public function sendResetPasswordMail(Request $request) {
        // 入力データの取得
        $inputData = $request->all();

        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力のバリデート
        if($val=$this->adminAuthValidator->sendResetPassword($inputData)) return $this->sendValidateErrorResponse($val);

        // パラメーターでユーザー情報を取得
        // 該当するメールアドレスがなくてもメッセージではメールの送信を通知する
        if(!$adminRow = $this->adminerService->getItem(['email' => $inputData['email']])) return $this->sendErrorResponse([],__('messages.system_password_reset'));

        // DB操作
        DB::beginTransaction();
        try {
            // パラメータをセット
            $password = date("His");
            if(!($item=$this->adminerService->updatePasswordByIdAndPassword($adminRow->id, $password))) return $this->sendErrorResponse([],__('messages.faild_create'));

            // 再取得
            $newUser = $this->adminerService->getItemById($adminRow->id);
            $newUser['pass'] = $password;

            // メールを送信する
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail('emails.admin.resetPassword',  $newUser->email, '['.config('const.site.SITE_NAME').' ]パスワード再設定のお知らせ', $newUser)) throw new Exception(__('messages.faild_send_mail'));
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

}
