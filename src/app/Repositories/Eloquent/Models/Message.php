<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Bases\BaseModel
{

    protected $table = 'messages';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'user_from_id',
        'user_to_id',
        'message',
        'open_flg',
        'confirm_date',
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

    protected $appends = [];

    public function images()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able');
    }
}