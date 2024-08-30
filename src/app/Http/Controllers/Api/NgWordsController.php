<?php namespace App\Http\Controllers\Api;

// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\NgWordsService;

class NgWordsController extends ApiBaseController
{

    // サービス
    protected $ngWordsService;

    public function __construct(
        Request $request,

        // サービス
        NgWordsService $ngWordsService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->ngWordsService = $ngWordsService;
    }

    // 登録処理一般ユーザーのみの作成
    public function store(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザーデータの登録処理
            if(!$newData=$this->ngWordsService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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
            $rows = $this->ngWordsService->getList($request->all());

            // 返す
            return $this->sendResponse($rows,  __('messages.success'));

        } catch (Exception $e) {

            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 削除
    public function destroy(Request $request, $id)
    {

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->ngWordsService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

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