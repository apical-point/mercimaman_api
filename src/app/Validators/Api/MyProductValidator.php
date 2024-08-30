<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;

// サービス
// use App\Services\UserService;

class MyProductValidator extends BaseValidator
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
    public function store($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$registProduct);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }

    // 更新
    public function update($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$updateProduct);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }



}