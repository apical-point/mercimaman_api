<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface PrefectureRepositoryInterface extends BaseBasicInterface
{

    // リストの取得
    public function getList(array $search);

    // idsで取得
    public function getItemsByIds($ids);

}
