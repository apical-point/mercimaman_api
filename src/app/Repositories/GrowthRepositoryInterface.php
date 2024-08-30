<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface GrowthRepositoryInterface extends BaseBasicInterface
{

    public function getListGrowthAge(array $where);

    public function createItemGrowthAge(array $where);

    public function getItemGrowthAge(array $where);

    public function updateItemGrowthAge(array $where, array $data);

    public function deleteItemGrowthAge(array $data);

    //-------------自分の子供の出来たこと記録---------
    public function getListGrowthUser(array $where);

    public function createItemGrowthUser(array $where);

    public function getItemGrowthUser(array $where);

    public function updateItemGrowthUser(array $where, array $data);

    public function deleteItemGrowthUser(array $data);

    public function updateOrCreateGrowthUser(array $where, array $data);


    public function createImageByGrowth($messageId, $imageData);
}
