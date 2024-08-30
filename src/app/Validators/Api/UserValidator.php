<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;
use Illuminate\Support\Facades\Log;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

// サービス
use App\Services\UserService;

class UserValidator extends BaseValidator
{
    // サービス
    protected $userService;

    public function __construct(
        UserService $userService

    ){
        parent::__construct();

        // サービス
        $this->userService = $userService;
    }

    // ユーザー更新
    public function update($userId, $inputData)
    {
        // ユーザー取得
        if(!$user = $this->userService->getItemById($userId)) return true;

        // エラー配列の定義
        $val = [];

        if ($inputData['check'] == "1"){

            if ($inputData['prefecture_id'] == 48){
                $v = Validator::make($inputData, ValidateCheckArray::$updateClient2);
                if ($v->fails()) $val = $v->errors()->toArray();
            }else{
                $v = Validator::make($inputData, ValidateCheckArray::$updateClient);
                if ($v->fails()) $val = $v->errors()->toArray();
            }

            //子供の情報のチェック
            if( (!empty($inputData["child_birthday1"]) && empty($inputData["child_gender1"])) || (empty($inputData["child_birthday1"]) && !empty($inputData["child_gender1"]))   ){
                $val['child_gender1'][] = __('messages.birthday_gender_error');
            }

            if( (!empty($inputData["child_birthday2"]) && empty($inputData["child_gender2"])) || (empty($inputData["child_birthday2"]) && !empty($inputData["child_gender2"]))   ){
                $val['child_gender2'][] = __('messages.birthday_gender_error');
            }

            if( (!empty($inputData["child_birthday3"]) && empty($inputData["child_gender3"])) || (empty($inputData["child_birthday3"]) && !empty($inputData["child_gender3"]))   ){
                $val['child_gender3'][] = __('messages.birthday_gender_error');
            }

            if( (!empty($inputData["child_birthday4"]) && empty($inputData["child_gender4"])) || (empty($inputData["child_birthday4"]) && !empty($inputData["child_gender4"]))   ){
                $val['child_gender4'][] = __('messages.birthday_gender_error');
            }

            if( (!empty($inputData["child_birthday5"]) && empty($inputData["child_gender5"])) || (empty($inputData["child_birthday5"]) && !empty($inputData["child_gender5"]))   ){
                $val['child_gender5'][] = __('messages.birthday_gender_error');
            }

        }else if( $inputData['check'] == "2"){
            //プロフィール
            $v = Validator::make($inputData, ValidateCheckArray::$registProfile);
            if ($v->fails()) $val = $v->errors()->toArray();

        }else if ($inputData['check'] == "3"){
            //銀行
            $v = Validator::make($inputData, ValidateCheckArray::$registBank);
            if ($v->fails()) $val = $v->errors()->toArray();

            //名前に途中空白があるかチェック
            $list = explode( " ", $inputData["bank_name"]);

            if(strpos($inputData["bank_name"], " ") === false && strpos($inputData["bank_name"], "　") === false ){
                $val['bank_name'][] = __('messages.bank_name');
            }

        }else if ($inputData['check'] == "4"){
            //non
        }
        return $val;
    }


}
