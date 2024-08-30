<?php namespace App\Repositories\BaseRepositoryInterface;

interface BaseBasicInterface
{
    // 検索
    public function getSearchQuery($query, $search);

    // リストの取得
    public function getList(array $search);

    // 新規作成
    public function createItem(array $data);

    // 1件数の取得
    public function getItem(array $where);

    // 複数の取得
    public function getItems(array $where, $take=0, $orderByRaw='' );

    // 1件の更新
    public function updateItem(array $where, array $data);

    // 複数の削除
    public function deleteItems(array $where);

    // 1件の削除
    public function deleteItem(array $where);

}
