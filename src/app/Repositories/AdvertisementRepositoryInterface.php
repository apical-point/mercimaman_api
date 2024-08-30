<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface AdvertisementRepositoryInterface extends BaseBasicInterface
{
    // 画像データの新規作成
    public function updateOrCreateImageData($Id, $where, $imageData);
}
