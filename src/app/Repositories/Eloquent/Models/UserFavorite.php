<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Bases\BaseModel
{
    protected $table = 'user_favorites';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        "user_id",
        "type",
        "product_id",
        "follow_id",

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

    // ユーザー詳細のリレーション
    public function user()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\User');
    }
    public function userFollow()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\User', 'follow_id', 'id');
    }
    public function userFollower()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\User', 'user_id', 'id');
    }

    //商品リレーション
    public function product()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Product','product_id', 'id')->where("products.status","!=",1)->where("open_flg", 1);
    }


}
