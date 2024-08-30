<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Exception;

//サービス
use App\Services\UserService;
use App\Services\AdminerService;

use App\Controllers\Api\Admin\AdminPlanController;


//モデル
use App\Repositories\Eloquent\Models\UserSubscription;
use App\Repositories\Eloquent\Models\Order;

// メール
//use App\Mails\Api\BatchMail;

class testBatch extends Bases\BaseCommand
{

    protected $userService;
    protected $adminerService;

    /**
     * The name and signature of the console command.
     * artisanコマンドで呼び出す時のコマンド名を定義する
     * @var string
     */
    protected $signature = "batch:test";//YYYY-MM-DD

    /**
     * The console command description.
     * artisanコマンド一覧の出力時に表示される説明文、必須ではないが設定推奨
     * @var string
     */
    protected $description = "定期購入の注文データを作成し、配送案内メール送信";

    private $fixDate; //受注確定日
    private $delivery_date_start; //配送開始日
    private $delivery_date_end; //配送終了日
    private $week_num; //配送日が第○週

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        UserService $userService,
        AdminerService $adminerService

        // メール
    //    BatchMail $mail
      )

  {
        parent::__construct();
        $this->userService = $userService;
        $this->adminerService = $adminerService;

        // メール
    //    $this->mail = $mail;
    }

    /**
     * Execute the console command.
     * 実際の処理をこのメソッド内に記述する
     * @return mixed
     */
    public function handle(){

        //$where['order_id'] = "444";
        //if(!$this->orderDetailService->deleteItem($where)) throw new Exception(__('messages.faild_delete'));


//        $userRow = $this->userService->getItems(['email' => 'hkanno1111@apice-tec.co.jp', 'status' => 1]);
        $userRow = $this->userService->getItems(['email' => 'hkanno@apice-tec.co.jp', 'status' => 1], 0 , "created_at DESC");

print_r($userRow[0]->id);


    }

}
