<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

// サービス
use App\Services\ProductService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\ProductCategoryService;
use App\Services\ProductOptionService;

// バリデート
use App\Validators\Api\MyProductValidator;

// // メール
// use App\Mails\Api\AuthMail;


class MyProductController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/product/';
    private static $saveFileUrl = 'product/';

    // サービス
    protected $productService;
    protected $fileService;
    protected $upFileService;
    protected $productCategoryService;
    protected $productOptionService;

    // バリデータ
    protected $myProductValidator;

    // リクエスト
    protected $request;

    public function __construct(
        Request $request,

        // サービス
        ProductService $productService,
        FileService $fileService,
        UpFileService $upFileService,
        ProductCategoryService $productCategoryService,
        ProductOptionService $productOptionService,

        // バリデータ
        MyProductValidator $myProductValidator
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
        $this->productCategoryService = $productCategoryService;
        $this->productOptionService = $productOptionService;

        // バリデータ
        $this->myProductValidator = $myProductValidator;
    }

    // 登録処理
    public function store(Request $request)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 入力データの取得
        $inputData = $request->all();

        // バリデート
        if($val=$this->myProductValidator->store($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // 作成
            if(!$newData=$this->productService->createItemByUserId($userData->id, $inputData)) throw new Exception(__('messages.faild_create'));

            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createMainImageByProductIdAndImageData($newData->id, $upMainFileData)) throw new Exception(__('messages.faild_create'));
            }

            // 画像があれば保存
            if(!empty($inputData['up_sub_files'])) {
                // 画像ファイルの保存
                $createFilePaths = $this->fileService->saveImagesByBase64($inputData['up_sub_files'], self::$saveFileDir);

                // 画像ファイルのデータ取得
                $upFileData = $this->upFileService->getImagesData($createFilePaths, self::$saveFileUrl);

                // 画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createImagesByProductIdAndImagesData($newData->id, $upFileData)) throw new Exception(__('messages.faild_create'));
            }

            // オプションがあればオプションを複数作成する
            if(!empty($inputData['options'])) {
                if(!$results = $this->productOptionService->createItemsByProductId($newData->id, $inputData['options'])) throw new Exception(__('messages.faild_create'));
            }

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $newData->load(['category', 'colorOptions', 'sizeOptions', 'subImages', 'mainImage']);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newData);
        } catch (Exception $e) {

            // ロールバック
            DB::rollBack();

            // dd($e);

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 一覧
    public function index(Request $request)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 入力データの取得
        $inputData = $request->all();

        // 自分のidを検索条件に入れる
        $inputData['user_id'] = $userData->id;

        try {
            // リスト取得
            $productRows = $this->productService->getList($inputData);

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $productRows->load(['category', 'colorOptions', 'sizeOptions', 'subImages', 'mainImage']);

            // 返す
            return $this->sendResponse($productRows);

         } catch (Exception $e) {

            //  エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件取得
    public function  show(Request $request, $id)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // 自分の商品でなければエラー
        if($productRow->user_id!=$userData->id) return $this->sendNotFoundErrorResponse();

        try {
            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $productRow->load(['category', 'colorOptions', 'sizeOptions', 'subImages', 'mainImage']);

            // 返す
            return $this->sendResponse($productRow);

         } catch (Exception $e) {

            // エラーを返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 更新--post
    public function update(Request $request, $id)
    {
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // 自分の商品でなければエラー
        if($productRow->user_id!=$userData->id) return $this->sendNotFoundErrorResponse();

        // バリデート
        if($val=$this->myProductValidator->update($inputData)) return $this->sendValidateErrorResponse($val);

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$this->productService->updateItemById($id, $inputData)) throw new Exception(__('messages.faild_update'));
            // メイン画像があれば保存
            if(!empty($inputData['up_main_file'])) {
                // メイン画像ファイルの保存
                $createMainFilePath = $this->fileService->saveImageByBase64($inputData['up_main_file'], self::$saveFileDir);

                // メイン画像ファイルのデータ取得
                $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);

                // メイン画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createMainImageByProductIdAndImageData($id, $upMainFileData)) throw new Exception(__('messages.faild_create'));
            }

            // 画像があれば保存
            if(!empty($inputData['up_sub_files'])) {
                // 画像ファイルの保存
                $createFilePaths = $this->fileService->saveImagesByBase64($inputData['up_sub_files'], self::$saveFileDir);

                // 画像ファイルのデータ取得
                $upFileData = $this->upFileService->getImagesData($createFilePaths, self::$saveFileUrl);

                // 画像ファイルのデータをデータベースに保存する
                if(!$this->productService->createImagesByProductIdAndImagesData($id, $upFileData)) throw new Exception(__('messages.faild_create'));
            }

            // オプションがあればオプションを複数作成または複数更新する
            if(!empty($inputData['options'])) {
                if(!$results = $this->productOptionService->updateOrCreateItemsByProductId($id, $inputData['options'])) throw new Exception(__('messages.faild_create'));
            }

            // オプションが削除
            if(!empty($inputData['delete_options'])) {
                // 画像ファイルのデータの削除
                if(!$this->productOptionService->deleteItemsByIds($inputData['delete_options'])) throw new Exception(__('messages.faild_create'));
            }

            // 商品取得
            $newProductRow = $this->productService->getItem(['id'=>$id]);

            // 詳細を取得(遅延)。リレーションをまだロードしていない場合のみロードする場合は、loadMissing([''])
            $newProductRow->load(['category', 'colorOptions', 'sizeOptions', 'subImages', 'mainImage']);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newProductRow);
        } catch (Exception $e) {

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 1件削除
    public function destroy(Request $request, $id)
    {
        // ユーザーデータの取得
        $userData = $request->user();

        // 商品の取得
        if(!$productRow = $this->productService->getItemById($id)) return $this->sendNotFoundErrorResponse();

        // 自分の商品でなければエラー
        if($productRow->user_id!=$userData->id) return $this->sendNotFoundErrorResponse();

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


}
