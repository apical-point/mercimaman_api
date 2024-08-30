<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface TweetRepositoryInterface extends BaseBasicInterface
{

    public function updateIncrement($where, array $data);
    public function createCheckUser($data);

    public function getItemCheckUser(array $data);

    public function getCountData(array $where);

    public function getItemCheckUserSum(array $where);



}
