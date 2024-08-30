<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface NewsRepositoryInterface extends BaseBasicInterface
{
    public function datedelete($where);
}
