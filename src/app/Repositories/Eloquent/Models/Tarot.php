<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Tarot extends Bases\BaseModel
{

    protected $table = 'tarots';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'post_date',
        'card1',
        'card_fb1',
        'card_result1',
        'card2',
        'card_fb2',
        'card_result2',
        'card3',
        'card_fb3',
        'card_result3',
        'card4',
        'card_fb4',
        'card_result4',
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

    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    protected $appends = [
    ];

    // ---------------------------------------- リレーション ----------------------------------------
}
