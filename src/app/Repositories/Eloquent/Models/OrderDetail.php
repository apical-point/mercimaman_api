<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Bases\BaseModel
{

    protected $table = 'order_details';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'order_id',
        'seller_user_id',
        'seller_evaluation',
        'seller_comment',
        'buyer_user_id',
        'buyer_evaluation',
        'buyer_comment',
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
    // FK側
    // 注文ID
    public function order()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Order');
    }
    // 商品ID
    public function product()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Product');
    }

    //プラン
    public function plan()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Plan');
    }

    // PK側

    // 他

    // ---------------------------------------- scope任意の検索関数を定義できる ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能
}
