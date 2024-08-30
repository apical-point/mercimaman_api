<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface UserRepositoryInterface extends BaseBasicInterface
{

    // 本登録済みのもの取得
    public function getMainRegistrationItem($where);

    // idsで取得
    public function getItemsByIds($ids);

    // 画像データの新規作成
    public function updateOrCreateImageData($Id, $where, $imageData);


}
