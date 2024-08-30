<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface BoardRepositoryInterface extends BaseBasicInterface
{

    //体験記　投稿
    public function createItemExp(array $where);

    public function getItemExp(array $where);

    public function updateItemExp(array $where, array $data);

    public function getListExp(array $where);

    public function updateExpIncrement($where, array $data);

    public function createExperienceUser($data);

    public function getItemExperienceUser(array $data);

    public function deleteItemExp(array $data);

    public function getCountData(array $where);

    public function updateOrCreateImageData($Id, $where, $imageData);

}
