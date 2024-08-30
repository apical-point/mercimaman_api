<?php namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Bases\BaseModel
{

    protected $table = 'products';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'category',
        'product_category1_id',
        'product_category2_id',
        'product_name',
        'user_id',
        'youtube',
        'detail',
        'status',
        'brand',
        'size',
        'condition',
        'taste',
        'shipping_charges',
        'shipping_method',
        'shipping_day',
        'shipping_area',
        'handing',
        'price',
        'system_charge',
        'inappropriate',
        'inappropriate_user',
        'inappropriate_reason',
        'auto_flg',
        'open_flg',
    ];

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

	// 取得させるもの指定。値を返す際にここで指定したもののみ返す。
	protected $visible = [];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [];

    // dbのカラムの定義を書く。例えば、charの上限など。なぜならカラムの定義が、tinyintになっていても文字列を指定してcreate()を行っても、エラーも出ずにそのまま実行されてしまう。しかし、 そのカラムはデフォルトのままである。
    public $rules = [];

    // リレーションの配列
    public $relationArray = [];


    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    // このモデルの任意の関数をjsonに変換した後に表示させることができる。メソッド名には決まりがあるので注意が必要
    // 「eagerロード」ではないと思われるので多用すると重くなるかと思われる。特にリストのときは。
    // なので、なるべくは「eagerロード」で済むような設計が好ましいと思われる。
    // https://laravel.com/docs/5.7/eloquent-serialization#appending-values-to-json
    protected $appends = [
        'is_sub_images', // サブ画像があるか
        'is_main_image', // メイン画像があるか
    //    'is_public', // 公開しているかどうか
    ];

    // 画像があるか
    public function getIsSubImagesAttribute()
    {
        $images = $this->subImages->toArray();
        return $images ? true : false;
    }

    // メイン画像があるか
    public function getIsMainImageAttribute()
    {
        $image = $this->mainImage->toArray();
        return $image ? true : false;
    }

    // 公開しているかどうか
    public function getIsPublicAttribute()
    {
        return $this->vire_flg === 1;
    }

    // ---------------------------------------- リレーション ----------------------------------------
    // カテゴリ
    public function productCategory()
    {
        return $this->belongsTo('App\Repositories\Eloquent\Models\ProductCategory', 'product_category_id');
    }

    // 注文詳細
    public function orderDetail()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\OrderDetail', 'order_id');
    }

    // 商品画像--多様性
    public function images()
    {
        return $this->morphMany('App\Repositories\Eloquent\Models\UpFile', 'up_file_able');
    }
    // 商品画像--多様性
    public function subImages()
    {
        return $this->images()->where('status', 0);
    }
    // 商品画像--多様性
    public function mainImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 1);
    }

    // 注文詳細
    public function userProfile()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\UserProfile', 'user_id');
    }

    // ---------------------------------------- scope任意の検索関数を定義できる ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能

    public function scopePublicOptionProduct($query)
    {
        return $query->where('vire_flg', 1)->where('product_type', 2);
    }

    /** オーダーステータス */
    public static $PRODUCT_DRAFT = 1; //下書き
    public static $PRODUCT_LISTING = 2; //出品中
    public static $PRODUCT_TRADING = 3; //取引中
    public static $PRODUCT_SHIPPED = 4; //発送済
    public static $PRODUCT_RECEIVED = 5; //受け取り完了
    public static $PRODUCT_TREAD_END = 6; //取引完了

}
