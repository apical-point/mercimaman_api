<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\UserService;
use App\Services\UserDetailService;

// ライブラリ
use App\Library\Stripe;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

class StripeController extends Bases\ApiBaseController {
    // リクエスト
    protected $request;

    // サービス
    protected $userService;
    protected $userDetailService;

    public function __construct(
        Request $request,
        UserService $userService,
        UserDetailService $userDetailService
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);

        // サービス
        $this->userService = $userService;
        $this->userDetailService = $userDetailService;
    }

    // カード取得
    public function getCard(Request $request) {
        // ユーザーデータの取得
        $userData = $request->user();

        // 再取得
        $userRow = $this->userService->getItemById($userData->id);

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail']);

        $cards = [];

        $stripeId = $userRow->userDetail->stripe_id;

        // StripeIdがあれば
        if ($stripeId) {
            $response = Stripe::getCardsStripe($stripeId);

            if ($response['result'] !== false) {
                $cards = $response['cards'];
            }
        }

        // 返す
        return $this->sendResponse(['cards' => $cards]);
    }

    // カード作成or更新
    public function setCard(Request $request) {
        // データ定義
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // 再取得
        $userRow = $this->userService->getItemById($userData->id);

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail']);

        $card = [];

        $stripeId = $userRow->userDetail->stripe_id;

        // StripeIdがあれば
        if ($stripeId) {
            // アップデート
            $response = Stripe::updateCardStripe($stripeId, $inputData['stripeToken']);
            if ($response['result'] === false) {
                // 致命的なエラーはここで止める
                return $this->sendErrorResponse([], $response['msg']);
            } else {
                $cards[] = $response['card'];
            }
        } else {
            // 顧客IDがない(新規登録)
            $response = Stripe::registerCustomerStripe($inputData['stripeToken'], $userRow->email, $userRow->userDetail->last_name.$userRow->userDetail->first_name);

            if ($response['result'] === false) {
                // 致命的なエラーはここで止める
                return $this->sendErrorResponse([], $response['msg']);
            } else {
                // $customer->id を顧客IDとしてDBに保持
                $customer = $response['customer'];

                // 更新を行う
                if (!$this->userDetailService->updateItemByUserId($userRow->id, ['stripe_id' => $customer->id])) {
                    // 失敗時
                    Stripe::deleteCustomrStripe($customer->id);
                    return $this->sendErrorResponse([], __('messages.failed_stripe'));
                }

                // 成功時
                $cards = $customer;
                //$cards = $customer->sources->data;
            }
        }

        // 返す
        return $this->sendResponse(['cards' => $cards]);
    }

    // カード消去
    public function deleteCard(Request $request) {
        // データ定義
        $inputData = $request->all();

        // ユーザーデータの取得
        $userData = $request->user();

        // 再取得
        $userRow = $this->userService->getItemById($userData->id);

        // 欲しいデータのみここで呼び出すと入れ子にしてくれる。(遅延読み込み) loadMissing()はすでに読み込んでいるものは読み込まない
        $userRow->loadMissing(['userDetail']);

        $cards = [];

        $stripeId = $userRow->userDetail->stripe_id;

        $response = Stripe::deleteCardStripe($stripeId, $inputData['cardId']);
        if ($response['result'] === false) {
            // 致命的なエラーはここで止める
            return $this->sendErrorResponse([], $response['msg']);
        }

        // 返す
        return $this->sendResponse([[], __('messages.success_delete')]);
    }
}
