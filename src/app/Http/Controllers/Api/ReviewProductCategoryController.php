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
use App\Services\ReviewProductCategoryService;

class ReviewProductCategoryController extends ApiBaseController
{

    // サービス
    protected $reviewProductCategoryService;

    public function __construct(
        Request $request,

        // サービス
        ReviewProductCategoryService $reviewProductCategoryService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->reviewProductCategoryService = $reviewProductCategoryService;
    }

    // リスト取得
    public function index(Request $request)
    {
        try {
            // リスト取得
            $rows = $this->reviewProductCategoryService->getList($request->all());

            // 返す
            return $this->sendResponse($rows,  __('messages.success'));

        } catch (Exception $e) {

            return $this->sendExceptionErrorResponse($e);
        }

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
            if(!$newData=$this->reviewProductCategoryService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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

    // 更新
    public function updateCategory(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        $data = ['id' => (int)$inputData['id'], 'category_name' => $inputData['category_name']];

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$this->reviewProductCategoryService->updateItem(['id'=>(int)$inputData['id']], $data)) throw new Exception(__('messages.faild_update'));

            // 取得
            $item = $this->reviewProductCategoryService->getItemById($inputData['id']);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($item, __('messages.success_update'));
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

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->reviewProductCategoryService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

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