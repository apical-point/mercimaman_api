<?php

namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Google_Client;

// リポジトリ
use App\Repositories\UserDetailRepositoryInterface;
use PHPUnit\Util\Json;
use Illuminate\Support\Facades\Storage;

class NotifyService extends Bases\BaseService
{
    protected $userDetailRepo;

    public function __construct(
        UserDetailRepositoryInterface $userDetailRepo
    ) {
        $this->userDetailRepo = $userDetailRepo;
    }
    //region 定数定義

    //region 通知種別

    //-- 運営からのお知らせ ---
    public const KIND_ADMIN_NOTIFICATION = '1';

    //-- メルママメイトからメッセージが届いた時 ---
    public const KIND_RECEIVE_MESSAGE = '2';

    //-- 商品にコメントがついた時 ---
    public const KIND_PRODUCT_COMMENT = '3';

    //-- 商品が購入されたとき ---
    public const KIND_PRODUCT_BUY = '4';

    //-- ツイートコメントに返信 ---
    public const KIND_TWEET_REP = '5';


    /** 種別に対応するメッセージのbody */
    private const MESSAGES = [
        //-- 運営からのお知らせ ---
        self::KIND_ADMIN_NOTIFICATION => 'お知らせが届きました',

        //-- メルママメイトからメッセージが届いた時 ---
        self::KIND_RECEIVE_MESSAGE => 'メルママメイトからメッセージが届きました',

        //-- 商品にコメントがついた時 ---
        self::KIND_PRODUCT_COMMENT => '出品商品にコメントがつきました',

        //-- 商品が購入されたとき ---
        self::KIND_PRODUCT_BUY => '出品商品が購入されました',

         //-- ツイートコメントに返信 ---
        self::KIND_TWEET_REP => 'コメントに返信がありました',


    ];

    /** 通知メッセージのタイトル */
    private const TITLE = 'MerciMaman!';

    //endregion 通知種別

    //endregion 定数定義


    /**
     * 通知メッセージの送信
     * $toで指定したユーザーに対して、$kindに対応する通知メッセージを送信する。
     * ※$kindには文字列型の数字を指定する事。数値型を指定すると送信されない
     *
     * tinkerを利用したテスト方法
     * % php artisan tinker
     * >>> app('App\Services\NotifyService')->sendNotify("6", [189]) // ユーザーID 189に管理者からのお知らせを送信
     * >>> app('App\Services\NotifyService')->sendNotify("12", [189], ["result_join" => "1"]) // ユーザーID 189に入会審査結果(OK)を送信
     * >>> exit
     * ※ tinkerは起動時にソースを読み込むので、起動後にソースを修正した場合、反映されない。
     * ソース修正後は必ずtinkerを再起動する
     *
     * @param string $kind 通知の種別 NotifiyService::KIND_*
     * @param array $to 通知先のユーザーIDの配列
     * @return 送信したメッセージの件数
     */
    public function sendNotify($kind, $to, $data = null)
    {
        $result = false;

        if (!isset(self::MESSAGES[$kind])){
            return 0;
        }

        \Log::debug($to);
        \Log::debug("PushStart");
        $deviceIds = $this->getDeviceIds($to);

        // print_r($deviceIds);
//        \Log::debug($deviceIds);

        $sended = 0;
        //print_r($data);
        //\Log::debug($data);

        $data['kind'] = $kind;
        //print_r($data);
        //\Log::debug($data);

        $senderId = config('const.site.FCM_SENDER_ID'); // 送信者ID
        $privateKeyFile = config('const.site.FCM_PRIVATE_KEY');
        //print_r($privateKeyFile . "\n");

        $client = new Google_Client();

        $client->useApplicationDefaultCredentials();

        $authDataList = json_decode(Storage::get($privateKeyFile), true);
 //\Log::debug($authDataList);
        $client->setAuthConfig($authDataList);

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');


        $httpClient = $client->authorize();
 //\Log::debug($httpClient);
        foreach ($deviceIds as $key => $deviceId) {
            try {
                // 通知メッセージ
                $notification = [
                    'message' => [
                        'token' => '/3/device/'.$deviceId,
                        "notification" => [
                            'title' => self::TITLE,
                            'body' => self::MESSAGES[$kind],
                        ],
                        'data' => $data,
                        'android' => [
                            "ttl" => "86400s",
                            "notification" => [
                                "click_action" => "",
                            ],
                        ],
                        'apns' => [
                            "headers" => [
                                "apns-priority" => "5",
                            ],
                            'payload' => [
                                'aps' => [
                                    "content-available" => 1,
                                    "category" => ""
                                ],
                            ],
                        ],
                    ],
                ];

                $fcmApi = 'https://fcm.googleapis.com/v1/projects/' . $senderId . '/messages:send';

                \Log::debug($notification);

                // 通知メッセージの送信
                $result = $httpClient->post($fcmApi, ['json' => $notification]);
                // print_r($result);
                \Log::debug("FCM コード");
                \Log::debug($result->getStatusCode());

                if ($result->getStatusCode() == 200){
                    $sended ++;
                }
            } catch (\Exception $e) {
                // print_r($e->getMessage());
                // print_r($e->getTraceAsString());
                throw $e;
            }
        }
        return $sended;
    }

    /**
     * データメッセージの送信
     * $toで指定したユーザーに対して、$kindに対応するデータメッセージを送信する。
     * ※$kindには文字列型の数字を指定する事。数値型を指定すると送信されない
     *
     * @param string $kind 通知の種別 NotifiyService::KIND_*
     * @param array $to 通知先のユーザーIDの配列
     * @return 送信したメッセージの件数
     */
    public function sendData($kind, $to, $data = null)
    {
        $result = false;

        if (!isset(self::MESSAGES[$kind])){
            return 0;
        }
        $deviceIds = $this->getDeviceIds($to);

       //print_r($deviceIds);

        $sended = 0;
        // print_r($data);
        $data['kind'] = $kind;
        // print_r($data);
        $senderId = config('const.site.FCM_SENDER_ID'); // 送信者ID
        $privateKeyFile = config('const.site.FCM_PRIVATE_KEY');
        // print_r($privateKeyFile . "\n");
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();

        $authDataList = json_decode(Storage::get($privateKeyFile), true);

        $client->setAuthConfig($authDataList);

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $httpClient = $client->authorize();

        foreach ($deviceIds as $key => $deviceId) {
            try {
                // データメッセージ　※データメッセージを送信する場合の形式はこちら
                $dataMessage = [
                    'message' => [
                        'token' => $deviceId,
                        'android' => [
                            'priority' => 'HIGH',
                            'data' => [
                                'title' => self::TITLE,
                                'body' => self::MESSAGES[$kind],
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'alert' => [
                                        'title' => self::TITLE,
                                        'body' => self::MESSAGES[$kind],
                                    ],
                                ],
                            ],
                        ],
                        'data' => $data,
                    ],
                ];

                $fcmApi = 'https://fcm.googleapis.com/v1/projects/' . $senderId . '/messages:send';
                // print_r($fcmApi . "\n");

                // print_r($dataMessage);
                // 通知メッセージの送信
                $result = $httpClient->post($fcmApi, ['json' => $dataMessage]);
                // print_r($result);
                if ($result->getStatusCode() == 200){
                    $sended ++;
                }
            } catch (\Exception $e) {
                // print_r($e->getMessage());
                // print_r($e->getTraceAsString());
                throw $e;
            }
        }
        return $sended;
    }

    /**
     *  対象ユーザーがあり、プッシュ通知OKの場合、デバイスIDを取得。
     * デバイスIDにdummy以外の値があれば送信対象
     *  ※ device_idのdummyはプレビュー表示やmonacaデバッガ等のプッシュ通知のプラグインが利用できない場合に設定される
     * @param array $userIds 送信対象となるユーザーのID
     * @param array 送信対象となるデバイスIDの配列
     */
    private function getDeviceIds($userIds)
    {
        $deviceIds = [];
        foreach ($userIds as $userId) {

            // 対象ユーザーがあり、プッシュ通知OKの場合、デバイスIDを取得。デバイスIDにdummy以外の値があれば送信対象
            // ※ device_idのdummyはプレビュー表示やmonacaデバッガ等のプッシュ通知のプラグインが利用できない場合に設定される
            //user = $this->userRepo->getItemById($userId);
            $user = $this->userDetailRepo->getItem(['user_id'=>$userId]);
            if(!empty($user->device_id)){
                $deviceIds[] = $user->device_id;
            }
        }
        return $deviceIds;
    }
}
