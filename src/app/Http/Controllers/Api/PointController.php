<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\PointService;
use App\Services\SiteConfigService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class PointController extends Bases\ApiBaseController
{
    // リクエスト
    protected $request;

    // サービス
    protected $point;
    protected $siteConfigService;

    public function __construct(
        Request $request,
        // サービス
        PointService $pointService,
        SiteConfigService $siteConfigService

    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);
        // リクエスト
        $this->request = $request;
        // サービス
        $this->pointService = $pointService;
        $this->siteConfigService = $siteConfigService;
    }


    // 一覧
    public function index(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        try {
            // リスト取得
            $items = $this->pointService->getList($inputData);

            // 返す
            return $this->sendResponse($items,  __('messages.success'));

         } catch (Exception $e) {
             Log::debug($e);

            //  エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件取得（ユーザーのポイント合計）
    public function show(Request $request, $id)
    {
        try {
            // 取得
            if(!$item = $this->pointService->getUsersSum($id)) return $this->sendErrorResponse([], __('messages.not_found'), 404);
            $item = $item->toArray();
            if (is_null($item[0]['total'])) $item[0]['total'] = 0;

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

        // 入力バリデート
        $v = Validator::make($inputData, ValidateCheckArray::$pointCreate);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

        // DB操作
        DB::beginTransaction();
        try {

            $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();
            $expiration_period = $siteConfigList[16]["value"];

            //有効期限の設定
            $date = date_create($inputData['point_date']);
            $inputData['expiration_date'] = $date->modify('+'.$expiration_period.' month')->format('Y-m-d');

            if(!$item=$this->pointService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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
    public function updatePoints(Request $request)
    {
        // 入力データの取得
        $input_data = $request->all();

        // DB操作
        DB::beginTransaction();

        try {

            foreach($input_data["point_detail"] as $point_id => $point_detail){

                $data = array(
                    "point_detail" => $point_detail,
                    "point" => $input_data["point"][$point_id],
                    "use_point" => $input_data["use_point"][$point_id],
                    "expiration_date" => $input_data["expiration_date"][$point_id]
                );

                if(!$this->pointService->updateItem(['id' => $point_id], $data)) throw new Exception(__('messages.faild_update'));

            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse("", 'ポイント情報が更新されました');
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件削除
    public function destroy(Request $request, $id)
    {

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$this->pointService->deleteItem(['id'=>$id])) throw new Exception(__('messages.faild_delete'));

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

    // ポイント削除
    public function deletePoints(Request $request)
    {
        // 入力データの取得
        $input_data = $request->all();

        // 入力バリデート
        $v = Validator::make($input_data, ValidateCheckArray::$pointDelete);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

        // DB操作
        DB::beginTransaction();

        try {

            $data['point_detail'] = $input_data['point_detail'];
            $data['point'] = "-".$input_data['point'];
            $data['user_id'] = $input_data['user_id'];
            $data['point_date'] = date("Y-m-d");
            $date = date_create(date("Y-m-d"));
            if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));

            $where = [];
            $where['expiration_date'] = date('Y-m-d');
            $where['use_flg'] = 0;
            $where['user_id'] = $input_data['user_id'];
            $where['per_page'] = "-1";
            $where['order_by_raw'] = "id asc";
            $pointRows = $this->pointService->getList($where);
            $pointRows = $pointRows->toArray();
            $point = $input_data['point'];//使用ポイント

            foreach($pointRows as $value){
                $current_point = $value['point'] - $value['use_point'];
                if ($point >= $current_point){
                    $point = $point - $current_point;
                    $data = [];
                    $data['use_point'] = $value['use_point'] + $current_point;
                    $data['use_flg'] = "1";
                    $data['use_date'] = date('Y-m-d');
                    if(!$this->pointService->updateItem(["id"=>$value['id']], $data)) throw new Exception(__('messages.faild_update'));
                }else{
                    $data = [];
                    $data['use_point'] = $value['use_point'] + $point;
                    $data['use_flg'] = ($data['use_point'] == $value['point']) ? "1" : "0";
                    $data['use_date'] = date('Y-m-d');
                    $point = 0;
                    if(!$this->pointService->updateItem(["id"=>$value['id']], $data)) throw new Exception(__('messages.faild_update'));
                }
                if ($point <= 0) break;
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse("", 'ポイント情報が削除されました');
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
                if(!$this->pointService->updateItem(['id'=>$inputData['category_id']["$i"]], $data)) throw new Exception(__('messages.faild_update'));
            }

            // コミット
            DB::commit();

            // 取得
            $search["parentid"] =  $inputData["parentid"];
            $search['order_by_raw'] = 'v_order';
            $items = $this->pintService->getList($search);

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
