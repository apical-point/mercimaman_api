<?php namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mails\BasicSendMail;
use Illuminate\Support\Facades\Log;

// サービス
use App\Services\UserService;
use App\Services\NewsService;
use App\Services\MailTemplateService;
use App\Services\MailSigService;

use App\Services\NotifyService;


class MailService
{
    protected $mailTemplateService;
    protected $mailSigService;
    protected $NewsService;
    protected $notifyService;

    public function __construct(

        MailTemplateService $mailTemplateService,
        MailSigService $mailSigService,
        NewsService $newsService,
        NotifyService $notifyService

    ) {

        $this->mailTemplateService = $mailTemplateService;
        $this->mailSigService = $mailSigService;
        $this->newsService = $newsService;
        $this->notifyService = $notifyService;

    }


    //通常メール
    public function sendMail($templateName, $to, $title, $data,$file=null)
    {

        //署名の取得
        $mailsig = $this->mailSigService->getitem(['id'=>1]);
        $data['sig'] = !empty($mailsig['sig']) ? $mailsig['sig']: "";

        try {

            Mail::to($to)->send(new BasicSendMail($templateName, $title, $data,$file));

            return true;

        } catch (Exception $e) {

            return false;
        }


    }

    /**
     * mb_send_mailを使ってメールを送信する
     *
     * Push通知不要の
     *
     * @param String $to 宛先メールアドレス
     * @param String_type $mail_no メールテンプレート
     */
    function sendMail_transaction($user_id,  $to,  $mail_no, $data=null){

        //メールテンプレート取得
        $mailTamplate = $this->mailTemplateService->getItem(['id'=>$mail_no,'status'=>1]);
        //署名の取得
        //署名の取得
        $mailsig = $this->mailSigService->getitem(['id'=>1]);



        $title = '['.config('const.site.SITE_NAME').']' . $mailTamplate['subject'];
        $maildata['email'] = $to;
        $maildata['body'] = "{name}様\n\n\n" . $mailTamplate['mail_text'];

        //文字の置き換え

        //文字の置き換え
        if (strstr($maildata['body'], "{name}" ) !== false) $maildata['body'] = str_replace("{name}", $data['name'] , $maildata['body']);
        if (strstr($maildata['body'], "{product_name}" ) !== false) $maildata['body'] = str_replace("{product_name}", $data['product_name'] , $maildata['body']);
        if (strstr($maildata['body'], "{price}" ) !== false) $maildata['body'] = str_replace("{price}", number_format($data['price']) , $maildata['body']);
        if (strstr($maildata['body'], "{nickname}" ) !== false) $maildata['body'] = str_replace("{nickname}", $data['nickname'] , $maildata['body']);
        if (strstr($maildata['body'], "{buyer_name}" ) !== false) $maildata['body'] = str_replace("{buyer_name}", $data['buyer_name'] , $maildata['body']);
        if (strstr($maildata['body'], "{seller_name}" ) !== false) $maildata['body'] = str_replace("{seller_name}", $data['seller_name'] , $maildata['body']);
        if (strstr($maildata['body'], "{buyer_zip}" ) !== false) $maildata['body'] = str_replace("{buyer_zip}", $data['buyer_zip'] , $maildata['body']);
        if (strstr($maildata['body'], "{buyer_address1}" ) !== false) $maildata['body'] = str_replace("{buyer_address1}", $data['buyer_address1'] , $maildata['body']);
        if (strstr($maildata['body'], "{buyer_address2}" ) !== false) $maildata['body'] = str_replace("{buyer_address2}", $data['buyer_address2'] , $maildata['body']);
        if (strstr($maildata['body'], "{buyer_building}" ) !== false) $maildata['body'] = str_replace("{buyer_building}", $data['buyer_building'] , $maildata['body']);
        if (strstr($maildata['body'], "{date}" ) !== false) $maildata['body'] = str_replace("{date}", $data['date'] , $maildata['body']);
        if (strstr($maildata['body'], "{cnt}" ) !== false) $maildata['body'] = str_replace("{cnt}", $data['cnt'] , $maildata['body']);
        if (strstr($maildata['body'], "{order_date}") !== false) $maildata['body'] = str_replace("{order_date}", $data['order_date'] , $maildata['body']);
        if (strstr($maildata['body'], "{order_id}") !== false) $maildata['body'] = str_replace("{order_id}", $data['order_id'] , $maildata['body']);
        if (strstr($maildata['body'], "{bank_price}") !== false) $maildata['body'] = str_replace("{bank_price}", number_format($data['bank_price']) , $maildata['body']);
        if (strstr($maildata['body'], "{present}") !== false) $maildata['body'] = str_replace("{present}", $data['present'] , $maildata['body']);
        if (strstr($maildata['body'], "{tweet_url}") !== false) $maildata['body'] = str_replace("{tweet_url}", $data['tweet_url'] , $maildata['body']);

        if (strstr($maildata['body'], "{event_name}") !== false) $maildata['body'] = str_replace("{event_name}", $data['event_name'] , $maildata['body']);

        if (strstr($maildata['body'], "{url}") !== false) $maildata['body'] = str_replace("{url}", $data['url'] , $maildata['body']);

        //お知らせの登録
        $newsdata['title'] = $mailTamplate['subject'];
        $newsdata['detail'] = $maildata['body'];
        $newsdata['status'] = "1";
        $newsdata['news_flg'] = "1";
        $newsdata['open_date'] = date("Y-m-d");
        $newsdata['open_flg'] = "1";
        $newsdata['user_id'] = $user_id;
        $newsdata['check'] = "1";
        $newRows = $this->newsService->createItem($newsdata);

        //送信するかしないか
        if (!$mailTamplate['mail_flg']) return true;


        //署名の設定
        $maildata['sig'] = !empty($mailsig['sig']) ? $mailsig['sig']: "";


        try {

            Mail::to($to)->send(new BasicSendMail('emails.common.non', $title, $maildata, ""));


            /*--Push　
             *
             *
             * メルママメイトからのメッセージ　No15
             * 商品へのコメント　　　　　　　　No5
             * 商品が購入された                No3
             * ツイート、コメントへの返信      No23
             *
             * な具体的にPush通知
             *
             * 他は運営からのお知らせとして送る
             *
             * ※この辺修正したいが、工数兼ね合いで現在は出来ない。
             *
             *
             */

            if($mail_no == 15){
                 //MessageControllerで送信
             }
            elseif($mail_no == 5){
                //ProductControllerで送信
            }
            elseif($mail_no == 3){
                //OrderControllerで送信
            }
            elseif($mail_no == 33 || $mail_no == 42 || $mail_no == 43 ){
                //ツイートはTweet側で処理。　イベント申込、お悩み返信はPush不要

            }
            else{
                $arr[] = $user_id;
                $this->notifyService->sendNotify(NotifyService::KIND_ADMIN_NOTIFICATION, $arr, $newRows);
            }

            return true;

        } catch (Exception $e) {

            return false;
        }

    }

}
