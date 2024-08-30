<?php namespace App\Repositories\Eloquent;

// use App\Repositories\BlockRepositoryInterface;
use App\Repositories\Eloquent\Models\Block;
use Illuminate\Support\Facades\Log;

class BlockRepository extends BaseEloquent
{
    protected $block;

    public function __construct(
        Block $block
    ){
        parent::__construct();
        $this->block = $block;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->block->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->block->where($where)->first();
    }

    // 複数件の取得
    public function getItems($where)
    {
        return $this->block->where($where)->get();
    }

    // ------------------------------------- basic -------------------------------------

    // 複数の削除
    public function deleteItems(array $where)
    {
        return $this->block->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

}