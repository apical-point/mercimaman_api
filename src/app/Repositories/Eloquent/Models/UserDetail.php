<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Bases\BaseModel
{
    use SoftDeletes;

    protected $table = 'user_details';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'user_id',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'prefecture_id',
        'zip',
        'address1',
        'address2',
        'building',
        'tel',
        'work',
        'address_flg',
        'withdrawal',

        'stripe_id',
        'bank_code',
        'bank_type',
        'bank_branch_code',
        'bank_number',
        'bank_name',
        'identification',
        'device_id',
        'mail_flg',
        'kanri_user_flg',
        //'receive_flg',
        //'receiver_last_name',
        //'receiver_first_name',
        //'receiver_last_name_kana',
        //'receiver_first_name_kana',
        //'receiver_zip',
        //'receiver_prefecture_id',
        //'receiver_address',
        //'receiver_building',
        //'receiver_tel',

    ];

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

	// 取得させるもの指定。値を返す際にここで指定したもののみ返す。
	protected $visible = [];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [];

    // dbのカラムの定義を書く。例えば、charの上限など。
    // なぜならカラムの定義が、tinyintになっていても文字列を指定してcreate()を行っても、エラーも出ずにそのまま実行されてしまう。しかし、 そのカラムはデフォルトのままである。
    public $rules = [
        'user_id' => '',
        'last_name' => '',
        'first_name' => '',
        'last_name_kana' => '',
        'first_name_kana' => '',
        'zip' => '',
        'prefecture_id' => '',
        'address' => '',
        'building' => '',
        'tel' => '',
        'stripe_id' => '',
        'campaign_code' => '',
        /*
        'user_id' => 'nullable|numeric',
        'name' => 'nullable|max:256',
        'name_kana' => 'nullable|max:256',
        'zip' => 'nullable|max:8',
        'prefecture' => 'nullable|numeric',
        'address' => 'nullable|max:256',
        'building' => 'nullable|max:256',
        'tel' => 'nullable|max:20',
        */
    ];

    // リレーションの配列
    public $relationArray = [];

    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    // このモデルの任意の関数をjsonに変換した後に表示させることができる。メソッド名には決まりがあるので注意が必要
    // 「eagerロード」ではないと思われるので多用すると重くなるかと思われる。特にリストのときは。
    // なので、なるべくは「eagerロード」で済むような設計が好ましいと思われる。
    // https://laravel.com/docs/5.7/eloquent-serialization#appending-values-to-json
    protected $appends = [];


    // ---------------------------------------- scope ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能


    // ---------------------------------------- リレーション ----------------------------------------
    // FK側
    // ユーザー
    public function user()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\User');
    }

    // 都道府県
    public function prefecture()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Prefecture');
    }

    // ---------------------------------------- scope任意の検索関数を定義できる ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能
}
