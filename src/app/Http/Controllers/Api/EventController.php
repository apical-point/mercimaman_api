<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\EventService;
use App\Services\UserService;
use App\Services\MailService;


// バリデートの配列
use App\Libraries\ValidateCheckArray;

class EventController extends Bases\ApiBaseController
{

    // サービス
    protected $eventService;
    protected $userService;
    protected $mailService;

    public function __construct(
        // サービス
        EventService $eventService,
        UserService $userService,
        MailService $mailService
    ){
        parent::__construct();

        // サービス
        $this->eventService = $eventService;
        $this->userService = $userService;
        $this->mailService = $mailService;

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
            $arr = $this->eventService->getList($inputData);

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

        $user = $this->eventService->getItemById($id);

        //$user->loadMissing(['eventTopic']);

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

        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->eventService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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

        // ユーザーデータの取得
        $userData = $request->user();

        // バリデート

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            $tweetArr = $this->eventService->updateItem(['id'=>$id], $inputData);

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

             if(!$this->eventService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));


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
