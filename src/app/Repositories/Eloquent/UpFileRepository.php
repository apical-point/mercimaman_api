<?php namespace App\Repositories\Eloquent;

use App\Repositories\UpFileRepositoryInterface;
use App\Repositories\Eloquent\Models\UpFile;

class UpFileRepository extends BaseEloquent implements UpFileRepositoryInterface
{
    protected $upFile;

    public function __construct(
        UpFile $upFile
    ){
        parent::__construct();
        $this->upFile = $upFile;
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->upFile->where($where)->first();
    }

    //複数取得
    public function getItems($where)
    {
        return $this->upFile->where($where)->get();
    }


    // idsで複数の削除
    public function deleteItemsByIds(array $ids)
    {
        return $this->upFile->whereIn('id', $ids)->delete();
    }

    public function createItem($data)
    {
        return $this->upFile->create($data);
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;

        return $item->delete();
    }
}
