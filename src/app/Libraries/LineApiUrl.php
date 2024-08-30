<?php namespace App\Libraries;


class LineApiUrl
{
    // public static $baseUrl='';

    public function __construct(
    ) {
        parent::__construct();

        // self::$baseUrl = config('const.site.MAIL_SEND_FLG');
    }

    public static function getBaseUrl()
    {
        return config('const.line.LINE_BASE_API_URL');
    }


    // ブロードキャストメッセージを送る
    public static function messageBroadcast()
    {
        return self::getBaseUrl().'bot/message/broadcast';
    }



    // プッシュメッセージを送る フリープランまたはベーシックプランのLINE＠アカウントでは使用できません。
    public static function messagePush()
    {
        return self::getBaseUrl().'bot/message/push';
    }


    // プッシュメッセージを送る フリープランまたはベーシックプランのLINE＠アカウントでは使用できません。
    public static function oauthAccessToken()
    {
        return self::getBaseUrl().'oauth/accessToken';
    }

    // 連携トークンを発行する
    public static function userLinkToken($userId) {
        return self::getBaseUrl()."bot/user/$userId/linkToken";

    }


    // // マルチキャストメッセージを送る
    // // フリープランまたはベーシックプランのLINE＠アカウントでは使用できません。
    // public static function messageMulticast()
    // {
    //     return self::$baseUrl.'/message/multicast';
    // }


    // プロフィール
    public static function profile($userId) {
        return self::getBaseUrl()."bot/profile/$userId";

    }



    // グループのユーザーidをすべて取得する
    public static function groupMemberIds($groupId) {
        return self::getBaseUrl()."bot/group/$groupId/members/ids";

    }

    // ルームのユーザーidをすべて取得する
    public static function roomMemberIds($roomId) {
        return self::getBaseUrl()."bot/room/$roomId/members/ids";

    }

    // リッチメニューの登録
    public static function createRichMenu()
    {
        return self::getBaseUrl()."bot/richmenu";
    }

    // リッチメニューの画像アップロード
    public static function uploadRichMenuImage($richMenuId)
    {
        return self::getBaseUrl()."bot/richmenu/$richMenuId/content";
    }

    // リッチメニューを有効にする
    public static function setDefaultRichMenu($richMenuId)
    {
        return self::getBaseUrl()."bot/user/all/richmenu/$richMenuId";
    }

}


