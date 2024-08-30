<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\GrowthService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\FileService;
use App\Services\UpFileService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class GrowthController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/user/';
    private static $saveFileUrl = 'user';

    // サービス
    protected $growthService;
    protected $userService;
    protected $mailService;
    protected $fileService;
    protected $upFileService;

    public function __construct(
        // サービス
        GrowthService $growthService,
        UserService $userService,
        MailService $mailService,
        FileService $fileService,
        UpFileService $upFileService
    ){
        parent::__construct();

        // サービス
        $this->growthService = $growthService;
        $this->userService = $userService;
        $this->mailService = $mailService;
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

        // ユーザーデータの取得
        //$userData = $request->user();

        try {

            //検索情報取得
            $arr = $this->growthService->getList($inputData);

            return $this->sendResponse($arr);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }


    // 指定したIDの情報を返す
    // 自身の情報を取得する場合はgetMyData()を利用
    public function show(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        $user = $this->growthService->getItemById($id);

        if(!$user){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($user, __('messages.success'));

    }

    //リクエスト登録処理
    public function store(Request $request)
    {


        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        //$v = Validator::make($inputData, ValidateCheckArray::$event);

        //if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->growthService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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


        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力データの取得
        $inputData = $request->all();

        // バリデート

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            $tweetArr = $this->growthService->updateItem(['id'=>$id], $inputData);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($tweetArr, __('messages.success'));

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

             //ツイートの削除であれば、コメントも削除とする

             if(!$this->growthService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));


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

     //------------ 各年齢で出来る事リスト用

     //一覧取得
     public function indexGrowthAge(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // ユーザーデータの取得
         //$userData = $request->user();

         try {

             //検索情報取得
             $arr = $this->growthService->getListGrowthAge($inputData);

             return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }


     // 指定したIDの情報を返す
     // 自身の情報を取得する場合はgetMyData()を利用
     public function showGrowthAge(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         $user = $this->growthService->getItemByIdGrowthAge($id);

         if(!$user){
             return $this->sendErrorResponse([], __('messages.not_found'));
         }

         // 返す
         return $this->sendResponse($user, __('messages.success'));

     }

     //リクエスト登録処理
     public function createGrowthAge(Request $request)
     {


         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
         $v = Validator::make($inputData, ValidateCheckArray::$growthAge);

         if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             // 登録処理
             if(!$newData=$this->growthService->createItemGrowthAge($inputData)) throw new Exception(__('messages.faild_create'));

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
     public function updateGrowthAge(Request $request, $id)
     {


         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         // バリデート

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             $tweetArr = $this->growthService->updateItemGrowthAge(['id'=>$id], $inputData);

             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse($tweetArr, __('messages.success'));

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

     //削除
     public function destroyGrowthAge(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         try {

             //ツイートの削除であれば、コメントも削除とする

             if(!$this->growthService->deleteItemByIdGrowthAge($id)) throw new Exception(__('messages.faild_delete'));


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


     //------------ 自分の子供の出来たこと記録---------------------

     //一覧取得
     public function indexGrowthUser(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // ユーザーデータの取得
         $userData = $request->user();

         try {

             //検索情報取得
             $arr = $this->growthService->getListGrowthUser($inputData, $userData);

             return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }

     public function indexGrowthUserOne(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // ユーザーデータの取得
         $userData = [];
         if($request->user()){
             $userData = $request->user();
         }

         try {

             //検索情報取得
             $arr = $this->growthService->getListGrowthUserOne($inputData, $userData);

             return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }




     // 指定したIDの情報を返す
     // 自身の情報を取得する場合はgetMyData()を利用
     public function showGrowthUser(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         $user = $this->growthService->getItemByIdGrowthUser($id);

         if(!$user){
             return $this->sendErrorResponse([], __('messages.not_found'));
         }

         // 返す
         return $this->sendResponse($user, __('messages.success'));

     }

     //リクエスト登録処理
     public function createGrowthUser(Request $request)
     {


         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
         //$v = Validator::make($inputData, ValidateCheckArray::$growthAge);
         //if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             // 登録処理
             if(!$newData=$this->growthService->createItemGrowthUser($inputData)) throw new Exception(__('messages.faild_create'));

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

     public function updateOrCreateGrowthUser(Request $request)
     {


         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
         //$v = Validator::make($inputData, ValidateCheckArray::$growthAge);
         //if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             // 登録 or Update
             $where["user_id"] = $inputData["user_id"];
             $where["growth_age_id"] = $inputData["growth_age_id"];
             $where["name"] = $inputData["name"];

             if(!$newData=$this->growthService->updateOrCreateGrowthUser($where, $inputData)) throw new Exception(__('messages.faild_create'));

             //画像
             if(!empty($inputData['up_file'])) {

                 //画像ファイルの保存
                 $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_file'], self::$saveFileDir."/growth/" );
                 // メイン画像ファイルのデータ取得
                 $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl."/growth/" );

                 // メイン画像ファイルのデータをデータベースに保存する
                 if(!$this->growthService->createImageByMessageId($newData->id, $upMainFileData)) throw new Exception(__('messages.faild_create'));

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
     public function updateGrowthUser(Request $request, $id)
     {


         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         // バリデート

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             $tweetArr = $this->growthService->updateItemGrowthUser(['id'=>$id], $inputData);

             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse($tweetArr, __('messages.success'));

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

     //削除
     public function destroyGrowthUser(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         try {

             //ツイートの削除であれば、コメントも削除とする

             if(!$this->growthService->deleteItemByIdGrowthUser($id)) throw new Exception(__('messages.faild_delete'));


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

    /*
     * 成長記録　画像ファイルのアップ
     */
     public function upFileGrowthUser(Request $request)
     {

         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
         //$v = Validator::make($inputData, ValidateCheckArray::$growthAge);
         //if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {


             $where["user_id"] = $inputData["user_id"];
             $where["growth_age_id"] = $inputData["growth_age_id"];
             $where["name"] = $inputData["name"];

             $data = $this->growthService->getItemGrowthUser($where);


             //画像
             if(!empty($inputData['up_file'])) {

                 //画像ファイルの保存
                 $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_file'], self::$saveFileDir."/growth/" );
                 // メイン画像ファイルのデータ取得
                 $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl."/growth/" );

                 // メイン画像ファイルのデータをデータベースに保存する
                 if(!$this->growthService->createImageByGrowth($data->id, $upMainFileData)) throw new Exception(__('messages.faild_create'));

             }

             $data->loadMissing(['images']);

             // コミット
             DB::commit();


             // 返す
             return $this->sendResponse($data);

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }
     }

     /*
      * 成長記録　画像ファイルの削除処理
      */
     public function upFileGrowthDelete (Request $request)
     {

         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();


         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {


             $data = $this->upFileService->deleteItemById($inputData);

             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse($data);

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }
     }
}
