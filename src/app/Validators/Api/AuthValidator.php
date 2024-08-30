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
use App\Services\OrderService;

class AuthValidator extends BaseValidator
{
    // サービス
    protected $userService;
    protected $orderService;

    public function __construct(
        UserService $userService,
        OrderService $orderService

    ){
        parent::__construct();

        // サービス
        $this->userService = $userService;
        $this->orderService = $orderService;
    }


    // 仮登録前のメールチェック
    public function authEmail($inputData) {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$authEmail);
        if ($v->fails()) $val = $v->errors()->toArray();

        // メアドの使用チェック
        if(!$this->userService->canUseEmailOfEntry($inputData['email'])) $val['email'][] = __('messages.duplicate_regist_email');

        return $val;
    }

    // 仮登録
    public function entry($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$entry);
        if ($v->fails()) $val = $v->errors()->toArray();

        // メアドの使用チェック
        if($inputData['email'] != $inputData['re_email']) $val['email'][] = __('messages.re_email_regist');

        // メアドの使用チェック
        if(!$this->userService->canUseEmailOfEntry($inputData['email'])) $val['email'][] = __('messages.duplicate_regist_email');

        return $val;
    }

    // 新規登録
    public function regist($userId, $inputData)
    {
        // ユーザー取得
        if(!$user = $this->userService->getItemById($userId)) return true;

        // エラー配列の定義
        $val = [];

        if ($inputData['check'] == "1"){
            if ($inputData['prefecture_id'] == 48){
                $v = Validator::make($inputData, ValidateCheckArray::$registClient2);
                if ($v->fails()) $val = $v->errors()->toArray();
            }else{
                $v = Validator::make($inputData, ValidateCheckArray::$registClient);
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

            // 紹介IDのチェック
            if (isset($inputData["referral_code"])){
                if (!$user = $this->userService->getItemById($inputData["referral_code"])){
                    $val['referral_code'][] = __('messages.referral_code_err');
                }
            }



        }else{
            $v = Validator::make($inputData, ValidateCheckArray::$registProfile);
            if ($v->fails()) $val = $v->errors()->toArray();

        }

        return $val;
    }

    // 更新
    public function update($userId, $inputData)
    {
        // ユーザー取得
        if(!$user = $this->userService->getItemById($userId)) return true;

        // エラー配列の定義
        $val = [];

        if ($inputData['receive_flg'] == "1"){
            $v = Validator::make($inputData, ValidateCheckArray::$updateClientReceiver);
            if ($v->fails()) $val = $v->errors()->toArray();
        }else{
            $v = Validator::make($inputData, ValidateCheckArray::$updateClient);
            if ($v->fails()) $val = $v->errors()->toArray();
        }

        return $val;
    }


    // パスワードの更新
    public function updatePassword($inputData, $hashedPassword) {
        // エラー配列の定義
        $val = [];

        $v = Validator::make($inputData, ValidateCheckArray::$updatePassword);
        if ($v->fails()) $val = $v->errors()->toArray();

        // パスワードのチェック
        if(!Hash::check($inputData['current_password'], $hashedPassword)) $val['current_password'][] = __('messages.is_not_now_password');

        return $val;
    }

    // メールアドレスの更新
    public function updateEmail($userId, $inputData)
    {
        // エラー配列の定義
        $val = [];

        $v = Validator::make($inputData, ValidateCheckArray::$updateEmail);
        if ($v->fails()) $val = $v->errors()->toArray();

        // メアドの使用チェック
        if(!empty($inputData['email']) && !$this->userService->canUseEmailOfUpdate($userId, $inputData['email'])) $val['email'][] = __('messages.duplication_email');

        return $val;
    }

    // StripeId
    public function setStripeId($userId, $inputData) {
        // エラー配列の定義
        $val = [];

        $v = Validator::make($inputData, ValidateCheckArray::$setStripeId);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }

    // パスワードリセットのメールチェック
    public function authEmailOnly($inputData) {
        // エラー配列の定義
        $val = [];

        $v = Validator::make($inputData, ValidateCheckArray::$authEmail);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }

    // パスワードリセットのチェック
    public function resetPassword($inputData) {
        // エラー配列の定義
        $val = [];

        $v = Validator::make($inputData, ValidateCheckArray::$resetPassword);
        if ($v->fails()) $val = $v->errors()->toArray();

        return $val;
    }

    // 退会
    public function withdrawal($inputData , $user) {
        // エラー配列の定義
        $val = [];

        $v = Validator::make($inputData, ValidateCheckArray::$withdrawal);
        if ($v->fails()) $val = $v->errors()->toArray();

        // 取引中のデータのチェック
        //販売者
        $search['seller_user_id'] = $inputData['user_id'];
        $search['status'] = array("1","2","3");
        $data = $this->orderService->getList($search);
        $list = $data->toArray();
        if ($list['total'] > 0) $val['withdrawal'][] = __('messages.withdrawal_order_error');

        //購入者
        $search = [];
        $search['buyer_user_id'] = $inputData['user_id'];
        $search['status'] = array("1","2","3");
        $data = $this->orderService->getList($search);
        $list = $data->toArray();
        if ($list['total'] > 0) $val['withdrawal'][] = __('messages.withdrawal_order_error');

        //売上金
        $search = [];
        $search['buyer_user_id'] = $inputData['user_id'];
        $search['status'] = "4";
        $data = $this->orderService->getList($search);
        $list = $data->toArray();
        if ($list['total'] > 0 && $user['user_detail']['bank_code'] == "") $val['withdrawal'][] = __('messages.withdrawal_payment_error');

        return $val;
    }

}
