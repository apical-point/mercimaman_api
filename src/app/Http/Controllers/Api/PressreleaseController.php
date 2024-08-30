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
use App\Services\PressreleaseService;


class PressreleaseController extends ApiBaseController
{


    // サービス
    protected $pressreleaseControllerService;

    public function __construct(
        Request $request,

        // サービス
        PressreleaseService $pressreleaseControllerService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->pressreleaseService = $pressreleaseControllerService;
    }


    // リスト取得
    public function index(Request $request)
    {
        $inputData = $request->all();

        try {
           
            // リスト取得
            $rows = $this->pressreleaseService->getList($inputData);
            //$rows = $this->pressreleaseService->getList(['order_by_raw => publish_date DESC']);

            // 返す
            return $this->sendResponse($rows,  __('messages.success'));

        } catch (Exception $e) {

            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 指定したidの情報を返す
    public function show(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        $pressreleaseRow = $this->pressreleaseService->getItemById($id);


        if(!$pressreleaseRow){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($pressreleaseRow, __('messages.success'));

    }

    // 登録処理
    public function store(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            // 登録処理
            if(!$newData=$this->pressreleaseService->createItem($inputData)) throw new Exception(__('messages.faild_create'));


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
   public function update(Request $request)
   {
       // 入力データの取得
       $inputData = $request->all();

       // DB操作
       DB::beginTransaction();
       try {
           // 更新
           if(!$this->pressreleaseService->updateItem(['id'=>$inputData['id']], $inputData)) throw new Exception(__('messages.faild_update'));
       
           //if(!$upData=$this->pressreleaseService->updateItemById($id, $inputData)) throw new Exception(__('messages.faild_update'));

           // 取得
           $item = $this->pressreleaseService->getItemById($inputData['id']);

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

    // プレスリリースの削除
    public function destroy($id)
    {

        Log::debug($id);

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->pressreleaseService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

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