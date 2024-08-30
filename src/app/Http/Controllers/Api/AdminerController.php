<?php namespace App\Http\Controllers\Api;

// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// バリデート実行
use App\Validators\Api\AdminerValidator;

// サービス
use App\Services\AdminerService;
use App\Services\MailService;

class AdminerController extends ApiBaseController
{
    // バリデート
    protected $adminerValidator;

    // メール
    protected $mailService;

    // サービス
    protected $adminerService;

    public function __construct(
        Request $request,
        // バリデート
        AdminerValidator $adminerValidator,

        // サービス
        MailService $mailService,

        // サービス
        AdminerService $adminerService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // バリデート
        $this->adminerValidator = $adminerValidator;

        // メール
        $this->mailService = $mailService;

        // サービス
        $this->adminerService = $adminerService;
    }

    // 登録処理一般ユーザーのみの作成
    public function store(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->adminerValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザーデータの登録処理
            if(!$newData=$this->adminerService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            // 登録者にはメールを送信する。
            if(config('const.site.MAIL_SEND_FLG')) {
                if(!$this->mailService->sendMail('emails.admin.admin_regist', $newData->email, '['.config('const.site.SITE_NAME').' ] 登録完了', $newData)) throw new Exception(__('messages.faild_send_mail'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newData, __('messages.success_create'));
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // リスト取得
    public function index(Request $request)
    {
        try {
            // リスト取得
            $rows = $this->adminerService->getList($request->all());

            // 返す
            return $this->sendResponse($rows,  __('messages.success'));

        } catch (Exception $e) {

            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 1件取得
    public function show(Request $request, $id)
    {
        // 該当のもの取得
        if(!$item = $this->adminerService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // 返す
        return $this->sendResponse($item,  __('messages.success'));
    }

    // 自分の情報のみを更新する
    public function update(Request $request, $id)
    {
        // データの定義
        // 入力データの取得
        $inputData = $request->all();

        // 該当のもの取得
        if(!$item = $this->adminerService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // バリデート
        if($val=$this->adminerValidator->update($id, $inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        // 更新
        try {
            // データの更新
            if(!$this->adminerService->updateItemById($id, $inputData)) throw new Exception(__('messages.faild_update'));

            // データの取得
            $newItem = $this->adminerService->getItemById($id);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newItem, __('messages.success_update'));
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // パスワードの更新
    public function updatePassword(Request $request, $id)
    {
        // データの定義
        $inputData = $request->all();

        // 入力のバリデート
        if($val=$this->adminerValidator->updatePassword($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // パス変処理
            if(!$this->adminerService->updatePasswordByIdAndPassword($id, $inputData['password'])) throw new Exception(__('messages.faild_update'));

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

    // 削除
    public function destroy(Request $request, $id)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 自分自身は削除できない
        if($userData->id==$id) return $this->sendNotFoundErrorResponse( __('messages.not_delete_myself'));

        // 該当のもの取得
        if(!$item = $this->adminerService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->adminerService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([],  __('messages.success_delete'));
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


}
