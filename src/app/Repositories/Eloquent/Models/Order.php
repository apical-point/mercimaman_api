<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Bases\BaseModel
{

    protected $table = 'orders';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'seller_user_id',
        'buyer_user_id',
        'product_id',
        'total_price',
        'system_charge',
        'postage',
        'sales_price',
        'payment_price',
        'point',
        'status',
        'charge_id',
        'seller_zip',
        'seller_prefecture_id',
        'seller_address1',
        'seller_address2',
        'seller_building',
        'seller_name',
        'seller_email',
        'seller_tel',
        'buyer_zip',
        'buyer_prefecture_id',
        'buyer_address1',
        'buyer_address2',
        'buyer_building',
        'buyer_name',
        'buyer_email',
        'buyer_tel',
        'order_date',
        'shipping_date',
        'arrival_date',
        'tradeend_date',
        'sellkeep_date',
        'payment_request_date',
        'payment_csv_date',
        'banktransfer_date',
        'auto_request',
        'open_flg',
        'payment_id',
        'yamato_reserve_no',
        'yamato_password',
    ];

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

	// 取得させるもの指定。値を返す際にここで指定したもののみ返す。
	protected $visible = [];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [];

    // dbのカラムの定義を書く。例えば、charの上限など。なぜならカラムの定義が、tinyintになっていても文字列を指定してcreate()を行っても、エラーも出ずにそのまま実行されてしまう。しかし、 そのカラムはデフォルトのままである。
    public $rules = [
    ];

    // リレーションの配列
    public $relationArray = [];


    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    // このモデルの任意の関数をjsonに変換した後に表示させることができる。メソッド名には決まりがあるので注意が必要
    // 「eagerロード」ではないと思われるので多用すると重くなるかと思われる。特にリストのときは。
    // なので、なるべくは「eagerロード」で済むような設計が好ましいと思われる。
    // https://laravel.com/docs/5.7/eloquent-serialization#appending-values-to-json
    protected $appends = [];

    // ---------------------------------------- リレーション ----------------------------------------
    // 購入ユーザーID
    public function user()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\User', 'user_id');
    }
    // ユーザー詳細
    public function userDetail()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\UserDetail', 'user_id', 'user_id');
    }
    // 商品ID
    public function product()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Product');
    }
    // 都道府県
    public function prefecture()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Prefecture', 'seller_prefecture_id');
    }

    // ユーザー詳細
    public function orderDetails()
    {
        return $this->hasMany('App\Repositories\Eloquent\Models\OrderDetail');
    }


    // ---------------------------------------- scope任意の検索関数を定義できる ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能


    /** オーダーステータス */
    public static $ORDER_CREDIT_NG = 0; //決済NG
    public static $ORDER_TRADE_START = 1; //取引中
    public static $ORDER_SHIPPING = 2; //発送済
    public static $ORDER_TRADE_RECEIVE = 3; //受取完了
    public static $ORDER_TRADE_END = 4; //取引完了
    public static $ORDER_PAYMENT = 5; //支払申請中（登録申請）
    public static $ORDER_AUTO_PAYMENT = 6; //支払申請中（自動申請）
    public static $ORDER_PAYMENTING = 7; //支払処理中
    public static $ORDER_PAYMENT_END = 8; //支払完了

}
