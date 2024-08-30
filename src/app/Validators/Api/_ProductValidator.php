<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;
use Illuminate\Support\Facades\Log;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;


class ProductValidator extends BaseValidator
{

    public function __construct(

    ){
        parent::__construct();

    }

    public function store($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$registProduct);
        if ($v->fails()) $val = $v->errors()->toArray();

        //メイン画像のチェック
        if (!isset($inputData["up_main_file"]) || (isset($inputData["up_main_file"]) && $inputData["up_main_file"] == "") ){
            $val['up_main_file'][] = __('messages.product_mein_file');
        }

        //発送方法
        if ($inputData["shipping_method"] == "1" || $inputData["shipping_method"] == "2" || $inputData["shipping_method"] == "3" ){
            $val['shipping_method'][] = __('messages.shipping_method');
        }

        //手渡しのときの発送エリアチェック
        if ($inputData["shipping_method"] != "13" && empty($inputData["shipping_area"])){
            $val['shipping_area'][] = __('messages.shipping_area');
        }

        return $val;
    }

    public function update($id, $inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$updateProduct);
        if ($v->fails()) $val = $v->errors()->toArray();

        //メイン画像のチェック
        if ((isset($inputData["up_main_delete"]) && $inputData["up_main_delete"] == "1") && (!isset($inputData['up_main_file']) || empty($inputData['up_main_file']))){
            $val['up_main_file'][] = __('messages.product_mein_file');
        }

        //発送方法
        if ($inputData["shipping_method"] == "1" || $inputData["shipping_method"] == "2" || $inputData["shipping_method"] == "3" ){
            $val['shipping_method'][] = __('messages.shipping_method');
        }

        //手渡しのときの発送エリアチェック
        if ($inputData["shipping_method"] != "13" && empty($inputData["shipping_area"])){
            $val['shipping_area'][] = __('messages.shipping_area');
        }


        return $val;
    }

    public function message($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$createProductMassage);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }
}
