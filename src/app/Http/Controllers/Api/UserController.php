<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\UserService;
use App\Services\UserDetailService;
use App\Services\UserProfileService;
use App\Services\UserFavoriteService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\MailService;
use App\Services\MessageService;
use App\Services\TweetService;
use App\Services\NotifyService;
use App\Services\BlockService;

// バリデート
use App\Validators\Api\UserValidator;

class UserController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/user/';
    private static $saveFileUrl = 'user/';

    // サービス
    protected $userService;
    protected $userDetailService;
    protected $userProfileService;
    protected $userFavoriteService;
    protected $fileService;
    protected $upFileService;
    protected $mailService;
    protected $messageService;
    protected $tweetService;
    protected $notifyService;
    protected $blockService;

    // バリデート
    protected $userValidator;

    public function __construct(
        // サービス
        UserService $userService,
        UserDetailService $userDetailService,
        UserProfileService $userProfileService,
        UserFavoriteService $userFavoriteService,
        FileService $fileService,
        MailService $mailService,
        UpFileService $upFileService,
        MessageService $messageService,
        TweetService $tweetService,
        NotifyService $notifyService,
        BlockService $blockService,
        // バリデート
        UserValidator $userValidator

    ){
        parent::__construct();

        // サービス
        $this->userService = $userService;
        $this->userDetailService = $userDetailService;
        $this->userProfileService = $userProfileService;
        $this->userFavoriteService = $userFavoriteService;
        $this->fileService = $fileService;
        $this->mailService = $mailService;
        $this->upFileService = $upFileService;
        $this->messageService = $messageService;
        $this->tweetService = $tweetService;
        $this->notifyService = $notifyService;
        $this->blockService = $blockService;

        // バリデート
        $this->userValidator = $userValidator;
    }

    // 一覧データ取得
    public function index(Request $request)
    {
Log::debug($request);        
        // データ定義
        $inputData = $request->all();

        // 取得
        $userRows = $this->userService->getList($inputData);
Log::debug($userRows);        
        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRows->loadMissing(['userDetail','userProfile']);

        //メッセージ一覧検索時は、相手との最新メッセージ取得
        if(!empty($inputData["usermessage"])){
            $id = $inputData['id'];
            foreach($userRows as $key=>$val){
                $search=[];
                $search['user_from_1'] = $id;
                $search['user_to_1'] = $val['id'];
                $search['user_from_2'] = $val['id'];
                $search['user_to_2'] = $id;
                $search['open_flg'] = "1";
                $search['update_flg'] = "1";
                $search['order_by_raw'] = 'created_at Desc';
                $search['msg'] = 1;//メッセージが入っているもの（画像だけは外す）
                $search['per_page'] = 5;

                $tmp = $this->messageService->getList($search);
                $userRows[$key]["message"] = $tmp;
            }
        }


        // 返す
        return $this->sendResponse($userRows);
    }

    // 一覧データ取得
    public function show(Request $request, $id)
    {

        // データ定義
        $inputData = $request->all();
        $inputData['to_user_id'] = $id; //データを表示させたいユーザーのID

        // 取得
        if(!$userRow = $this->userService->getItemByID($id)) return $this->sendNotFoundErrorResponse();

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail','userProfile', 'mainImage', 'subImage']);

        $user_data = $userRow->toArray();

        //ブロック情報の取得
        $blockRow = $this->blockService->getItem($inputData);
        $block_data = ($blockRow) ? $blockRow->toArray() : null;
        $user_data['block'] = $block_data;

        // 返す
        return $this->sendResponse($user_data);
    }

    // 更新
    public function update(Request $request, $id) {
        // 入力データの取得
        $inputData = $request->all();

        $userData = $request->user();

        // 該当のもの取得
        if(!$userRow = $this->userService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // バリデート
        if($val=$this->userValidator->update($id, $inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
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
                if(!$this->userService->updateOrCreateImageData($id, $upMainFileData, "1")) throw new Exception(__('messages.faild_create'));

                $inputData['identification'] = "1";
            }

            // 画像があれば保存
            if(!empty($inputData['up_sub_file'])){

                // 画像ファイルの保存
                $createFilePaths = $this->fileService->saveImageByBase64($inputData['up_sub_file'], self::$saveFileDir );

                // 画像ファイルのデータ取得
                $upFileData = $this->upFileService->getFileData($createFilePaths, self::$saveFileUrl );

                // 画像ファイルのデータをデータベースに保存する
                if(!$this->userService->updateOrCreateImageData($id, $upFileData, "0")) throw new Exception(__('messages.faild_create'));

                $inputData['identification'] = "1";
            }


            // ユーザー詳細の更新
            if(!$this->userDetailService->updateItemByUserId($id, $inputData)) throw new Exception(__('messages.faild_update'));

            // ユーザープロフィールの登録
            //興味の編集
            if ($inputData['check'] == "2"){
                for ($i = 1; $i < 5 ; $i++){
                    if ($i < 4) $inputData['taste' . $i] = (isset($inputData['taste' . $i])) ? $inputData['taste' . $i] : null;
                    $inputData['mother_interest' . $i] = (isset($inputData['mother_interest' . $i])) ? $inputData['mother_interest' . $i] : "";
                    $inputData['child_interest' . $i] = (isset($inputData['child_interest' . $i])) ? $inputData['child_interest' . $i] : "";
                    $inputData['experience' . $i] = (isset($inputData['experience' . $i])) ? $inputData['experience' . $i] : "";
                }
            }

            if($userData->user_type != 1){//イベント会員はプロフィール無し
                if(!$this->userProfileService->updateItemByUserId($id, $inputData)) throw new Exception(__('messages.faild_regist'));
            }
            // データの取得
            $userRow = $this->userService->getItemById($id);

            // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
            $userRow->loadMissing(['userDetail']);

            if (isset($inputData['identification']) && isset($inputData['identification_request'])){

                if ($inputData['identification_request'] == "1" && $inputData['identification'] == "2"){
                    $maildata['name'] = $userRow->userDetail->last_name . $userRow->userDetail->first_name;
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($userRow->id, $userRow->email, 21, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }else if(($inputData['identification_request'] == "1" && $inputData['identification'] == "3")){
                    $maildata['name'] = $userRow->userDetail->last_name . $userRow->userDetail->first_name;
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($userRow->id, $userRow->email, 22, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($userRow, __('messages.success_update_User'));

        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 削除
    public function destroy(Request $request, $id)
    {
        // 該当のもの取得
        if(!$userRow = $this->userService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail', 'userProfiles']);
        $user = $userRow->toArray();

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->userService->deleteItemById($user['id'])) throw new Exception(__('messages.faild_delete'));

            // ユーザー詳細更新
            if(!empty($user['user_detail'])) {
                if(!$this->userDetailService->deleteItemByUserId($user['id'])) throw new Exception(__('messages.faild_delete'));
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([],  __('messages.success_delete'));
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // フォロー数、フォロワー数、ツイートリアクション総数
    public function getFavorite(Request $request, $id)
    {

        // 入力データの取得
        $inputData = $request->all();

        $data = [];
        // 取得
        //if(!$userRow = $this->userFavoriteService->getfollowSum($id)) return $this->sendNotFoundErrorResponse();
        //$data['userfollow'] = $userRow[0]['total'];
        $data['userfollow'] =  $this->userFavoriteService->getfollowSum($id);

        //if(!$userRow = $this->userFavoriteService->getfollowerSum($id)) return $this->sendNotFoundErrorResponse();
        //$data['userfollower'] = $userRow[0]['total'];
        $data['userfollower'] = $this->userFavoriteService->getfollowerSum($id);

        $data['tweetSum'] = $this->tweetService->getUserSum($id);


        //if(!$userRow = $this->userFavoriteService->getfavoriteSum($id)) return $this->sendNotFoundErrorResponse();
        //$data['userfavorite'] = $userRow[0]['total'];
        //$data['userfavorite'] = $this->userFavoriteService->getfavoriteSum($id);

        // 返す
        return $this->sendResponse($data);

    }

    // お気に入りの取得(ユーザー情報)
    public function getUserFavorite(Request $request)
    {

        // 入力データの取得
        $inputData = $request->all();
        try {

            if ($inputData['type'] == "1"){
                $favoriteRow=$this->userFavoriteService->getItem(['user_id'=>$inputData['id'],'product_id'=>$inputData["product_id"]]);
            }else{
                $favoriteRow=$this->userFavoriteService->getItem(['user_id'=>$inputData['id'],'follow_id'=>$inputData["follow_id"]]);
            }

            if ($favoriteRow == ""){
                $data = 0;
            }else{
                $data = 1;
            }
            // 返す
            return $this->sendResponse($data);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);
        }

        // 返す
        return $this->sendResponse($favoriteRow);
    }

    // お気に入りの登録
    public function setFavorite(Request $request, $id)
    {

        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        //お気に入りデータの取得
        if ($inputData['type'] == "1"){
            $favoriteRow=$this->userFavoriteService->getItem(['user_id'=>$id,'product_id'=>$inputData["product_id"]]);
        }else{
            $favoriteRow=$this->userFavoriteService->getItem(['user_id'=>$id,'follow_id'=>$inputData["follow_id"]]);
        }

        //お気に入りのチェック
        $val = [];
        if (($inputData['value']=="0" && $favoriteRow != "") || ($inputData['value']=="1" && $favoriteRow == "")){
            $val['favorite'][] = __('messages.faild_update');
            return $this->sendValidateErrorResponse($val);
        }

        // DB操作
        DB::beginTransaction();
        try {
            // 作成
            if ($inputData['value']=="0"){
                if(!$newData=$this->userFavoriteService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
            }else{
                if(!$newData=$this->userFavoriteService->deleteItem(['id'=>$favoriteRow->id])) throw new Exception(__('messages.faild_create'));
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

    // ブロックの追加・解除
    public function setBlock(Request $request)
    {

        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();

        try {
            // 作成
            if ($inputData['flag']=="1"){
                if(!$newData=$this->blockService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
            }else{
                if(!$newData=$this->blockService->deleteItem(['user_id'=>$inputData['user_id'], 'to_user_id'=>$inputData['to_user_id']])) throw new Exception(__('messages.faild_create'));
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

    /*
     * 一覧データ取得
     *
     *
     */
    public function indexFavorite(Request $request)
    {
        // データ定義
        $inputData = $request->all();

        // 取得
        $userRows = $this->userFavoriteService->getList($inputData);



        // 返す
        return $this->sendResponse($userRows);
    }

    /*
     * 運営からのお知らせ
     * 送信成功した会員へPush通知を行う。
     *
     *
     */
    public function pushMailMagazin(Request $request)
    {

        // データ定義
        $inputData = $request->all();

        foreach($inputData as $key=>$val){
            $arr[] = $val["user_id"];
            $this->notifyService->sendNotify(NotifyService::KIND_ADMIN_NOTIFICATION, $arr, $val);
        }




    }


}
