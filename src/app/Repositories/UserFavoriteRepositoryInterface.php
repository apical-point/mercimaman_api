<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface UserFavoriteRepositoryInterface extends BaseBasicInterface
{

    public function getfollowSum($userId);

    public function getfollowerSum($userId);

    public function getfavoriteSum($userId);

}
