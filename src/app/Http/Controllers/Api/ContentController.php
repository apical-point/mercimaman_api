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
use App\Services\PointService;
use App\Services\SiteConfigService;
use App\Services\NgWordsService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;
use App\Repositories\Eloquent\Models\PublicitySurvey;
use App\Validators\Api\ContentValidator;
use Carbon\CarbonImmutable;

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
    protected $pointService;
    protected $siteConfigService;
    protected $ngWordsService;

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
        PointService $pointService,
        SiteConfigService $siteConfigService,
        NgWordsService $ngWordsService,

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
        $this->pointService = $pointService;
        $this->siteConfigService = $siteConfigService;
        $this->ngWordsService = $ngWordsService;

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

         $response=$this->ngWordsService->checkNgWords(['word' => $inputData['message']]);
         if(!$response['success']){
             return $this->sendErrorResponse(['含まれてはいけない単語が含まれています。「'.$response['data'].'」'], __('含まれてはいけない単語が含まれています。「'.$response['data'].'」'));
         }

         DB::beginTransaction();
         // 更新
         try {

            $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();
            $expiration_date = $siteConfigList[16]["value"];
            $point_type = config('const.point_id.PRESENT_POINT_ID')-1;

            // 更新
            if(!$item=$this->contentMessageService->createItem($inputData)) throw new Exception(__('messages.faild_update'));

            if($inputData["type"] != 3){

                //ポイントプレゼント（プレゼント応募以外）
                $point_type = config('const.point_id.CONTENTS_POINT_ID')-1;
                $data['point_type'] = $point_type;
                $data['point_detail'] = $siteConfigList[$point_type]["description"];
                $data['point'] = $siteConfigList[$point_type]["value"];
                $data['user_id'] = $inputData["user_id"];
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
            }
            else{
                //プレゼント応募は10p使用する

                $total_point = $this->pointService->getUsersSum($inputData["user_id"]);
                $total_point = $total_point->toArray();
                if (is_null($total_point[0]['total'])) $total_point[0]['total'] = 0;

                if($total_point[0]['total'] < $siteConfigList[$point_type]["value"]){
                    return $this->sendErrorResponse(["応募できませんでした。ポイントが不足しています。"], __("応募できませんでした。ポイントが不足しています。"));
                }

                $data['point_type'] = $point_type;
                $data['point_detail'] = $siteConfigList[$point_type]["description"];
                $data['point'] = "-".$siteConfigList[$point_type]["value"];
                $data['user_id'] = $inputData["user_id"];
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));

                $where = [];
                $where['expiration_date'] = date('Y-m-d');
                $where['use_flg'] = 0;
                $where['user_id'] = $inputData["user_id"];
                $where['per_page'] = "-1";
                $where['order_by_raw'] = "id asc";
                $pointRows = $this->pointService->getList($where);
                $pointRows = $pointRows->toArray();
                $point = $siteConfigList[$point_type]["value"];//使用ポイント
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

            $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();

            $expiration_date = $siteConfigList[16]["value"];

            // 更新
            if(!$item=$this->contentOfferService->createItem($inputData)) throw new Exception(__('messages.faild_update'));

            $point_type = config('const.point_id.THEME_POINT_ID')-1;
            $data['point_type'] = $point_type;
            $data['point_detail'] = $siteConfigList[$point_type]["description"];
            $data['point'] = $siteConfigList[$point_type]["value"];
            $data['user_id'] = $inputData["user_id"];
            $data['point_date'] = date("Y-m-d");
            $date = date_create(date("Y-m-d"));
            $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
            if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));

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
            $contentRow = $contentRow->toArray();
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

    //----------- クロスワード ------------------

    public function storeCrossword(Request $request)
    {


        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*

        $v = Validator::make($inputData, ValidateCheckArray::$crossword);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());



        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->contentService->createItemCrossword($inputData)) throw new Exception(__('messages.faild_create'));


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

    //一覧取得
    public function indexCrossword(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        try {

            //検索情報取得
            $news = $this->contentService->getListCrossword($inputData);

            return $this->sendResponse($news);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }


    //指定したIDの情報
    public function showCrossword(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        $user = $this->contentService->getItemByIdCrossword($id, $userData["id"]);

        if(!$user){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($user, __('messages.success'));

    }

    //指定IDを更新する
    public function updateCrossword(Request $request, $id)
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
            $this->contentService->updateItemCrossword(['id'=>$id], $inputData, $userData["id"]);

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
    public function deleteCrossword(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        try {

            if(!$this->contentService->deleteItemByIdCrossword($id)) throw new Exception(__('messages.faild_delete'));

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


    //----------- タロット占い ------------------

    public function storeTarot(Request $request)
    {


        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*

        $v = Validator::make($inputData, ValidateCheckArray::$tarot);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());



        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->contentService->createItemTarot($inputData)) throw new Exception(__('messages.faild_create'));


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

    //一覧取得
    public function indexTarot(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        try {

            //検索情報取得
            $news = $this->contentService->getListTarot($inputData);

            return $this->sendResponse($news);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }


    //指定したIDの情報
    public function showTarot(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        $user = $this->contentService->getItemByIdTarot($id, $userData["id"]);

        if(!$user){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($user, __('messages.success'));

    }

    //指定IDを更新する
    public function updateTarot(Request $request, $id)
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
            $this->contentService->updateItemTarot(['id'=>$id], $inputData, $userData["id"]);

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
    public function deleteTarot(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        try {

            if(!$this->contentService->deleteItemByIdTarot($id)) throw new Exception(__('messages.faild_delete'));

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

    //タロット占いを行ったユーザーと結果登録
    public function storeTarotUser(Request $request)
    {

        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->contentService->createItemTarotUser($inputData)) throw new Exception(__('messages.faild_create'));

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

    //タロット占いをした人取得
    public function indexTarotUser(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        try {

            //検索情報取得
            $news = $this->contentService->getListTarotUser($inputData);

            return $this->sendResponse($news);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }

    //TODO
    public function fetchSurveys(Request $request)
    {
        try {
            $today = CarbonImmutable::today();
            $sortedSurveys = PublicitySurvey::whereDate('themedate', '<=', $today)->orderBy('themedate', 'desc')->get()->loadMissing(['mainImage']);
            return $this->sendResponse($sortedSurveys);
        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);
        }
    }

    public function answerSurvey(Request $request)
    {
        $attributes = $request->all();

        try {
            $answeredSurvey = PublicitySurvey::findOrFail($attributes['id']);
            switch(true) {
                case $attributes['answer'] === '0':
                    $answeredSurvey->increment('noCnt');
                    break;
                case $attributes['answer'] === '1':
                    $answeredSurvey->increment('yesCnt');
                    break;
                default:
                    break;
            }
            return $this->sendResponse($answeredSurvey);
        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);
        }
    }

    //管理ページ用これ知ってる関数
    public function indexSurvey(Request $request)
    {
        try {
            $surveys = PublicitySurvey::all();
            $surveys->loadMissing(['mainImage']);
            return $this->sendResponse($surveys, __('messages.success'));
        } catch (Exception $e) {
            Log::error($e);
            return $this->sendErrorResponse([$e], __('messages.not_found'));
        }
    }

    /**
     * これ知ってる？新規登録（管理画面から）
     */
    public function storeSurvey(Request $request)
    {
        $this->requestKeyCheck($request);


        $inputData = $request->all();
        // *-*-*-*-*-*-*-*-*-*-*- バリデート *-*-*-*-*-*-*-*-*-*-*-
        // 入力バリデート
        // if($val=$this->ContentValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        // *-*-*-*-*-*-*-*-*-*-*- api操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 日付の設定
            $date[] = date("Y-m-d",strtotime("last TuesDay"));
            for ($i=0; $i < 10 ; $i++){
                $date[] = date("Y-m-d",strtotime("+1 week", strtotime($date[$i])));
            }
            $inputData['themedate'] = $date[$inputData['selectthemedate']];

            // 新規登録
            if(!$item=$this->contentService->createItemSurvey($inputData)) throw new Exception(__('messages.faild_update'));

            //メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                Log::debug($createMainFilePath, $upMainFileData);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->contentService->updateOrCreateSurveyImage($item->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
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

    public function showSurvey(Request $request, $id)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // // 入力データの取得
        $inputData = $request->all();

        $survey = PublicitySurvey::find($id);

        if(!$survey){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        $survey->loadMissing(['mainImage']);



        // 返す
        return $this->sendResponse($survey, __('messages.success'));
    }

    public function updateSurvey(Request $request, $id)
    {
        Log::debug('ixciygocewy');

        $this->requestKeyCheck($request);

         /**
         * @var array <string, string | int> {
         *  'id' => int,
         *  'themedate' => {} (編集不可　何か不都合がありそうな気がしてるので一応からのオブジェクトのままにしている　要調査),
         *  'up_main_file' => {} (同上),
         *  'theme' => string,
         *  'submit_btn' => 1
         * } $inputData
         */
        $inputData = $request->all();

        DB::beginTransaction();
        try {
            $survey = PublicitySurvey::find($inputData['id']);

            if(!$survey) return $this->sendErrorResponse([], __('messages.not_found'));

            $survey->update([
                'theme' => $inputData['theme'],
                'description' => $inputData['description'],
                'url' => $inputData['url']
            ]);

            //メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                Log::debug($createMainFilePath, $upMainFileData);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->contentService->updateOrCreateSurveyImage($survey->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
            }
            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($survey->loadMissing('mainImage'),__('messages.success_update'));
        } catch(Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    public function destroySurvey(Request $request, $id)
    {
        Log::debug('これはidです' . $id);
        $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         try {

             if(!PublicitySurvey::where(['id' => $id])->delete()) throw new Exception(__('content.faild_delete'));

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
