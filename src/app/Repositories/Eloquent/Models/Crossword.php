<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Crossword extends Bases\BaseModel
{

    protected $table = 'crosswords';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'post_date',
        'answer',
        'hint1',
        'hint2',
        'hint3',
        'hint4',
        'hint5',
        'hint6',
        'hint7',
        'hint8',
        'hint9',
        'hint10',
        'xq1',
        'xq2',
        'xq3',
        'xq4',
        'yq1',
        'yq2',
        'yq3',
        'yq4',

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
