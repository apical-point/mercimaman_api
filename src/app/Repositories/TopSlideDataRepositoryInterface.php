<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface TopSlideDataRepositoryInterface extends BaseBasicInterface
{

    public function updateOrCreateImageData($Id, $where, $imageData);

}
