<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Bases\BaseModel
{

    protected $table = 'contents';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'themedate',
        'theme',
        'choicesdate',
        'choicestheme',
        'answer1',
        'answer2',
        'answerCnt1',
        'answerCnt2',
        'present',
        'presentdate',
        'presentdetail',
        'election',
        'company_name',
        'company_url',
        'election_flg',
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

    // プレゼント画像--多様性
    public function images()
    {
        return $this->morphMany('App\Repositories\Eloquent\Models\UpFile', 'up_file_able');
    }

    // プレゼント画像--多様性
    public function mainImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 1);
    }

    // ユーザー詳細
    public function contentOffer()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\contentOffer');
    }

    // ---------------------------------------- リレーション ----------------------------------------
}
