<?php namespace App\Repositories\Eloquent\Models;


use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adminer extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'adminers';

    // ホワイトリストです。$fillableに指定したカラムのみ、create()やfill()、update()で値が代入されます
    protected $fillable = [
        'email',
        'password',
        'name',
        'admin_type',
        'status',
        'remember_token',
    ];

	// 取得させないもの指定。値を返す際にここで指定したものは返さない。
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ---------------------------------------- 任意の関数をjson結果に含める ----------------------------------------
    protected $appends = [
    ];


    // ---------------------------------------- リレーション ----------------------------------------
}
