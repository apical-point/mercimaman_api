<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\ContentService;
use App\Services\ContentMessageService;
use App\Services\ContentOfferService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\UserService;
use App\Services\MailService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;
use App\Validators\Api\ContentValidator;

class ContentController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/content/';
    private static $saveFileUrl = 'content/';

    // サービス
    protected $contentService;
    protected $contentMessageService;
    protected $contentOfferService;
    protected $fileService;
    protected $upFileService;
    protected $userService;
    protected $mailService;

    // バリデート
    protected $ContentValidator;

    public function __construct(
        // サービス
        ContentService $contentService,
        ContentMessageService $contentMessageService,
        ContentOfferService $contentOfferService,
        FileService $fileService,
        UserService $userService,
        UpFileService $upFileService,
        MailService $mailService,

        // バリデート
        ContentValidator $ContentValidator
    ){
        parent::__construct();

        // サービス
        $this->contentService = $contentService;
        $this->contentMessageService = $contentMessageService;
        $this->contentOfferService = $contentOfferService;
        $this->fileService = $fileService;
        $this->userService = $userService;
        $this->upFileService = $upFileService;
        $this->mailService = $mailService;

        // バリデート
        $this->ContentValidator = $ContentValidator;

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
            $content = $this->contentService->getList($inputData);

            if (isset($inputData['presentdate'])){
                $content->loadMissing(['mainImage']);
            }

            return $this->sendResponse($content);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }

    }

    //コンテント情報
    public function show(Request $request, $uid)
    {

        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        $contentRow = $this->contentService->getItemById($uid);
        $contentRow->loadMissing(['mainImage']);

        if(!$contentRow){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($contentRow, __('messages.success'));

    }

    //登録処理
    public function store(Request $request)
    {

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート *-*-*-*-*-*-*-*-*-*-*-
        // 入力バリデート
        if($val=$this->ContentValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        // *-*-*-*-*-*-*-*-*-*-*- api操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 日付の設定
            $inputData['choicesdate'] = date("Y-m-d",strtotime("+2 day", strtotime($inputData['themedate'])));
            $inputData['presentdate'] = date("Y-m-d",strtotime("+4 day", strtotime($inputData['themedate'])));

            // 更新
            if(!$item=$this->contentService->createItem($inputData)) throw new Exception(__('messages.faild_update'));

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->contentService->updateOrCreateImageData($item->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($item,__('messages.success_update'));

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
        $v = Validator::make($inputData, ValidateCheckArray::$content);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

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
                if(!$this->contentService->updateOrCreateImageData($id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
            }

            if(!$this->contentService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('content.faild_update'));

            // 商品取得
            $newContentRow = $this->contentService->getItem(['id'=>$id]);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newContentRow, __('messages.success'));

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

             if(!$this->contentService->deleteItemById($id)) throw new Exception(__('content.faild_delete'));

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


     //コンテンツメッセージの取得
     public function getMessageContent(Request $request)
     {
         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         try {

             //検索情報取得
             $data = [];
        //     $data['content'] = $this->contentService->getItemById($inputData['content_id']);
             $data = $this->contentMessageService->getList($inputData);

             return $this->sendResponse($data);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }

     //コンテンツメッセージの設定
     public function setMessageContent(Request $request)
     {

         // 入力データの取得
         $inputData = $request->all();

         // 入力バリデート
         if($val=$this->ContentValidator->messageStore($inputData)) return $this->sendValidateErrorResponse($val);

         DB::beginTransaction();
         // 更新
         try {

             // 更新
             if(!$item=$this->contentMessageService->createItem($inputData)) throw new Exception(__('messages.faild_update'));

             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse($item,__('messages.success_update'));

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();
             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

     //コンテンツメッセージの取得
     public function getOfferContent(Request $request)
     {
         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         try {

             //検索情報取得
             $data = $this->contentOfferService->getList($inputData);

             return $this->sendResponse($data);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }

     //テーマ募集の登録
      public function setOfferContent(Request $request)
     {

         // 入力データの取得
         $inputData = $request->all();

         // 入力バリデート
         if($val=$this->ContentValidator->offerStore($inputData)) return $this->sendValidateErrorResponse($val);

         DB::beginTransaction();
         // 更新
         try {

             // 更新
             if(!$item=$this->contentOfferService->createItem($inputData)) throw new Exception(__('messages.faild_update'));

             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse($item,__('messages.success_update'));

         } catch (Exception $e) {
             Log::debug($e);
             // ロールバック
             DB::rollBack();
             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

     //二択選択の登録
      public function choiceUpdate(Request $request, $id)
     {

         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         // コンテンツ取得
         $ContentRow = $this->contentService->getItem(['id'=>$id]);

         DB::beginTransaction();
         // 更新
         try {

             if ($inputData['answer'] == "1"){
                 $data['answerCnt1'] = 	$ContentRow->answerCnt1 + 1;
             }else{
                 $data['answerCnt2'] = 	$ContentRow->answerCnt2 + 1;
             }

             if(!$this->contentService->updateItem(['id'=>$id], $data)) throw new Exception(__('content.faild_update'));

             // コンテンツ取得
             $newContentRow = $this->contentService->getItem(['id'=>$id]);

             // コミット
             DB::commit();

             // 返す
             return $this->sendResponse($newContentRow, __('messages.success'));

         } catch (Exception $e) {

             Log::debug($e);
             // ロールバック
             DB::rollBack();

             // 返す
             return $this->sendExceptionErrorResponse($e);
         }

     }

    //削除
    public function destroyOfferContent(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        try {

            if(!$this->contentOfferService->deleteItemById($id)) throw new Exception(__('content.faild_delete'));

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

    // メッセージの更新
    public function updateMessage(Request $request,$id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            $dataRows = $this->contentMessageService->getItem(['id'=>$id]);

            // 更新
            $inputData['open_flg'] = ($dataRows->open_flg) ? 0 : 1;
            if(!$newData=$this->contentMessageService->updateItem(['id'=>$id],$inputData)) throw new Exception(__('messages.faild_create'));

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

    //削除
    public function destroyMessage(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        try {

            $id = $inputData["id"];

            if(!$this->contentMessageService->deleteItemById($id)) throw new Exception(__('content.faild_delete'));

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

    public function updatePresent(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {

            $contentRow = $this->contentService->getItem(['id'=>$id]);

            // 更新
            $contentData['id'] = $id;
            $contentData['election_flg'] = 1;
            if(!$this->contentService->updateItem(['id'=>$id], $contentData)) throw new Exception(__('messages.faild_update'));


            //当選情報のステータス更新
            foreach($inputData['election'] as $value){

                $electiondata = $this->contentMessageService->getItem(["id"=>$value]);

                $data = [];
                $data['election_flg'] = "1";
                if(!$orderhistoryRow = $this->contentMessageService->updateItem(["id"=>$value], $data)) return $this->sendNotFoundErrorResponse();

                //ユーザーに振込通知を出す
                if(!$userRow = $this->userService->getitem(["id"=>$electiondata->user_id])) return $this->sendNotFoundErrorResponse();
                $userRow->loadMissing(['userDetail']);
                $userRow = $userRow->toArray();

                if(config('const.site.MAIL_SEND_FLG')) {
                    $contentRow = $contentRow->toArray();
                    $maildata['present'] = $contentRow['present'];
                    $maildata['name'] = $userRow['user_detail']['last_name'] . $userRow['user_detail']['first_name'];
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($userRow['id'], $userRow['email'], 14, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($electiondata);

        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();
            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

}
