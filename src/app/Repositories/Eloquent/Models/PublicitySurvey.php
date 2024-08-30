<?php
namespace App\Repositories\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class PublicitySurvey extends Model
{

    protected $table = 'publicity_survey';

    // ブラックリストです。$guardedに指定したカラムのみ、create()やfill()、update()で値が代入されません。
    protected $guarded = [];

    // プレゼント画像--多様性
    public function mainImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 1);
    }

    // 一旦なしで
    // public function contentOffer()
    // {
    //     return $this->hasOne('App\Repositories\Eloquent\Models\contentOffer');
    // }
}
