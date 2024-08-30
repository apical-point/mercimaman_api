<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\AdvertisementService;
use App\Services\FileService;
use App\Services\UpFileService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class AdvertisementController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/advertisement/';
    private static $saveFileUrl = 'advertisement/';

    // サービス
    protected $advertisementService;
    protected $fileService;
    protected $upFileService;

    public function __construct(
        // サービス
        AdvertisementService $advertisementService,
        FileService $fileService,
        UpFileService $upFileService

    ){
        parent::__construct();

        // サービス
        $this->advertisementService = $advertisementService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;

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
            $advertisementRow = $this->advertisementService->getList($inputData);
            $advertisementRow->loadMissing(['mainImage']);

            return $this->sendResponse($advertisementRow);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }


    // 指定したユーザの情報を返す
    // 自身の情報を取得する場合はgetMyData()を利用
    public function show(Request $request, $uid)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        $advertisementRow = $this->advertisementService->getItemById($uid);
        $advertisementRow->loadMissing(['mainImage']);


        if(!$advertisementRow){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($advertisementRow, __('messages.success'));

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
        if (!isset($inputData['type']) || empty($inputData['type'])){
            $v = Validator::make($inputData, ValidateCheckArray::$advertisement);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }else if($inputData['type'] == "1"){
            $v = Validator::make($inputData, ValidateCheckArray::$advertisementComapny);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }else{
            $v = Validator::make($inputData, ValidateCheckArray::$advertisementScript);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 広告登録処理
            if(!$newData=$this->advertisementService->createItem($inputData)) throw new Exception(__('help.faild_create'));

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->advertisementService->updateOrCreateImageData($newData->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
            }

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
        if (!isset($inputData['type']) || empty($inputData['type'])){
            $v = Validator::make($inputData, ValidateCheckArray::$advertisement);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }else if($inputData['type'] == "1"){
            $v = Validator::make($inputData, ValidateCheckArray::$advertisementComapnyUpdate);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }else{
            $v = Validator::make($inputData, ValidateCheckArray::$advertisementScript);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->advertisementService->updateOrCreateImageData($id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
            }

            if(!$this->advertisementService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('advertisement.faild_update'));


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

             if(!$this->advertisementService->deleteItemById($id)) throw new Exception(__('advertisement.faild_delete'));

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
