<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewProduct extends Bases\BaseModel
{

    protected $table = 'review_product';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'product_name',
        'brand',
        'category_id',
        'star',
        'total_star',
        'review_count',
        'price',
        'price_range',
        'url',
        'rakuten_url',
        'myshop_url',
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

}
