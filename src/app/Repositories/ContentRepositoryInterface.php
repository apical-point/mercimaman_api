<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface ContentRepositoryInterface extends BaseBasicInterface
{

    // 画像データの新規作成
    public function updateOrCreateImageData($Id, $where, $imageData);


    //--------- クロスワード--------------
    public function createItemCrossword(array $where);

    public function getItemCrossword(array $where);

    public function updateItemCrossword(array $where, array $data);

    public function getListCrossword(array $where);

    public function deleteItemCrossword(array $data);


    //--------- タロット--------------
    public function createItemTarot(array $where);

    public function getItemTarot(array $where);

    public function updateItemTarot(array $where, array $data);

    public function getListTarot(array $where);

    public function deleteItemTarot(array $data);

    public function createItemTarotUser(array $data);

    public function getListTarotUser(array $where);


}
