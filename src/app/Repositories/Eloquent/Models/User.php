<?php namespace App\Repositories\Eloquent\Models;

namespace App\Repositories\Eloquent\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // 認証
        'email',
        'password',
        'status',
        'regist_limit',
        'param',
        'remember_token',
        'temporary_email',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    protected $appends = [
        'is_temporary_registration', // 仮登録かどうか
        'is_main_regist', // 本登録かどうか
    ];

    // 仮登録かどうか
    public function getIsTemporaryRegistrationAttribute()
    {
        return $this->status === 0;
    }

    // 本登録かどうか
    public function getIsMainRegistAttribute()
    {
        return $this->status === 1;
    }

    // ---------------------------------------- リレーション ----------------------------------------
    // FK側

    // PK側
    // ユーザー詳細
    public function userDetail()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\UserDetail');
    }

    // ユーザープロフィール
    Public function userProfile()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\UserProfile');
    }

    // ブロック
    Public function block()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\Block');
    }

    // ユーザーお気に入り
    Public function userFavorite()
    {
        return $this->hasOne('App\Repositories\Eloquent\Models\UserFavorite');
    }

    // 本人確認画像--多様性
    public function images()
    {
        return $this->morphMany('App\Repositories\Eloquent\Models\UpFile', 'up_file_able');
    }

    // 本人確認画像--多様性
    public function mainImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 1);
    }

    // 本人確認画像--多様性
    public function subImage()
    {
        return $this->morphOne('App\Repositories\Eloquent\Models\UpFile', 'up_file_able')->where('status', 0);
    }

    // メッセージ
    Public function messageFrom()
    {
        return $this->hasMany('App\Repositories\Eloquent\Models\Message', 'user_from_id');
    }
    Public function messageTo()
    {
        return $this->hasMany('App\Repositories\Eloquent\Models\Message', 'user_to_id');
    }


    // ---------------------------------------- scope任意の検索関数を定義できる ----------------------------------------
    // 「scopePersonMember()」のように(scopeXxxYyy())定義しておけば、使用するときは「Member::personMember()->get()」のように(xxxYyy())して仕様が可能.
    // また、引数も可能
    public function scopeMainRegistration($query) {
        return $query->where('status', 1);
    }
}
