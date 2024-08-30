<?php

namespace App\Library;

// use \Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;

class Stripe
{
    public function __construct()
    {
    }

    /**
     * 顧客情報を作成しstripeに登録する
     * @param string $token クレジットカードのトークン/stripeから入手
     * @param string $email 顧客のemail
     * @param string $name 顧客の姓名
     * @return array ('result' => 処理ステータス(true:正常終了, false:異常終了), 'customer' => 顧客情報)
     * 顧客情報の仕様
     * https://stripe.com/docs/api/customers/create
     * のRESPONSE
     */
    public static function registerCustomerStripe(string $token, string $email, string $name) : array
    {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        try{
            // 顧客情報作成
            $customer = \Stripe\Customer::create([
                'source' => $token, // クレジットカードトークン
                'email'  => $email, // メールアドレス
                'name'   => $name,  // 顧客の名前
            ]);
            $result = array('result' => true, 'customer' => $customer);
        } catch(\Stripe\Exception\CardException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\RateLimitException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Exception $e) {
            \Log::info($e);
            $result = ['result' => false, 'msg' => $e->getMessage()];
        }
        if ($result['result'] !== true){
            \Log::info(__FILE__.':'.__FUNCTION__.'() : '.__LINE__);
            \Log::info('token = '.$token.', email = '.$email.', name = '. $name);
            \Log::info($result);
        }
        return $result;
    }

    /**
     * 顧客情報をstripeから削除する
     * @param string $stripeId 対象顧客ID
     * @return array true:正常終了, false:異常終了
     * 顧客情報の仕様
     * https://stripe.com/docs/api/customers/create
     * のRESPONSE
     */
    public static function deleteCustomrStripe(string $stripeId)
    {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        $result = true;
        try{
            // 顧客情報削除
            $customer = \Stripe\Customer::retrieve($stripeId);
            $customer->delete();
        } catch(\Stripe\Exception\CardException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\RateLimitException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Exception $e) {
            \Log::info($e);
            $result = ['result' => false, 'msg' => $e->getMessage()];
        }
        if ($result !== true){
            \Log::info(__FILE__.':'.__FUNCTION__.'() : '.__LINE__);
            \Log::info($stripeId);
            \Log::info($result);
        }
        return $result;

    }

    /**
     * 指定された顧客のカード情報を返す
     * @param string $stripeId 対象顧客ID
     * @param int $max = 1 取得するカード情報の件数
     * @return array ('result' => 処理ステータス(true:正常終了, false:異常終了), 'cards' => カード情報]
     */
    public static function getCardsStripe(string $stripeId, int $max = 1) : array
    {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        try{
            $res = \Stripe\Customer::allSources(
                $stripeId, // 顧客ID
                [
                    'limit'  => $max,      // 最大件数
                    'object' => 'card', // リソース種別
                ]
            );
            $result = array('result' => true, 'cards' => $res['data']);
        } catch(\Stripe\Exception\CardException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\RateLimitException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Exception $e) {
            \Log::info($e);
            $result = ['result' => false, 'msg' => $e->getMessage()];
        }
        if ($result['result'] !== true){
            \Log::info(__FILE__.':'.__FUNCTION__.'() : '.__LINE__);
            \Log::info($stripeId);
            \Log::info($result);
        }
        return $result;
    }

    /**
     * 指定された顧客のカード情報を削除する
     * @param string $stripeId 対象顧客ID
     * @param string $cardId 削除するカード情報のID
     * @return bool 処理ステータス(true:正常終了, false:異常終了)
     */
    public static function deleteCardStripe(string $stripeId, string $cardId)
    {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        try {
            // https://stripe.com/docs/api/cards/delete
            $res = \Stripe\Customer::deleteSource(
                $stripeId, // 顧客ID
                $cardId // カードID
            );
            $result = array('result' => $res['deleted'], 'card' => $res['id']);
        } catch(\Stripe\Exception\CardException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\RateLimitException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Exception $e) {
            \Log::info($e);
            $result = ['result' => false, 'msg' => $e->getMessage()];
        }
        if ($result['result'] !== true){
            \Log::info(__FILE__.':'.__FUNCTION__.'() : '.__LINE__);
            \Log::info('stripeId = '.$stripeId.', cardId = '.$cardId);
            \Log::info($result);
        }
        return $result;
    }

    /**
     * 指定された顧客のカード情報を更新する
     * @param string $stripeId 対象顧客ID
     * @param string $cardToken 更新するカードのトークン
     * @return array 登録したカードの情報(
     * "id" => カードId, "object" => "card", "address_zip" => 郵便番号,  "brand" => カード種別, "customer" => 顧客ID,
     *  "exp_month" => 有効月, "exp_year" => 有効年, "last4" => カード番号下四桁) ※主な項目
     *  取得エラーの場合はnull
     */
    public static function updateCardStripe(string $stripeId, string $cardToken)
    {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        try {
            $res = \Stripe\Customer::createSource(
                $stripeId, // 顧客ID
                [
                    'source' => $cardToken, // クレジットカードトークン
                ]
            );
            $result = array('result' => true, 'card' => $res);
        } catch(\Stripe\Exception\CardException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\RateLimitException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Exception $e) {
            \Log::info($e);
            $result = ['result' => false, 'msg' => $e->getMessage()];
        }
        if ($result['result'] !== true){
            \Log::info(__FILE__.':'.__FUNCTION__.'() : '.__LINE__);
            \Log::info('stripeId = '.$stripeId.', cardToken = '.$cardToken);
            \Log::info($result);
        }

        return $result;
    }

    /**
     * 与信無で決済処理を行う
     * @param array $args (price => 価格, description => 決済時の記述情報, stripeId => 顧客ID/token=>カードID　注：stripeIdとtokenは排他
     * @return array ('result' => 処理ステータス(true:正常終了, false:異常終了), 'chargeId' => チャージID)
     */
    public static function chargeStripe(array $args)
    {

        $result = true;
        $chargeId = null;
        try {
            \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
            $createArgs = [
                'amount'      => $args['price'],       // 金額
                'currency'    => 'jpy',                // 単位
                'description' => $args['description'], // 名目
                'statement_descriptor' => $args['statement_descriptor'], // 明細書表記
            ];
            if (!empty($args['stripeId'])) {
                $createArgs['customer'] = $args['stripeId'];
            } else if (!empty($args['token'])) {
                $createArgs['source'] = $args['token'];
            }
            $charge = \Stripe\Charge::create($createArgs);
            $chargeId = $charge['id'];
            \Log::info($charge);

        } catch(\Stripe\Exception\CardException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\RateLimitException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $result = self::handleStripeError($e->getError()->type, $e->getError()->code);
        } catch (\Exception $e) {
            \Log::info($e);
            $result = ['result' => false, 'msg' => $e->getMessage()];
        }
        if ($result !== true){
            \Log::info(__FILE__.':'.__FUNCTION__.'() : '.__LINE__);
            \Log::info($args);
            \Log::info($result);
        }
        return array ('result' => $result, 'chargeId' => $chargeId);
    }

    /**
     * stripeの例外情報を変換
     * @see https://stripe.com/docs/api/errors
     */
    public static function handleStripeError($type, $code)
    {
        $result = [];
        switch ($type) {
            case 'api_connection_error': // StripeのAPIへの接続の失敗
                $msg = '決済システムに接続できません。時間をおいてください。';
                break;

            case 'api_error': // Stripeのサーバーでの一時的な問題等　※ほとんど起きない
                $msg = '決済システムでエラーとなりました。時間をおいてください。';
                break;

            case 'authentication_error': // 認証エラー
                $msg = 'システムエラー。管理者にお知らせください。';
                break;

            case 'card_error': // 入力されたカードが不正
                $msg = __('messages.stripe_'.$code);
                break;

            case 'idempotency_error': // 要求の一貫性が担保されない
                $msg = 'システムエラー。管理者にお知らせください。';
                break;

            case 'invalid_request_error': // 要求に無効なパラメータが含まれている
                $msg = __('messages.stripe_'.$code);
                if (!$msg){
                    $msg = 'システムエラー。管理者にお知らせください。';
                }
                break;

            case 'rate_limit_error': // 要求の発行頻度が多すぎる
                $msg = 'アクセスが集中しています。時間をおいて再度お試しください。';
                break;

            case 'validation_error': // 入力データ不正
                $msg = __('messages.stripe_'.$code);
                break;

            default:
                $msg = __('messages.failed_stripe');
                break;
        }
        $result = array('result' => false, 'msg' => $msg);

        return $result;
    }

    /**
     * 例外の種別がstripeの例外か否か
     */
    public static function isStripeException(\Exception $e)
    {
        if (is_a($e, '\Stripe\Exception\CardException') ||
            is_a($e, '\Stripe\Exception\RateLimitException') ||
            is_a($e, '\Stripe\Exception\InvalidRequestException') ||
            is_a($e, '\Stripe\Exception\AuthenticationException') ||
            is_a($e, '\Stripe\Exception\ApiConnectionException') ||
            is_a($e, '\Stripe\Exception\ApiErrorException ')){
                return true;
        }
        return false;
    }


    /**
     * 与信/支払いをキャンセルする
     * @param string $chargeId 支払いID
     */
    public static function refundChargeStripe(string $chargeId)
    {

        // 与信/支払いを取り消す
        $res = true;
        try {
            \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
            $result = \Stripe\Refund::create(array(
                'charge' => $chargeId,
            ));
            $res = ($result['status'] == 'succeeded');
        } catch(Exception $e) {
            // error_log("\n".date("Y/m/d H:i:s")." ".__FILE__." ".__LINE__." ".__FUNCTION__."(): e = ".print_r($e, true),3, ERROR_LOG_FILE);
            return array ('result' => false, 'exception' => $e);
        }
        return $res;
    }


}



//region stripe関連

/**
 * 与信処理を行う
 * @param array $args (stripeId => 顧客ID, price => 価格, description => 与信時の記述情報
 * @return array ('result' => 処理ステータス(true:正常終了, false:異常終了), 'chargeId' => チャージID)
 */
/*
function authChargeStripe(array $args)
{
    $res = true;
    try {
        // (1) オーソリ（与信枠の確保）
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        $charge = \Stripe\Charge::create(array(
            'amount' => $args['price'],
            'currency' => 'jpy',
            'description' => $args['description'],
            'customer' => $args['stripeId'],
            'capture' => false, // 与信を指定
        ));
        $chargeId = $charge['id'];
    } catch(Exception $e) {
        // error_log("\n".date("Y/m/d H:i:s")." ".__FILE__." ".__LINE__." ".__FUNCTION__."(): e = ".print_r($e, true),3, ERROR_LOG_FILE);
        $result = handleStripeError($e);
        return $result;
    }
    return array ('result' => $res, 'chargeId' => $chargeId);
}
*/




/**
 * 与信後の支払い請求する
 * @param string $chargeId 請求対象ID
 * @return bool 処理結果 true:正常終了, false:異常終了
 * @throws \Stripe\Exception\ApiErrorException 支払い請求で発生する例外
 */
/*
function captureCharge(string $chargeId)
{
    $res = true;
    try {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        $charge = \Stripe\Charge::retrieve($chargeId);
        $charge->capture();
    } catch(Exception $e) {
        // error_log("\n".date("Y/m/d H:i:s")." ".__FILE__." ".__LINE__." ".__FUNCTION__."(): e = ".print_r($e, true),3, ERROR_LOG_FILE);
        return array ('result' => false, 'exception' => $e);
    }
    return $res;
}
*/

/**
 * 支払情報を取得する
 * @param string $chargeId 請求対象ID
 * @return array (result =>(処理結果 true:正常終了, false:異常終了), 'charge' => 支払情報(charge))
 */
/*
function retrieveCharge(string $chargeId) : array
{
    $res = true;
    $charge = null;
    try {
        \Stripe\Stripe::setApiKey(config('const.site.STRIPE_SECRET_KEY'));
        $charge = \Stripe\Charge::retrieve($chargeId);
    } catch(Exception $e) {
        // error_log("\n".date("Y/m/d H:i:s")." ".__FILE__." ".__LINE__." ".__FUNCTION__."(): e = ".print_r($e, true),3, ERROR_LOG_FILE);
        $res = false;
    }
    return array('result' => $res, 'charge' => $charge);
}
*/

//endregion stripe関連
