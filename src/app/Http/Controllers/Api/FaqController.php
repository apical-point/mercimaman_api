<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\FaqService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class FaqController extends Bases\ApiBaseController
{

    // サービス
    protected $faqService;

    public function __construct(
        // サービス
        FaqService $faqService

    ){
        parent::__construct();

        // サービス
        $this->faqService = $faqService;

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
        try {

            //検索情報取得
            $users = $this->faqService->getList($inputData);

            return $this->sendResponse($users);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }


    //1件の情報取得
    public function show(Request $request, $uid)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        $user = $this->faqService->getItemById($uid);

        if(!$user){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($user, __('messages.success'));

    }

    //登録処理
    public function store(Request $request)
    {


        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        $v = Validator::make($inputData, ValidateCheckArray::$faqCreate);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // ヘルプの登録処理
            if(!$newData=$this->faqService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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


        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        $v = Validator::make($inputData, ValidateCheckArray::$faqCreate);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            if(!$this->faqService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('messages.faild_update'));


            // コミット
            DB::commit();

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

     //削除
     public function destroy(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         try {

             if(!$this->faqService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

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
}
