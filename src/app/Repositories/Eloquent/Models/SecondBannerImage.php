<?php

namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class SecondBannerImage extends Bases\BaseModel
{
    protected $table = 'second_banner_images';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    // protected $fillable = [
    //     'detail',
    //     'eventpage_flg',
    //     'url',
    //     "name"
    // ];
    protected $fillable = ['type', 'second_banner_id', 'image_url', 'name'];

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

    public function getBannerById($id)
    {
        return $this->find($id);
    }

    public function updateBanner($id, $data)
    {
        if(empty($item=$this->getBannerById($id)))  return false;
        $item->fill($data)->save();
        return $item;
    }

    public function updateOrCreateImageData($id, $where, $imageData)
    {
        // 商品取得
        if(!$item = $this->getBannerById($id)) return false;

        // 画像作成
        return $item->images()->updateOrCreate($where, $imageData);
    }

    public function getPcBanner($bannerId) {
        $pcBanner = $this->where([
            ['type', '=', '0'],
            ['second_banner_id', '=', $bannerId]
        ])->first();
        return $pcBanner;
    }

    public function getSpBanner($bannerId) {
        $pcBanner = $this->where([
            ['type', '=', '1'],
            ['second_banner_id', '=', $bannerId]
        ])->first();
        return $pcBanner;
    }

    // ---------------------------------------- リレーション ----------------------------------------

    //複数選択
    public function banner()
    {
        return $this->belongsTo(SecondBanner::class);
    }
}