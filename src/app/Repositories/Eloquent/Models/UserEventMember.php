<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class UserEventMember extends Bases\BaseModel
{

    protected $table = 'user_event_members';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'user_id',
        'user_event_id',
        'attend_date',
        'pay_flg',
        'charge_id',
        'status',
        'point',
        'price',
        'system_price',
        'pay_price',
        'pay_status',
        'payment_request_date',
        'banktransfer_date',
        "payment_id"

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

    public function userEvent()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\UserEvent','user_event_id', 'id');
    }

}