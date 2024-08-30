<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\BoardService;
use App\Services\UserService;
use App\Services\PointService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\MailService;
use App\Services\UserDetailService;
use App\Services\SiteConfigService;
use App\Services\NgWordsService;
use App\Services\BlockService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class BoardController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/board/';
    private static $saveFileUrl = 'board/';

    // サービス
    protected $boardService;
    protected $userService;
    protected $pointService;
    protected $fileService;
    protected $upFileService;
    protected $mailService;
    protected $userDetailService;
    protected $siteConfigService;
    protected $ngWordsService;
    protected $blockService;

    public function __construct(
        // サービス
        BoardService $boardService,
        UserService $userService,
        PointService $pointService,
        FileService $fileService,
        MailService $mailService,
        UserDetailService $userDetailService,
        UpFileService $upFileService,
        SiteConfigService $siteConfigService,
        NgWordsService $ngWordsService,
        BlockService $blockService
    ){
        parent::__construct();

        // サービス
        $this->boardService = $boardService;
        $this->userService = $userService;
        $this->pointService = $pointService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;
        $this->mailService = $mailService;
        $this->userDetailService = $userDetailService;
        $this->siteConfigService = $siteConfigService;
        $this->ngWordsService = $ngWordsService;
        $this->blockService = $blockService;
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

        //ブロックユーザーの取得
        if(!empty($inputData['login_user_id'])){
            $block_response = $this->blockService->getItems(['user_id' => $inputData['login_user_id']])->toArray();
            $block_array = array_column($block_response, 'to_user_id');

            $block_response_to = $this->blockService->getItems(['to_user_id' => $inputData['login_user_id']])->toArray();
            $block_array_to = array_column($block_response_to, 'user_id');

            $inputData['block_users'] = array_merge($block_array, $block_array_to);

        }

        try {

            //検索情報取得
            $news = $this->boardService->getList($inputData);

            return $this->sendResponse($news);

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

        $user = $this->boardService->getItemById($id);

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

        $inputData = $request->all();
        $response=$this->ngWordsService->checkNgWords(['word' => $inputData['title']]);
        if(!$response['success']){
            return $this->sendErrorResponse(['含まれてはいけない単語が含まれています。「'.$response['data'].'」'], __('含まれてはいけない単語が含まれています。「'.$response['data'].'」'));
        }

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        $v = Validator::make($inputData, ValidateCheckArray::$boardRequest);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->boardService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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

            $this->boardService->updateItem(['id'=>$id], $inputData);

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

             if(!$this->boardService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

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

     //==============    体験記 ==================================================
     //要望、体験登録処理
     public function storeExp(Request $request)
     {
         /**
         * @var string $inputData["exp_flg"]
         * 1
         * 2 => 'お悩み相談'
         * 3 => '雑談'
         *
         */
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();
         $response=$this->ngWordsService->checkNgWords(['word' => $inputData['detail']]);
         if(!$response['success']){
             return $this->sendErrorResponse(['含まれてはいけない単語が含まれています。「'.$response['data'].'」'], __('含まれてはいけない単語が含まれています。「'.$response['data'].'」'));
         }

         $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();

         $expiration_date = $siteConfigList[16]["value"];

         // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
         if($inputData["exp_flg"] == 1){//要望
             $v = Validator::make($inputData, ValidateCheckArray::$boardExpRequest);
             if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

             $point_type = config('const.point_id.WORRY_POINT_ID')-1;
         }
         else{//体験記
             $v = Validator::make($inputData, ValidateCheckArray::$boardExpCommentRequest);
             if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

             if(!empty($inputData["parent_id"])){
                 $point_type = config('const.point_id.ANSWER_POINT_ID')-1;
             }
             else{
                 $point_type = config('const.point_id.EXPERIENCE_POINT_ID')-1;
             }
         }


         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         // 更新
         try {

             // 登録処理
             if(!$newData=$this->boardService->createItemExp($inputData)) throw new Exception(__('messages.faild_create'));

             if(!empty($inputData['up_file'])) {
                 // メイン画像ファイルの保存
                 $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_file'], self::$saveFileDir);

                 // メイン画像ファイルのデータ取得
                 $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                 // メイン画像ファイルのデータをデータベースに保存する
                 if(!$this->boardService->updateOrCreateImageData($newData->id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));
             }

             $newData->loadMissing(['mainImage']);

             if (empty($inputData['no_point']) && $inputData["exp_flg"]!=3) {
                //ポイントプレゼント
                $data['point_type'] = $point_type;
                $data['point_detail'] = $siteConfigList[$point_type]["description"];
                $data['point'] = $siteConfigList[$point_type]["value"];
                $data['user_id'] = $inputData["user_id"];
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+'.$expiration_date.' month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
            }

             //お悩み相談に対しのコメントの場合は、お悩みを出した人にコメントが入った旨メールを送信する
             if(!empty($inputData["parent_id"]) && config('const.site.MAIL_SEND_FLG')){

                 //送信者（お悩みを出した相手）
                 $tmp = $this->boardService->getListExp(["id" => $inputData["parent_id"]]);

                 $send_user_id = $tmp[0]->user_id;

                 $userRow = $this->userService->getItemById($send_user_id);

                 $email = $userRow->email;
                 $userRow = $this->userDetailService->getItem(["user_id"=>$send_user_id]);
                 $maildata["name"] = $userRow->last_name.$userRow->first_name;

                 if($inputData["detail_flg"]){
                 	$maildata["url"] = config('const.site.DEMAE_BASE_URL')."board/exp_detail/?id=".$inputData["parent_id"];
                 }else{
                	$maildata["url"] = config('const.site.DEMAE_BASE_URL')."board/comment/?id=".$inputData["parent_id"];
                }
                if($inputData["exp_flg"]==3){
                    $mail_no = 44;
                    //雑談は通知しなくなったので、メールの処理は入れない
                    if(!$this->mailService->sendMail_transaction($send_user_id, $email, $mail_no, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                }else{
                    $mail_no = 43;
                    if(!$this->mailService->sendMail_transaction($send_user_id, $email, $mail_no, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                }
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

     //一覧取得
     public function indexExp(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         try {

            //ブロックユーザーの取得
            if(!empty($inputData['login_user_id'])){
                $block_response = $this->blockService->getItems(['user_id' => $inputData['login_user_id']])->toArray();
                $block_array = array_column($block_response, 'to_user_id');

                $block_response_to = $this->blockService->getItems(['to_user_id' => $inputData['login_user_id']])->toArray();
                $block_array_to = array_column($block_response_to, 'user_id');

                $inputData['block_users'] = array_merge($block_array, $block_array_to);

            }

            //検索情報取得
            $arr = $this->boardService->getListExp($inputData);
            $arr->loadMissing(['mainImage']);

            return $this->sendResponse($arr);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }


    //指定したIDの情報
     public function showExp(Request $request, $id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
         // 入力データの取得
         $inputData = $request->all();

         // ユーザーデータの取得
         $userData = $request->user();

         $user = $this->boardService->getItemByIdExp($id, $userData["id"]);
         $user->loadMissing(['mainImage']);

         if(!$user){
             return $this->sendErrorResponse([], __('messages.not_found'));
         }

         // 返す
         return $this->sendResponse($user, __('messages.success'));

     }

     //指定IDを更新する
     public function updateExp(Request $request, $id)
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
             $this->boardService->updateItemExp(['id'=>$id], $inputData, $userData["id"]);

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
     public function deleteExp(Request $request)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         // 入力データの取得
         $inputData = $request->all();

         // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
         DB::beginTransaction();
         try {

             $id = $inputData["id"];

             if(!$this->boardService->deleteItemByIdExp($id)) throw new Exception(__('messages.faild_delete'));

             //親のお悩み削除であれば、紐づくコメントも削除
             $where=[];
             $where["parent_id"] = $id;
             $this->boardService->deleteItemExp($where);


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

      // ツリー一覧取得
      public function indexTree(Request $request)
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
              $tweet_id = $inputData["id"];
              $news = $this->boardService->getTreetList($tweet_id);

              return $this->sendResponse($news);

          } catch (Exception $e) {
              Log::debug($e);
              return $this->sendExceptionErrorResponse($e);

          }
      }
}
