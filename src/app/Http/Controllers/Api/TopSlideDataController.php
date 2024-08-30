<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use App\Repositories\Eloquent\Models\SecondBanner;
use App\Repositories\Eloquent\Models\SecondBannerImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\TopSlideDataService;
use App\Services\FileService;
use App\Services\UpFileService;

// バリデートの配列
//use App\Libraries\ValidateCheckArray;


class TopSlideDataController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/content/';
    private static $saveFileUrl = 'content/';

    // サービス
    protected $secondBanner;
    protected $topSlideDataService;
    protected $fileService;
    protected $upFileService;

    public function __construct(
        // サービス
        SecondBannerImage $secondBannerImage,
        SecondBanner $secondBanner,
        TopSlideDataService $topSlideDataService,
        FileService $fileService,
        UpFileService $upFileService
    ){
        parent::__construct();

        // サービス
        $this->secondBannerImage = $secondBannerImage;
        $this->secondBanner = $secondBanner;
        $this->topSlideDataService = $topSlideDataService;
        $this->fileService = $fileService;
        $this->upFileService = $upFileService;
    }

    //一覧取得
    public function index(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        //ルーティング追加ができないので、暫定的にスライダー取得処理とエンドポイントを共有
        //bannerのget処理　管理画面とトップページで二回使用
        if(request()->has('type')) {
            $registeredBanner = $this->getBanner();
            $registeredBanner->loadMissing(['images']);
            return $this->sendResponse($registeredBanner);
        }

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得
        $inputData = $request->all();

        // ユーザーデータの取得
        //$userData = $request->user();

        try {

            //検索情報取得
            $arr = $this->topSlideDataService->getList($inputData);
            $arr->loadMissing(['images']);

            return $this->sendResponse($arr);

        } catch (Exception $e) {
            Log::debug($e);
            return $this->sendExceptionErrorResponse($e);

        }
    }

    public function getBanner()
    {
        $registeredBanner = $this->secondBanner->getBannerById(1);
        return $registeredBanner;
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

        $arr = $this->topSlideDataService->getItemById($id);
        $arr->loadMissing(['images']);

        if(!$arr){
            return $this->sendErrorResponse([], __('messages.not_found'));
        }

        // 返す
        return $this->sendResponse($arr, __('messages.success'));

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
        //$v = Validator::make($inputData, ValidateCheckArray::$event);

        if ($v->fails()) return $this->sendValidateErrorResponse($v->errors()->toArray());


        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            // 登録処理
            if(!$newData=$this->topSlideDataService->createItem($inputData)) throw new Exception(__('messages.faild_create'));

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
     //ルーティング追加ができないため暫定的にスライダーと処理を共有
     //pattern A: トップスライダー画面から更新ボタン押下
     //pattern B: バナー画面から更新ボタン押下
     //A, Bの分岐は、hiddenで持たせた['banner']があるかどうかで行う
     public function update(Request $request, $id)
    {


        // キーのチェック
        $this->requestKeyCheck($request);

        // 入力データの取得
        $inputData = $request->all();

        //banner画面から更新ボタンを押下すると、以下の処理に移る
        if(array_key_exists('banner', $inputData)) {
            try {
                $arr = $this->updateBannerAndImage($id, $inputData);
                $arr->loadMissing(['images']);
            } catch(Exception $e) {
                $this->sendExceptionErrorResponse($e);
            }
            return $this->sendResponse($arr, __('messages.success'));
        }


        // ユーザーデータの取得
        $userData = $request->user();

        // バリデート

        // *-*-*-*-*-*-*-*-*-*-*- DB操作 -*-*-*-*-*-*-*-*-*-*-*
        DB::beginTransaction();
        // 更新
        try {

            $arr = $this->topSlideDataService->updateItem(['id'=>$id], $inputData);

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
                    if(!$this->topSlideDataService->updateOrCreateImageData($id, $upMainFileData, $wh)) throw new Exception(__('messages.faild_create'));
                }

            }


            // コミット
            DB::commit();

            // 返す
            return $this->sendResponse($arr, __('messages.success'));

        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();

            // 返す
            return $this->sendExceptionErrorResponse($e);
        }

    }

    //pattern A: バナーのデータが送信されているかどうか
    //pattern B: 既にバナーが登録されているか
    //A,Bそれぞれについて、pc.spで分岐
    public function updateBannerAndImage($id, $inputData) {
        $pcPath = '';
        $spPath = '';

        if ($inputData['image_pc']) {
            $encodedPcBanner = $inputData['image_pc'];
            $fileName = 'pc_banner.jpg';
            $pcPath = $this->fileService->saveBannerImageToStorage($encodedPcBanner, $fileName);
        }

        if ($inputData['image_sp']) {
            $encodedSpBanner = $inputData['image_sp'];
            $fileName = 'sp_banner.jpg';
            $spPath = $this->fileService->saveBannerImageToStorage($encodedSpBanner, $fileName);
        }


        DB::beginTransaction();
        try {

            $arr = $this->secondBanner->updateBanner($id, $inputData);
            $pcBanner = $this->secondBannerImage->getPcBanner($id);
            $spBanner = $this->secondBannerImage->getSpBanner($id);

            //PC用バナー：更新
            if($pcBanner) {
                $pcBanner->update([
                    'image_url' => $pcPath,
                    'updated_at' => now(),
                ]);
            } else {
                $pcImage = new SecondBannerImage();
                $pcImage->fill([
                    'second_banner_id' => $id,
                    'image_url' => $pcPath,
                    'type' => '0',
                    'name' => 'pc_banner',
                ]);
                $pcImage->save();
            }

            //SP用バナー：更新
            if($spBanner) {
                $spBanner->update([
                    'image_url' => $spPath,
                    'updated_at' => now(),
                ]);
            } else {
                //SP用バナー：新規登録
                $spImage = new SecondBannerImage();
                $spImage->fill([
                    'second_banner_id' => $id,
                    'image_url' => $spPath,
                    'type' => '1',
                    'name' => 'sp_banner',
                ]);
                $spImage->save();
            }
            DB::commit();

            return $arr;

        } catch (Exception $e) {
            Log::debug($e);
            // ロールバック
            DB::rollBack();
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

             if(!$this->topSlideDataService->deleteItemById($id)) throw new Exception(__('messages.faild_delete'));


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
