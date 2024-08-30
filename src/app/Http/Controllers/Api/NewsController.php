<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\NewsService;
use App\Services\UserService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class NewsController extends Bases\ApiBaseController
{

    // サービス
    protected $newsService;
    protected $userService;

    public function __construct(
        // サービス
        NewsService $newsService,
        UserService $userService

    ){
        parent::__construct();

        // サービス
        $this->newsService = $newsService;
        $this->userService = $userService;

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
            $news = $this->newsService->getList($inputData);

            return $this->sendResponse($news);

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

        $user = $this->newsService->getItemById($uid);

        if(!$user){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($user, __('messages.success'));

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
        if ($inputData['check'] == "1"){
            $v = Validator::make($inputData, ValidateCheckArray::$newCreate);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }else{
            $v = Validator::make($inputData, ValidateCheckArray::$newCreate_public);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if ($inputData['check'] == "1"){
                if(!$newData=$this->newsService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
            }else{
                $inputData['public_id'] = 0;
                if(!$newData=$this->newsService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
                $id = $newData->id;
                $userData = $this->userService->getList(['status'=>'1']);
                foreach($userData as $value){
                    $inputData['user_id'] = $value->id;
                    $inputData['public_id'] = $id;
                    if(!$newData=$this->newsService->createItem($inputData)) throw new Exception(__('messages.faild_create'));
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

     //指定IDを更新する
     public function update(Request $request, $id)
    {


        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力データの取得
        $inputData = $request->all();
        // バリデート
        if ($inputData['check'] == "1"){
            $v = Validator::make($inputData, ValidateCheckArray::$newsUpdate);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }else{
            $v = Validator::make($inputData, ValidateCheckArray::$newsUpdate_public);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {
            if ($inputData['check'] == "1"){
                if(!$this->newsService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('messages.faild_update'));
            }else{
                if(!$this->newsService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('messages.faild_update'));

                $newsData = $this->newsService->getItems(['public_id'=>$id]);
                foreach($newsData as $value){
                    if(!$this->newsService->updateItem(['id'=>$value['id']], $inputData)) throw new Exception(__('messages.faild_update'));
                }
            }

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

             if(!$newsRows = $this->newsService->getItem(['id'=>$id])) throw new Exception(__('messages.faild_delete'));

             if ($newsRows->news_flg == "1"){
                 if(!$this->newsService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));
             }else{
                 if(!$this->newsService->deleteItemById(['id'=>$id])) throw new Exception(__('messages.faild_update'));
                 if(!$this->newsService->deleteItems(['public_id'=>$id])) throw new Exception(__('messages.faild_update'));
             }

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

     //一覧取得
     public function getUserNews(Request $request,$id)
     {
         // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
         // キーのチェック
         $this->requestKeyCheck($request);

         //３か月前までのお知らせを
         $date =  date('Y-m-d', strtotime("-3 month"));

         // 入力データの取得
         $inputData = $request->all();
         try {

             //未読の取得
             $search['user_id'] = $id;
             $search['open_date'] = $date;
             $search['open_flg'] = "1";
             $search['status'] = "1";
             $news = $this->newsService->getList($search);
             $news = $news->toArray();
             $data['total'] = $news['total'];
             return $this->sendResponse($data);

         } catch (Exception $e) {
             Log::debug($e);
             return $this->sendExceptionErrorResponse($e['total']);

         }
     }

}
