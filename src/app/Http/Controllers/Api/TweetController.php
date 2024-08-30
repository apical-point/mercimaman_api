<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\TweetService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\NotifyService;
use App\Services\BlockService;
use App\Services\NgWordsService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class TweetController extends Bases\ApiBaseController
{

    // サービス
    protected $tweetService;
    protected $userService;
    protected $mailService;
    protected $notifyService;
    protected $blockService;
    protected $ngWordsService;

    public function __construct(
        // サービス
        TweetService $tweetService,
        UserService $userService,
        MailService $mailService,
        NotifyService $notifyService,
        BlockService $blockService,
        NgWordsService $ngWordsService

    ){
        parent::__construct();

        // サービス
        $this->tweetService = $tweetService;
        $this->userService = $userService;
        $this->mailService = $mailService;
        $this->notifyService = $notifyService;
        $this->blockService = $blockService;
        $this->ngWordsService = $ngWordsService;
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

            //ブロックユーザーの取得
            if(!empty($inputData['login_user_id'])){
                $block_response = $this->blockService->getItems(['user_id' => $inputData['login_user_id']])->toArray();
                $block_array = array_column($block_response, 'to_user_id');

                $block_response_to = $this->blockService->getItems(['to_user_id' => $inputData['login_user_id']])->toArray();
                $block_array_to = array_column($block_response_to, 'user_id');

                $inputData['block_users'] = array_merge($block_array, $block_array_to);

            }

            //検索情報取得
            //$news = $this->tweetService->getList($inputData, $userData["id"]);
            $arr = $this->tweetService->getList($inputData);

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

        //ブロックユーザーの取得
        if(!empty($inputData['login_user_id'])){
            $block_response = $this->blockService->getItems(['user_id' => $inputData['login_user_id']])->toArray();
            $block_array = array_column($block_response, 'to_user_id');

            $block_response_to = $this->blockService->getItems(['to_user_id' => $inputData['login_user_id']])->toArray();
            $block_array_to = array_column($block_response_to, 'user_id');

            $inputData['block_users'] = array_merge($block_array, $block_array_to);

        }

        $user = $this->tweetService->getItemById($id, $inputData);

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
        $response=$this->ngWordsService->checkNgWords(['word' => $inputData['tweet']]);
        if(!$response['success']){
            return $this->sendErrorResponse(['含まれてはいけない単語が含まれています。「'.$response['data'].'」'],  __('含まれてはいけない単語が含まれています。「'.$response['data'].'」'));
        }

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // *-*-*-*-*-*-*-*-*-*-*- バリデート -*-*-*-*-*-*-*-*-*-*-*
        if($inputData["tweet_flg"] == 2){
            $v = Validator::make($inputData, ValidateCheckArray::$tweet_rep);
        }
        else{
            $v = Validator::make($inputData, ValidateCheckArray::$tweet);
        }
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->tweetService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            //直前の親コメント投稿者にメールを送る
            if($inputData["tweet_flg"] == 2){
                //親IDの情報取得

                $tmp = $this->tweetService->getItemById($inputData["parent_id"], $inputData);
                $maildata['name'] = $tmp['name'];
                if($tmp["parent_id"]==0){
                    $maildata['tweet_url'] = config('const.site.DEMAE_BASE_URL')."tweet/detail/?id=".$tmp["id"];
                }
                else{
                    $maildata['tweet_url'] = config('const.site.DEMAE_BASE_URL')."tweet/re_detail/?id=".$tmp["id"];
                }

                if(!$this->mailService->sendMail_transaction($tmp['user_id'], $tmp['email'], 33, $maildata)) throw new Exception(__('messages.faild_send_mail'));

                //Push通知　ツイート、返信
                $arr[] = $tmp['user_id'];
                $this->notifyService->sendNotify(NotifyService::KIND_TWEET_REP, $arr, $inputData);


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

            $tweetArr = $this->tweetService->updateItem(['id'=>$id], $inputData, $userData["id"]);

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

             if(!$this->tweetService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

             $where=[];
             $where["parent_id"] = $id;
             $this->tweetService->deleteItems($where);


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
             $news = $this->tweetService->getTreetList($tweet_id);

             return $this->sendResponse($news);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e);

         }
     }
}
