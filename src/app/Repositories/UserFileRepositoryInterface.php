<?php namespace App\Repositories;

interface UserFileRepositoryInterface {
    // public function search($conditions);
    // public function updateOrInsert($wkey, $params);

    public function upadteOrCreateItem($where, $data);

    // idsとメンバーidで削除
    public function deleteItemsByIdsAndUserId($ids, $memebrId);

}
