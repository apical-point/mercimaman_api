<?php namespace App\Http\Controllers\Api\Bases;
header('Access-Control-Allow-Origin: *');

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

use App\Services\Mst\MstDebugService;

// *-*-*-*-*-*-*-*-*-*-ステータスコード一覧-*-*-*-*-*-*-*-*-*-*
// 200	OK	一般的な正常終了
// 201	Created	新規作成のみで使用。正常終了

// 400	Bad Request	リクエストが不正	期限切れなどもこのエラー
// 401	Unauthorized	認証エラー
// 404	Not Found	リソースが存在しないエラー
// 409	Conflict	作成しようとしたリソースが既にある
// 499	error	ほか、各種エラー	細かい情報はエラー詳細情報として記載する？
// 422  Validate Error バリデーションエラー (ステータスについては参照→https://qiita.com/nesheep5/items/6da796f6ac628c430c36)

// 500	Internal  Server Error	サーバー関連のエラー
// 503	Service  Unavailable	何らかのサービスエラー

// sha1('campbelove')
// b456fabdec2d3d128c08f53e18570e8245dd721e


use Illuminate\Support\Facades\Auth;

class ApiBaseController extends Controller
{
    // api keyの平文
    private static $plainApiKey = 'wxydxbmjep';

    protected $request;
    protected $user;
    protected $inputData;

    public function __construct(
    ){
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     *
     *  この時はデータのみを返すことが多い
     */
    public function sendResponse($data=[], $message='', $status=200)
    {
    	$response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'status' => $status,
        ];

        // 整形したい場合
        return response()->json($response, $status, [], \JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     *
     * この時はエラーの文言のみを返すことが多い
     */
    public function sendErrorResponse($data = [], $message='', $status=499)
    {
    	$response = [
            'success' => false,
            'message' => $message,
            'status' => $status,
        ];

        if(!empty($data)){
            $response['data'] = $data;
        }

        return response()->json($response, $status, [], \JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }



    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     *
     * 受け取ったeで場合分けを行う
     */
    public function sendExceptionErrorResponse($e)
    {
        // データのまとめ
        $message = $e->getMessage();
        $status = $e->getCode();
        $data = method_exists($e, 'getData') ? $e->getData() : [];

        // バリデートエラー
        if($status==422) {

        } elseif($status==404) {

        } elseif($status==500) {

        } else {
            $status = 499;

        }
        return $this->sendErrorResponse($data, $message, $status);
    }

    // throwでないバリデートエラーで使用する。この場合はエラー配列のみを入れることの方が多い
    public function sendValidateErrorResponse($validate=[], $message='', $status=422)
    {
        if(empty($message)) $message = __('messages.faild_validate');

        return $this->sendErrorResponse($validate, $message, $status);
    }

    // 404エラー
    public function sendNotFoundErrorResponse($message='', $status=404)
    {
        if(empty($message)) $message = __('messages.not_found');

        return $this->sendErrorResponse([], $message, $status);
    }

    // 403エラー
    public function send403ErrorResponse()
    {
        abort(403, 'Not Access Deny', ['Content-Type' => 'application/json']);

        return ;
    }

    // キーのチェックを行う
    public function requestKeyCheck($request)
    {
        // headerにX-api-keyを取得
        if(!$apikey = $request->header('X-Api-Key', false)) {
            abort(403, 'API key is not provided', ['Content-Type' => 'application/json']);
        }

        // キーの確認
        if ($apikey != sha1(self::$plainApiKey)) {
            abort(403, 'API key is not matched', ['Content-Type' => 'application/json']);
        }
    }

    // ログインしているかどうか
    // public function isLogin($request)
    // {
    //     if($request->user()) return true;
    //     return false;
    // }

    // 管理者のみのアクセス
    public function accessForAdmin($request)
    {
        $this->accessRestrictions($request, 'adminer');
    }

    // ユーザーレベルによりアクセス制限をかける
    public function accessRestrictions($request, $userLebelName)
    {
        // アクセスの配列取得
        $userLevelArray = config('const.user_level');

        // 指定された権限
        $accessLevel = $userLevelArray[$userLebelName];

        // ログインしているユーザーの権限取得
        $userLevel = $request->user()->user_level;

        // 指定したものより以下ならばエラー
        if($accessLevel < $userLevel) abort(403, 'Not Access Deny', ['Content-Type' => 'application/json']);
    }

}

