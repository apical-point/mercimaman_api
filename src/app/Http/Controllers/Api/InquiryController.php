<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\InquiryService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class InquiryController extends Bases\ApiBaseController
{

    // サービス
    protected $inquiryService;

    public function __construct(
        // サービス
        InquiryService $inquiryService

    ){
        parent::__construct();

        // サービス
        $this->inquiryService = $inquiryService;

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
            $users = $this->inquiryService->getList($inputData);

            return $this->sendResponse($users);

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

        $inquiry = $this->inquiryService->getItemById($uid);

        if(!$inquiry){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($inquiry, __('messages.success'));

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
        if($inputData['inquiry_flg'] == "3"){
            $v = Validator::make($inputData, ValidateCheckArray::$inquiryAdvertisementCreate);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }
        elseif ($inputData['inquiry_flg'] != "1"){
            $v = Validator::make($inputData, ValidateCheckArray::$inquiryCreate);
            if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());
        }

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->inquiryService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            //メール送信
            // 登録者にメールを送信する
            //if ($inputData['inquiry_flg'] == "1"){
            //    if(config('const.site.MAIL_SEND_FLG')) {
            //        if(!$this->mailService->sendMail('emails.inquiry.opinion', $inputData, '['.config('const.site.SITE_NAME').'] ご意見・ご要望ありがとうございます', $inputData)) throw new Exception(__('messages.faild_send_mail'));
            //    }
            //}else if ($inputData['inquiry_flg'] == "2"){
            //    if(config('const.site.MAIL_SEND_FLG')) {
            //        if(!$this->mailService->sendMail('emails.inquiry.inauiry', $inputData, '['.config('const.site.SITE_NAME').'] お問い合わせを受付ました', $inputData)) throw new Exception(__('messages.faild_send_mail'));
            //    }
            //}else if ($inputData['inquiry_flg'] == "3"){
            //    if(config('const.site.MAIL_SEND_FLG')) {
            //        if(!$this->mailService->sendMail('emails.advertisement.opinion', $inputData, '['.config('const.site.SITE_NAME').'] 広告出稿のご相談を受付ました', $inputData)) throw new Exception(__('messages.faild_send_mail'));
            //    }
            //}

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
        $v = Validator::make($inputData, ValidateCheckArray::$inquiryUpdate);
        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            if(!$this->inquiryService->updateItem(['id'=>$id], $inputData)) throw new Exception(__('messages.faild_update'));


            // コミット
            DB::commit();

            $inquiry = $this->inquiryService->getItemById($id);

            // 返す
            return $this->sendResponse($inquiry , __('messages.success'));

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

             if(!$this->inquiryService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

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
