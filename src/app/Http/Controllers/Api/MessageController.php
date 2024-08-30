<?php namespace App\Http\Controllers\Api;
// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

// サービス
use App\Services\MessageService;
use App\Services\MailService;
use App\Services\NotifyService;
use App\Services\FileService;
use App\Services\UpFileService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class MessageController extends ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/user/';
    private static $saveFileUrl = 'user';

    // サービス
    protected $messageService;
    protected $mailService;
    protected $notifyService;
    protected $fileService;
    protected $upFileService;
    // リクエスト
    protected $request;

    public function __construct(
        Request $request,
        // サービス
        MessageService $messageService,
        MailService $mailService,
        NotifyService $notifyService,
        FileService $fileService,
        UpFileService $upFileService

    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->messageService = $messageService;
        $this->mailService = $mailService;
        $this->notifyService = $notifyService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;
    }

    // 一覧データ取得
    public function index(Request $request)
    {
        // データ定義
        $inputData = $request->all();

        // 既読の更新 機能削除
        /*try {
            //既読の更新
            if (isset($inputData['update_flg'])){
                    DB::beginTransaction();
                    $items = $this->messageService->getItems(['user_from_id'=>$inputData['user_from_1'],'user_to_id'=>$inputData['user_to_1'], 'confirm_date'=>null]);
                    foreach($items as $value){
                        $this->messageService->updateConfirmDate(['id'=>$value['id']]);
                    }
                    DB::commit();
                }

        } catch (Exception $e) {
            Log::debug($e);
            DB::rollBack();
            return $this->sendExceptionErrorResponse($e);
        }*/

        //メッセージ取得
        $items = $this->messageService->getList($inputData);
        $items->loadMissing(['images']);

        // 返す
        return $this->sendResponse($items);
    }


    /**
     * 新規作成
     */
    public function store(Request $request)
    {

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        $userData = $request->user();
        $userData = $userData->toArray();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        // 入力のバリデート
        if(!empty($inputData['up_file'])) {
            $v = Validator::make($inputData, ValidateCheckArray::$messageStoreImg);
        }
        else{
            $v = Validator::make($inputData, ValidateCheckArray::$messageStore);
        }

        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors());

        // 登録
        try {
            DB::beginTransaction();

            //登録
            if(!$item=$this->messageService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            //画像
            if(!empty($inputData['up_file'])) {

                //画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_file'], self::$saveFileDir."/message/" );
                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl."/message/" );

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->messageService->createImageByMessageId($item->id, $upMainFileData)) throw new Exception(__('messages.faild_create'));

            }
            //送信者へのメール
            if(config('const.site.MAIL_SEND_FLG')) {
                $maildata['nickname'] = $inputData['from_nickname'];
                $maildata['name'] = $inputData['to_name'];
                if(!$this->mailService->sendMail_transaction($inputData['user_to_id'], $inputData['to_email'], 15, $maildata)) throw new Exception(__('messages.faild_send_mail'));


                //Push通知　メッセージが届いた　
                $arr[] =$inputData['user_to_id'];
                $this->notifyService->sendNotify(NotifyService::KIND_RECEIVE_MESSAGE, $arr, $inputData);


            }

            DB::commit();
            return $this->sendResponse($item,__('messages.success_create'));

        } catch (Exception $e) {
            Log::debug($e);
            DB::rollBack();
            return $this->sendExceptionErrorResponse($e);
        }
    }

    /**
     * 1件取得
     */
    public function show(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();


        try {
            if (empty($item=$this->messageService->getItemById($id))) return $this->sendErrorResponse([],__('messages.not_found'));


            return $this->sendResponse($item);
        } catch (Exception $e) {
            return $this->sendExceptionErrorResponse($e);

        }
    }


    // 更新
    public function update(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        // 入力のバリデート
        //$v = Validator::make($inputData, ValidateCheckArray::$messageStore);
        //if ($v->fails()) return $this->sendValidateErrorResponse($v->errors());

        // *-*-*-*-*-*-*-*-*-*-*- 処理 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        try {
            // 更新処理
            if(!$this->messageService->updateItemById($id,$inputData)) return $this->sendErrorResponse([],__('messages.faild_update'));
            $newItem=$this->messageService->getItemById($id);

            // 更新データの取得
            DB::commit();
            return $this->sendResponse($newItem,__('messages.success_update'));
        } catch (Exception $e) {

            DB::rollBack();
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // idで削除
    public function destroy(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();


        // *-*-*-*-*-*-*-*-*-*-*- 処理 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        try {

            if(!$this->messageService->deleteItemById($id)) return $this->sendErrorResponse([],__('messages.faild_delete'));

            DB::commit();
            return $this->sendResponse(null,__('messages.success_delete'));
        } catch (Exception $e) {

            DB::rollBack();
            return $this->sendExceptionErrorResponse($e);
        }
    }
}
