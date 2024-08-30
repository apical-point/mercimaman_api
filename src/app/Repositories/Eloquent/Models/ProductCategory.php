<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Bases\BaseModel
{
    protected $table = 'product_categories';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'product_category_name',
        'parentid',
        'v_order',
        'cflag',
    ];

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

	// 取得させるもの指定。値を返す際にここで指定したもののみ返す。
	protected $visible = [];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [];

    // dbのカラムの定義を書く。例えば、charの上限など。なぜならカラムの定義が、tinyintになっていても文字列を指定してcreate()を行っても、エラーも出ずにそのまま実行されてしまう。しかし、 そのカラムはデフォルトのままである。
    public $rules = [
        // 'id' => '',
        'product_category_name' => '',
        'parentid' => '',
        'v_order' => '',
        'cflag' => '',
    ];

    // リレーションの配列
    public $relationArray = [];


    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    // このモデルの任意の関数をjsonに変換した後に表示させることができる。メソッド名には決まりがあるので注意が必要
    // 「eagerロード」ではないと思われるので多用すると重くなるかと思われる。特にリストのときは。
    // なので、なるべくは「eagerロード」で済むような設計が好ましいと思われる。
    // https://laravel.com/docs/5.7/eloquent-serialization#appending-values-to-json
    protected $appends = [
        'parent', // 親を取得する
    ];

    // 親を取得する
    public function getParentAttribute()
    {

        // 0ならばnull
        if($this->parentid==0) return NULL;

        // 親があれば親を返す
        return $this->where('id', $this->parentid)->first();
    }

    // ---------------------------------------- リレーション ----------------------------------------
    // FK側

    // PK側
    // 商品
    public function product()
    {
        return $this->hasMany('App\Repositories\Eloquent\Models\Product', 'product_category_id');
    }

    // 他

    // ---------------------------------------- scope任意の検索関数を定義できる ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能
}
