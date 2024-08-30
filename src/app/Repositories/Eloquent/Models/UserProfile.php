<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Bases\BaseModel
{
    use SoftDeletes;

    protected $table = 'user_profiles';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        "user_id",
        "image_id",
        "chat",
        "nickname",
        "condition",
        "birthday",

        "child_birthday1",
        "child_birthday2",
        "child_birthday3",
        "child_birthday4",
        "child_birthday5",
        "child_gender1",
        "child_gender2",
        "child_gender3",
        "child_gender4",
        "child_gender5",
        "child_name1",
        "child_name2",
        "child_name3",
        "child_name4",
        "child_name5",
        "taste1",
        "taste2",
        "taste3",
        "mother_interest1",
        "mother_interest2",
        "mother_interest3",
        "mother_interest4",
        "child_interest1",
        "child_interest2",
        "child_interest3",
        "child_interest4",
        "experience1",
        "experience2",
        "experience3",
        "experience4",

        "introduction",
        "referral_code",

        "mother_word1",
        "mother_word2",
        "mother_word3",
        "mother_word4",

        "child_word1",
        "child_word2",
        "child_word3",
        "child_word4",

        "experience_word1",
        "experience_word2",
        "experience_word3",
        "experience_word4",

        "show_product",

        "url1",
        "url_title1",
        "url2",
        "url_title2",
        "url3",
        "url_title3",
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

    // 商品メッセージ詳細のリレーション
    public function product()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\Product');
    }

}
