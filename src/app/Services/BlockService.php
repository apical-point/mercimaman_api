<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\Eloquent\BlockRepository;

class BlockService extends Bases\BaseService
{
    // リポジトリ
    protected $blockRepo;

    public function __construct(
        BlockRepository $blockRepo
    ) {
        // リポジトリ
        $this->blockRepo = $blockRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->blockRepo->createItem($data);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->blockRepo->getItem($where);
    }

    // 複数件取得
    public function getItems($where)
    {
        return $this->blockRepo->getItems($where);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->blockRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->blockRepo->deleteItems($where);
    }

}