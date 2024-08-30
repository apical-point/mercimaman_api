<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class UserEvent extends Bases\BaseModel
{

    protected $table = 'user_events';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'user_id',
        'event_name',
        'event_date',
        'event_date_end',
        'event_time',
        'pref',
        'place',
        'access',
        'event_detail',
        'event_price',
        'member_cnt',
        'status',
        'host_name',
        'contact',
        'hp_url',
        'insta_url',
        'tel',
        'topic1',
        'topic2',
        'topic3',
        'admit_status'

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

    //複数取得に変更
    public function mainImage()
    {
        return $this->morphMany('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->orderby("v_order");
    }
/*
    public function mainImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 1);
    }
*/
}
