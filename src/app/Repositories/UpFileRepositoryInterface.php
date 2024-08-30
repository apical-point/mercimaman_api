<?php namespace App\Repositories;

interface UpFileRepositoryInterface
{
    // 1件数の取得
    public function getItem(array $where);


    public function getItems(array $where);


    // idsで複数の削除
    public function deleteItemsByIds(array $ids);

    // 新規作成
    public function createItem($data);
}
