<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\ProductService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\ProductBrandService;
use App\Services\ProductCategoryService;
use App\Services\ProductMessageService;
use App\Services\OrderService;
use App\Services\PointService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\NotifyService;

// バリデート
use App\Validators\Api\ProductValidator;

// // メール
// use App\Mails\Api\AuthMail;


class ProductController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/product/';
    private static $saveFileUrl = 'product';

    // サービス
    protected $productService;
    protected $fileService;
    protected $upFileService;
    protected $productCategoryService;
    protected $productBrandService;
    protected $productMessageService;
    protected $orderService;
    protected $pointService;
    protected $userService;
    protected $mailService;
    protected $notifyService;

    // バリデータ
    protected $ProductValidator;

    // リクエスト
    protected $request;

    public function __construct(
        Request $request,

        // サービス
        ProductService $productService,
        FileService $fileService,
        UpFileService $upFileService,
        ProductCategoryService $productCategoryService,
        ProductBrandService $productBrandService,
        ProductMessageService $productMessageService,
        OrderService $orderService,
        PointService $pointService,
        UserService $userService,
        MailService $mailService,
        NotifyService $notifyService,
        // バリデータ
        ProductValidator $productValidator

    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // リクエスト
        $this->request = $request;

        // サービス
        $this->productService = $productService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;
        $this->productBrandService = $productBrandService;
        $this->productCategoryService = $productCategoryService;
        $this->productMessageService = $productMessageService;
        $this->orderService = $orderService;
        $this->pointService = $pointService;
        $this->userService = $userService;
        $this->mailService = $mailService;
        $this->notifyService = $notifyService;

        // バリデータ
        $this->productValidator = $productValidator;

    }

    // 一覧
    public function index(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        try {

            // リスト取得
            $productRows = $this->productService->getList($inputData);

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
//            $productRows->load(['mainImage']);

            // 返す
            return $this->sendResponse($productRows);

         } catch (Exception $e) {

            //  エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件取得
    public function show(Request $request, $id)
    {
        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        try {
            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $productRow->loadMissing(['userProfile']);

            // 返す
            return $this->sendResponse($productRow);

         } catch (Exception $e) {

            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 登録処理
    public function store(Request $request) {
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // バリデート
        if($val=$this->productValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        //チェックのみの場合はここで返す
        if ($inputData['chkonly']){
            return $this->sendResponse();
        }

        // DB操作
        DB::beginTransaction();
        try {

            //初回登録の場合ポイント付与
            $pointRow = $this->productService->getItem(['user_id'=>$userData->id]);
            if (empty($pointRow)){
                $data['point_type'] = "3";
                $data['point_detail'] = config('const.point.3.info');
                $data['point'] = config('const.point.3.point');
                $data['user_id'] = $userData->id;
                $data['point_date'] = date("Y-m-d");
                $date = date_create(date("Y-m-d"));
                $data['expiration_date'] = $date->modify('+3 month')->format('Y-m-d');
                if(!$this->pointService->createItem($data)) throw new Exception(__('messages.faild_regist'));
            }

            // 作成
            if(!$newData=$this->productService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir . "/" . $newData->id  . "/");

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl. "/" . $newData->id  . "/");

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createMainImageByProductIdAndImageData($newData->id, $upMainFileData)) throw new Exception(__('messages.faild_create'));
            }

            // 画像があれば保存
            if(!empty($inputData['up_sub_file'][0]) || !empty($inputData['up_sub_file'][1]) ||
                                        !empty($inputData['up_sub_file'][2]) || !empty($inputData['up_sub_file'][3])){

                // 画像ファイルの保存
                $createFilePaths = $this->fileService->saveImagesByBase64($inputData['up_sub_file'], self::$saveFileDir . "/" . $newData->id  . "/");

                // 画像ファイルのデータ取得
                $upFileData = $this->upFileService->getImagesData($createFilePaths, self::$saveFileUrl. "/" . $newData->id  . "/");

                // 画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createImagesByProductIdAndImagesData($newData->id, $upFileData)) throw new Exception(__('messages.faild_create'));
            }

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            //$newData->loadMissing(['productCategory', 'subImages', 'mainImage']);

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

    // 更新--post
    public function update(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // バリデート
        if ($inputData['check']=="1"){
            if($val=$this->productValidator->update($id, $inputData)) return $this->sendValidateErrorResponse($val);
        }else if ($inputData['check']=="2"){

        }

        //チェックのみの場合はここで返す
        if (isset($inputData['chkonly']) && $inputData['chkonly']){
            return $this->sendResponse();
        }

        // DB操作
        DB::beginTransaction();
        try {
            // メイン画像を更新する場合、古い画像を消去
            if ($inputData['up_id'][0] != $productRow->mainImage->id) {
                $where = [
                    'id' => $productRow->mainImage->id,
                    'up_file_able_id' => $id,
                    'up_file_able_type' => 'App\Repositories\Eloquent\Models\Product',
                ];
                // 削除
                if(!$this->upFileService->deleteItem($where)) throw new Exception(__('messages.faild_update'));
            }

            for($i = 1; $i < 5; $i++ ){
                // サブ画像を更新する場合、古い画像を消去
                if (!empty($productRow->subImages[$i-1]) && ($inputData['up_id'][$i] != $productRow->subImages[$i-1]->id)) {
                    $where = [
                        'id' => $productRow->subImages[$i-1]->id,
                        'up_file_able_id' => $id,
                        'up_file_able_type' => 'App\Repositories\Eloquent\Models\Product',
                    ];
                    // 削除
                    if(!$this->upFileService->deleteItem($where)) throw new Exception(__('messages.faild_update'));
                }

                // サブ画像の消去があれば消去
                if (isset($inputData['up_delete'][$i]) && ($inputData['up_delete'][$i] == "1")){
                    $where = [
                        'id' => $inputData['up_id'][$i],
                        'up_file_able_id' => $id,
                        'up_file_able_type' => 'App\Repositories\Eloquent\Models\Product',
                    ];
                    // 削除
                    if(!$this->upFileService->deleteItem($where)) throw new Exception(__('messages.faild_update'));
                }
            }

            // 更新
            if(!$this->productService->updateItemById($id, $inputData)) throw new Exception(__('messages.faild_update'));

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir . "/" . $id  . "/" );

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl . "/" . $id  . "/");

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$r=$this->productService->createMainImageByProductIdAndImageData($id, $upMainFileData)) throw new Exception(__('messages.faild_create'));

                // dd($r);
            }

            // 画像があれば保存
            if(!empty($inputData['up_sub_file'][0]) || !empty($inputData['up_sub_file'][1]) ||
                                    !empty($inputData['up_sub_file'][2]) || !empty($inputData['up_sub_file'][3])) {
                // 画像ファイルの保存
                $createFilePaths = $this->fileService->saveImagesByBase64($inputData['up_sub_file'], self::$saveFileDir . "/" . $id  . "/");

                // 画像ファイルのデータ取得
                $upFileData = $this->upFileService->getImagesData($createFilePaths, self::$saveFileUrl . "/" . $id  . "/");

                // 画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createImagesByProductIdAndImagesData($id, $upFileData)) throw new Exception(__('messages.faild_create'));
            }

            // 商品取得
            $newProductRow = $this->productService->getItem(['id'=>$id]);

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $newProductRow->loadMissing(['productCategory', 'subImages', 'mainImage']);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newProductRow);
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件削除
    public function destroy(Request $request, $id)
    {
        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // DB操作
        DB::beginTransaction();
        try {
            // 削除
            if(!$item=$this->productService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([]);
        } catch (Exception $e) {

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // ブランドの取得
    public function getBrand(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();
        // DB操作
        try {
            // リスト取得
            $productRows = $this->productBrandService->getList($inputData);

            // 返す
            return $this->sendResponse($productRows);

         } catch (Exception $e) {


            //  エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // メッセージの取得
    public function getMessage(Request $request)
    {

        // 入力データの取得
        $inputData = $request->all();
        // DB操作
        try {
            // リスト取得
            $productRows = $this->productMessageService->getList($inputData);
        //    $productRows->loadMissing(['user_profile']);

            // 返す
            return $this->sendResponse($productRows);

         } catch (Exception $e) {

            //  エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // メッセージの設定
    public function setMessage(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // バリデート
        if($val=$this->productValidator->message($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // 作成
            if(!$newData=$this->productMessageService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            // 商品取得
            $productRow = $this->productService->getItem(['id'=>$inputData['product_id']]);

            //出品者の取得
            if(!$saller_userRow = $this->userService->getItemByID($productRow->user_id)) return $this->sendNotFoundErrorResponse();
            $saller_userRow->loadMissing(['userDetail']);
            $saller_userRow = $saller_userRow->toArray();

            // 購入されているか
            $orderRow = $this->orderService->getItems(['product_id'=>$productRow->id],1,'id desc');
            $orderRow = $orderRow->toArray();


            // 出品者への商品コメントのお知らせ。
            if ($inputData['user_id'] != $productRow->user_id){
                if(config('const.site.MAIL_SEND_FLG')) {
                    //メール送信
                    $maildata['product_name'] = $productRow->product_name;
                    $maildata['name'] = $saller_userRow['user_detail']['last_name'] . $saller_userRow['user_detail']['first_name'];
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($saller_userRow['id'], $saller_userRow['email'], 5, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
                }

                //Push通知　コメントした時のPush機能
                $arr[] = $productRow->user_id;
                $this->notifyService->sendNotify(NotifyService::KIND_PRODUCT_COMMENT, $arr, $inputData);

            }

            // 購入者がいた場合の商品コメントのお知らせ。
            if (isset($orderRow[0]) and $inputData['user_id'] == $productRow->user_id ){
                if(!$buyer_userRow = $this->userService->getItemByID($orderRow[0]['buyer_user_id'])) return $this->sendNotFoundErrorResponse();
                $buyer_userRow->loadMissing(['userDetail']);
                $buyer_userRow = $buyer_userRow->toArray();

                if(config('const.site.MAIL_SEND_FLG')) {
                    $maildata['product_name'] = $productRow->product_name;
                    $maildata['name'] = $buyer_userRow['user_detail']['last_name'] . $buyer_userRow['user_detail']['first_name'];
                    if(config('const.site.MAIL_SEND_FLG')) {
                        if(!$this->mailService->sendMail_transaction($orderRow[0]['buyer_user_id'], $orderRow[0]['buyer_email'], 7, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                    }
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

    // メッセージの更新
    public function updateMessage(Request $request,$id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$newData=$this->productMessageService->updateItem(['id'=>$id],$inputData)) throw new Exception(__('messages.faild_create'));

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

    // 1件削除
    public function destroyMessage(Request $request)
    {

        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {

            $id = $inputData["id"];

            // 削除
            if(!$item=$this->productMessageService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse([]);
        } catch (Exception $e) {

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    public function updateStatus(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();
        // バリデート
        //if($val=$this->productValidator->update($id, $inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {

            // 更新
            $productData['id'] = $inputData['product_id'];
            $productData['status'] = $inputData['product_status'];
            if(!$this->productService->updateItemById($id, $productData)) throw new Exception(__('messages.faild_update'));

            // 商品取得
            $productRow = $this->productService->getItem(['id'=>$id]);
            //注文情報のステータス更新
            if ($inputData['product_status'] != "1"){

                $orderData['id'] = $inputData['order_id'];
                $orderData['status'] = $inputData['order_status'];
                if (isset($inputData['shipping_date']))$orderData['shipping_date'] = $inputData['shipping_date'];
                if (isset($inputData['tradeend_date']))$orderData['tradeend_date'] = $inputData['tradeend_date'];

                if(!$this->orderService->updateItem(["id"=>$orderData['id']],$orderData)) throw new Exception(__('messages.faild_update'));

                //注文情報の取得
                $orderRow = $this->orderService->getItem(['id'=>$inputData['order_id']]);

                // 発送完了メールを送信する。
                if ($inputData['order_status'] == "2" ){
                    if(config('const.site.MAIL_SEND_FLG')) {
                        //メール送信
                        $productRow = $productRow->toArray();
                        $orderRow = $orderRow->toArray();
                        $maildata['product_name'] = $productRow['product_name'];
                        $maildata['order_id'] = $inputData['order_id'];
                        $maildata['name'] = $orderRow['buyer_name'];

                        // 購入完了後にはメールを送信する。
                        if(config('const.site.MAIL_SEND_FLG')) {
                            if(!$this->mailService->sendMail_transaction($orderRow['buyer_user_id'], $orderRow['buyer_email'], 8, $maildata)) throw new Exception(__('messages.faild_send_mail'));
                        }
                    }
                }
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($productRow);
        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();
            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }


}
