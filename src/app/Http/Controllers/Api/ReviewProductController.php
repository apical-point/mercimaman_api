<?php namespace App\Http\Controllers\Api;

// ベース
use App\Http\Controllers\Api\Bases\ApiBaseController;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\ReviewProductService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\ReviewProductCategoryService;
use App\Services\BlockService;
use App\Services\NgWordsService;
use App\Services\PointService;
use App\Services\SiteConfigService;


class ReviewProductController extends ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/reviewProduct/';
    private static $saveFileUrl = 'reviewProduct/';

    // サービス
    protected $reviewProductService;

    public function __construct(
        Request $request,

        // サービス
        ReviewProductService $reviewProductService,
        FileService $fileService,
        UpFileService $upFileService,
        BlockService $blockService,
        NgWordsService $ngWordsService,
        PointService $pointService,
        SiteConfigService $siteConfigService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->reviewProductService = $reviewProductService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;
        $this->blockService = $blockService;
        $this->ngWordsService = $ngWordsService;
        $this->pointService = $pointService;
        $this->siteConfigService = $siteConfigService;
    }


    // リスト取得
    public function index(Request $request)
    {
        $inputData = $request->all();

        try {
            // リスト取得
            $rows = $this->reviewProductService->getList($inputData);
            $rows->loadMissing(['mainImage']);

            // 返す
            return $this->sendResponse($rows,  __('messages.success'));

        } catch (Exception $e) {

            return $this->sendExceptionErrorResponse($e);
        }

    }

    // 指定したIDの情報を返す
    public function getReviewProduct(Request $request)
    {
        $inputData = $request->all();

        //ブロックユーザーの取得
        if(!empty($inputData['login_user_id'])){
            $block_response = $this->blockService->getItems(['user_id' => $inputData['login_user_id']])->toArray();
            $block_array = array_column($block_response, 'to_user_id');

            $block_response_to = $this->blockService->getItems(['to_user_id' => $inputData['login_user_id']])->toArray();
            $block_array_to = array_column($block_response_to, 'user_id');

            $inputData['block_users'] = array_merge($block_array, $block_array_to);

        }
        //口コミ検索処理 route追加できないため、以下のキーで分岐
        /** @var array $inputData
         * -    [
         *       [user_id] => 1714
         *       [order_by_raw] => created_at DESC
         *       [from_myaccount_controller] => 1
         *     ]
         */
        if(!empty($inputData['from_myaccount_controller'])){

            //投稿一覧で口コミ検索したときの処理
            $reviews = $this->reviewProductService->getUserReviews($inputData);

            return $this->sendResponse($reviews, __('messages.success'));
        } else {
            //本来の処理
            $reviewProductRows = $this->reviewProductService->getItemById($inputData);
            $reviewProductRows->loadMissing(['mainImage']);

            return $this->sendResponse($reviewProductRows, __('messages.success'));
        }
    }

    // 登録処理一般ユーザーのみの作成
    public function store(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            // ユーザーデータの登録処理
            if(!$newData=$this->reviewProductService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

            // メイン画像があれば保存
            if(!empty($inputData['up_file'])) {
                foreach($inputData['up_file'] as $key=>$val){
                    // メイン画像ファイルの保存
                    $createMainFilePath = $this->fileService->saveImageByBase64($val, self::$saveFileDir);
                    // メイン画像ファイルのデータ取得
                    $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);
                    // メイン画像ファイルのデータをデータベースに保存する
                    //$status = ($key == 1) ? 1 : 0;
                    $wh["name"] =  $upMainFileData["name"];
                    $upMainFileData["v_order"] = $key;
                    if(!$this->reviewProductService->updateOrCreateImageData($newData->id, $upMainFileData, $wh)) throw new Exception(__('messages.faild_create'));
                }
            }

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($newData, __('messages.success_create'));
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }
    }

    // 更新
    public function updateReviewProduct(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            // 更新
            if(!$this->reviewProductService->updateItem(['id'=>$inputData['id']], $inputData)) throw new Exception(__('messages.faild_update'));

            // メイン画像があれば保存
            if(!empty($inputData['up_file'])) {

                foreach($inputData['up_file'] as $key=>$val){
                    // メイン画像ファイルの保存
                    $createMainFilePath = $this->fileService->saveImageByBase64($val, self::$saveFileDir);
                    // メイン画像ファイルのデータ取得
                    $upMainFileData = $this->upFileService->getFileData($createMainFilePath, self::$saveFileUrl);
                    // メイン画像ファイルのデータをデータベースに保存する
                    //$wh["status"] = ($key == 1) ? 1 : 0;
                    if(!empty($inputData['image_id'][$key])){
                        $wh["id"] = $inputData['image_id'][$key];
                    }
                    else{
                        $wh["name"] =  $upMainFileData["name"];
                    }
                    $upMainFileData["v_order"] =  $key;
                    if(!$this->reviewProductService->updateOrCreateImageData($inputData['id'], $upMainFileData, $wh)) throw new Exception(__('messages.faild_create'));
                }

            }

            $search = [
                'id' => $inputData['id'],
                'per_page' => '-1',
                'block_users' => '',
                'order_by_raw' => 'created_at desc'
            ];

            // 取得
            $item = $this->reviewProductService->getItemById($search);

            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($item, __('messages.success_update'));
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

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->reviewProductService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));

            if(!$this->reviewProductService->deleteReviewByProductId($id)) throw new Exception(__('messages.faild_delete'));

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

    // 口コミ削除
    public function deleteReview(Request $request)
    {
        // 入力データの取得
        $inputData = $request->all();

        // DB操作
        DB::beginTransaction();
        try {
            // 削除の実行
            if(!$this->reviewProductService->deleteReviewById($inputData['id'])) throw new Exception(__('messages.faild_delete'));

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

    // 口コミ投稿
    public function postReview(Request $request){

        // 入力データの取得
        $inputData = $request->all();

        $response=$this->ngWordsService->checkNgWords(['word' => $inputData['review']]);
        if(!$response['success']){
            return $this->sendErrorResponse(['含まれてはいけない単語が含まれています。「'.$response['data'].'」'],  __('含まれてはいけない単語が含まれています。「'.$response['data'].'」'));
        }

        // DB操作
        DB::beginTransaction();
        try {

            if(!$newData=$this->reviewProductService->postReview($inputData)) throw new Exception(__('messages.faild_create'));

            $siteConfigList = $this->siteConfigService->getList(['per_page' => '-1'])->toArray();

            $expiration_date = $siteConfigList[16]["value"];

            //ポイントプレゼント
            $point_type = config('const.point_id.REVIEW_POINT_ID')-1;
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
            return $this->sendResponse($newData, __('messages.success_create'));
        } catch (Exception $e) {
            Log::debug($e);

            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }


}
