<?php namespace App\Repositories\Eloquent;

// use Illuminate\Database\DatabaseManager;

// カスタムエラー
// use App\Exceptions\CustomException;


abstract class BaseEloquent
{
    public $perPage;

    function __construct() {
        $this->perPage = config('const.site.PER_PAGE');
    }

    // ページの取得
    public function getPerPage($search=[])
    {
        if(empty($search['per_page'])) {
            $perPage =  $this->perPage;

        // 整数判定(int string)
        } elseif(preg_match('/^[0-9]+$/', $search['per_page'])) {
            $perPage = $search['per_page'];

        } elseif($search['per_page']=='-1') {
            $perPage = -1;

        } else {
            $perPage =  $this->perPage;
        }

        return $perPage;
    }

    // カラムの取得
    public function getColumns($search='')
    {
        // なければ終了
        if(empty($search['column_csv'])) return[];

        // 文字列出な開ければ終了
        if(!is_string($search['column_csv'])) return[];

        // 「,」で区切って配列にして返す
        return explode(',', $search['column_csv']);
    }


}
