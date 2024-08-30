<?php namespace App\Repositories\Eloquent\Models;

use Rorecek\Ulid\HasUlid;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpFile extends Bases\BaseModel
{
    // primary keyにulidの使用
    use HasUlid;

    protected $table = 'up_files';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    // protected $fillable = [];

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

	// 取得させるもの指定。値を返す際にここで指定したもののみ返す。
	protected $visible = [];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [
    ];

    // dbのカラムの定義を書く。例えば、charの上限など。なぜならカラムの定義が、tinyintになっていても文字列を指定してcreate()を行っても、エラーも出ずにそのまま実行されてしまう。しかし、 そのカラムはデフォルトのままである。
    public $rules = [

        // ポリモーフィック
        'up_file_able_id' => 'nullable|max:256', // ポリモーフィックid (ulidにも対応させるためにstring型になっている)
        'up_file_able_type' => 'nullable|max:256', // ポリモーフィックタイプ

        // ファイル
        'name' => 'nullable|max:1000', // ファイル名
        'title' => 'nullable|max:1000', // タイトル---任意の名前をつけれる
        'mime_type' => 'nullable|max:64', // マイムタイプ
        'size' => 'nullable|numeric', // ファイルサイズ
        'url_path' => 'nullable|max:128', // ルートからのurlパス
        'dir_path' => 'nullable|max:128', // ルートからのdirパス
        'v_order' => 'nullable|integer', // 順番

        // システムで使用
        'status' => 'nullable|numeric|between:0,7', // ステータス
        'remarks' => 'nullable|max:65535', // 備考
    ];

    // リレーションの配列
    public $relationArray = [''];

    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    // このモデルの任意の関数をjsonに変換した後に表示させることができる。メソッド名には決まりがあるので注意が必要
    // 「eagerロード」ではないと思われるので多用すると重くなるかと思われる。特にリストのときは。
    // なので、なるべくは「eagerロード」で済むような設計が好ましいと思われる。
    // https://laravel.com/docs/5.7/eloquent-serialization#appending-values-to-json
    protected $appends = [
        'url', // url
    ];

    //
    public function getUrlAttribute()
    {
        $base = config('const.site.BASE_URL');
        return  !empty($this->url_path) ? $base.$this->url_path : false;
    }


    // ---------------------------------------- scope ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能
    // public function scopePersonMember($query)
    // {
    //     return $query->where('member_type', 4);
    // }

    // ---------------------------------------- リレーション 逆参照 ----------------------------------------

    // // 参加者
    // public function eventParticipants()
    // {
    //     return $this->hasMany('App\Repositories\Eloquent\Models\EventParticipant');
    // }


    /**
     * 所有しているcommentableモデルの全取得
    */
    public function upFileAble()
    {
        return $this->morphTo();
    }
}
