<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class BoardExperience extends Bases\BaseModel
{

    protected $table = 'board_experiences';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'title',
        'detail',
        'user_id',
        'exp_flg',
        'check1',
        'check2',
        'check3',
        'check4',
        'parent_id',

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

    public function images()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able');
    }
    public function mainImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 1);
    }

}
