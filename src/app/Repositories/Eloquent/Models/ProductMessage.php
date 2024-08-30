<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMessage extends Bases\BaseModel
{
    protected $table = 'product_messages';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'product_id',
        'message',
        'user_id',
        'open_flg',
    ];

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

	// 取得させるもの指定。値を返す際にここで指定したもののみ返す。
	protected $visible = [];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [];

    // dbのカラムの定義を書く。例えば、charの上限など。なぜならカラムの定義が、tinyintになっていても文字列を指定してcreate()を行っても、エラーも出ずにそのまま実行されてしまう。しかし、 そのカラムはデフォルトのままである。
    public $rules = [];

    // リレーションの配列
    public $relationArray = [];

    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    protected $appends = [];

    // ---------------------------------------- リレーション ----------------------------------------
    // 商品
    public function product()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Product');
    }


	// ---------------------------------------- /リレーション ----------------------------------------

}
