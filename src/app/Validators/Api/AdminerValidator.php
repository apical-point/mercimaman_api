<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;

// サービス
use App\Services\AdminerService;

class AdminerValidator extends BaseValidator
{
    // サービス
    protected $adminerService;

    public function __construct(
        AdminerService $adminerService
    ){
        parent::__construct();

        // サービス
        $this->adminerService = $adminerService;
    }

    public function store($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$createAdminer);
        if ($v->fails()) $val = $v->errors()->toArray();

        // メアドの使用チェック
        if(!$this->adminerService->canUseEmailOfCreate($inputData['email'])) $val['email'][] = __('messages.duplicate_regist_email');

        return $val;
    }

    public function update($id, $inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--それぞれの項目
        $v = Validator::make($inputData, ValidateCheckArray::$updateAdminer);
        if ($v->fails()) $val = $v->errors()->toArray();

        // メアドの使用チェック
        if(!$this->adminerService->canUseEmailOfUpdate($id, $inputData['email'])) $val['email'][] = __('messages.duplicate_regist_email');

        return $val;
    }

    public function updatePassword($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$updatePassword);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;

    }


}
