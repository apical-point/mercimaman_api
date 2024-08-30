<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;
use Illuminate\Support\Facades\Log;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;

// サービス
// use App\Services\UserService;

class OrderValidator extends BaseValidator
{
    // サービス
    // protected $userService;

    public function __construct(
        // UserService $userService
    ){
        parent::__construct();

        // サービス
        // $this->userService = $userService;
    }

    // 登録
    public function store($inputData, $productRows, $pointRow)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$createOrder);
        if ($v->fails()) $val = $v->errors()->toArray();

        // 最後にstatusチェック
        if($productRows['status'] != "2"){
            $val['product_id'][] = __('messages.product_already_bought');
        }

        // ポイントのチェック
        //自分のポイントを取得
        if(!empty($inputData['point'])  && $inputData['point'] >  $pointRow[0]['total']){
            $val['point'][] = __('messages.product_point');
        }

        return $val;
    }

    // 登録
    public function storeFromCart($inputData) {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$cart);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }

    // 月・店舗別検索機能付き売り上げリスト
    public function salesList($inputData) {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$salesList);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }

    // 月・店舗別検索機能付き売り上げリスト
    public function salesDetails($inputData) {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$salesDetails);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }


    //更新
     public function update($inputData)
     {
         // エラー配列の定義
         $val = [];

         // 入力のバリデート--共通の項目
         if ($inputData['order_status'] == "3"){
             $v = Validator::make($inputData, ValidateCheckArray::$order1Update);
             if ($v->fails()) $val = $v->errors()->toArray();
         }

         return $val;
     }

     //匿名配送　送料更新
     public function updatePostage($inputData)
     {
         // エラー配列の定義
         $vala= [];

         // 入力のバリデート--共通の項目
         foreach($inputData as $key=>$val){
             $check["postage"] = $val;
             $v = Validator::make($check, ValidateCheckArray::$orderPostage);
             if ($v->fails()) $vala = $v->errors()->toArray();
         }

         return $vala;
     }



}
