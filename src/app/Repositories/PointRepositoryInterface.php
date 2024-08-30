<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface PointRepositoryInterface extends BaseBasicInterface
{
    // idsで複数の削除
    public function getUsersSum($id);

}
