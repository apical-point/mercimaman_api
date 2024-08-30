<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface EventRepositoryInterface extends BaseBasicInterface
{

    public function createItemTopic(array $where);

    public function getItemsTopic(array $where, $data, $order);

    public function deleteItemsTopic(array $where);

}
