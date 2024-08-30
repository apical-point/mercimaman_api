<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\ProductCategoryService;
use App\Services\Mst\MstDebugService;

// バリデート
use App\Validators\Api\ProductCategoryValidator;

class ProductCategoryController extends Bases\ApiBaseController
{
    // リクエスト
    protected $request;

    // サービス
    protected $productCategory;

    // バリデート
    protected $ProductCategoryValidator;

    public function __construct(
        Request $request,

        // サービス
        ProductCategoryService $productCategoryService,

        // バリデート
        ProductCategoryValidator $ProductCategoryValidator
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // リクエスト
        $this->request = $request;

        // サービス
        $this->productCategoryService = $productCategoryService;

        // バリデート
        $this->ProductCategoryValidator = $ProductCategoryValidator;

    }


    // 一覧
    public function index(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        try {
            // リスト取得
            $items = $this->productCategoryService->getList($inputData);
            // 返す
            return $this->sendResponse($items,  __('messages.success'));

         } catch (Exception $e) {
             Log::debug($e);

            //  エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件取得
    public function  show(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        try {
            // 取得
            if(!$item = $this->productCategoryService->getItemById($id)) return $this->sendErrorResponse([], __('messages.not_found'), 404);

            // 返す
            return $this->sendResponse($item);

         } catch (Exception $e) {
             Log::debug($e);

            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 登録処理
    public function store(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->ProductCategoryValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {

            //v-orderの最後の番号を取得
            // 取得
            //$search["parentid"] =  $inputData["parentid"];
            //$search["cflag"] =  $inputData["cflag"];
            //$search['order_by_raw'] = 'v_order desc';
            //$categorylist = $this->productCategoryService->getList($search);

            if(!$item=$this->productCategoryService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($item);

        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


    // 更新
    public function update(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->ProductCategoryValidator->update($id, $inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$this->productCategoryService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('messages.faild_update'));

            // 取得
            $item = $this->productCategoryService->getItemById($id);

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

    // 1件取得
    public function destroy(Request $request, $id)
    {

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$this->productCategoryService->deleteItem(['id'=>$id])) throw new Exception(__('messages.faild_delete'));

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

    // 並び替え
    public function sortUpdate(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            //カテゴリ（２階層目）の中で順番を振りなおす
            // 更新
            for ($i=0; $i < count($inputData['category_id']); $i++){
                $data = ['id'=>$inputData['category_id']["$i"], 'v_order'=>$i+1];
                if(!$this->productCategoryService->updateItem(['id'=>$inputData['category_id']["$i"]], $data)) throw new Exception(__('messages.faild_update'));
            }

            // コミット
            DB::commit();

            // 取得
            $search["parentid"] =  $inputData["parentid"];
            $search['order_by_raw'] = 'v_order';
            $items = $this->productCategoryService->getList($search);

            // 返す
            return $this->sendResponse($items,__('messages.success_update'));

        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }



}
