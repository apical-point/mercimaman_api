<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Bases\BaseModel
{
    protected $table = 'points';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        "user_id",
        "point_type",
        "point_detail",
        "point",
        "use_point",
        "use_flg",
        "point_date",
        "use_date",
        "expiration_date",
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
    protected $appends = [];

	// ---------------------------------------- リレーション 逆参照 ----------------------------------------

    public static $point_buy = 1;//購入
    public static $point_return = 2;//返信
    public static $point_iine = 3;//いいね交換

}
