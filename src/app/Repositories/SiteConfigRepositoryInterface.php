<?php namespace App\Repositories;

// use App\Repositories\BaseRepositoryInte  rface\BaseBasicInterface;

interface SiteConfigRepositoryInterface
{
    // リストの取得
    public function getList(array $search);

    // 1件数の取得
    public function getItem(array $where);

    // 1件の更新
    public function updateItem(array $where, array $data);
}
