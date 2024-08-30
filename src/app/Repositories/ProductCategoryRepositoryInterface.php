<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface ProductCategoryRepositoryInterface extends BaseBasicInterface
{
    // 特定の親の子供の個数を返す
    public function getCountNumByParentid($parentid);

}
