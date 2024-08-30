<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface ProductRepositoryInterface extends BaseBasicInterface
{
    // 画像データの新規作成
    public function createImageByProductIdAndImageData($productId, $imageData);

    //  商品idとカラーidとサイズidを指定して取得する
    // public function getItemByIdAndAndColorIdAndSizeId($productId, $colorId, $sizeId);

    // idsで取得
    public function getItemsByIds($ids);

    // 画像データの新規作成or更新
    public function updateOrCreateImageByProductIdAndImageData($productId, $where, $imageData);


}
