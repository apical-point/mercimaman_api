<?php namespace App\Http\Controllers\Api;
// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

// サービス
use App\Services\SiteConfigService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class SiteConfigController extends ApiBaseController
{
    protected $siteConfigService;

    public function __construct(
        Request $request,
        SiteConfigService $siteConfigService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->siteConfigService = $siteConfigService;
    }

    // 一覧
    public function index(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();
        // *-*-*-*-*-*-*-*-*-*-*- db操作 *-*-*-*-*-*-*-*-*-*-*-
        try {
            return $this->sendResponse($this->siteConfigService->getList($inputData));
        }catch (Exception $e) {
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件取得
    public function show(Request $request, $id)
    {

        try {

            // 返す
            return $this->sendResponse($this->siteConfigService->getItemById($id));

            } catch (Exception $e) {

            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 更新
    public function update(Request $request, $id)
    {


        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();


        // *-*-*-*-*-*-*-*-*-*-*- バリデート *-*-*-*-*-*-*-*-*-*-*-
        // 入力バリデート
        $v = Validator::make($inputData, ValidateCheckArray::$updateSiteConfig);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- api操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {
            // 更新
            if(!$this->siteConfigService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('messages.faild_update'));

            // 再取得
            if(!$item = $this->siteConfigService->getItem(['id'=>$id])) return $this->sendErrorResponse([], $message='not found', $status=404);

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            // $item->load([]);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($item,__('messages.success_update'));
        } catch (Exception $e) {
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

}
