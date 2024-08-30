<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\TopSlideDataRepositoryInterface;



//use App\Http\Controllers\Api\BoardController;

class TopSlideDataService extends Bases\BaseService
{
    // リポジトリ
    protected $topSlideDataRepo;



    public function __construct(
        TopSlideDataRepositoryInterface $topSlideDataRepo
    ) {
        // リポジトリ
        $this->topSlideDataRepo = $topSlideDataRepo;

    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->topSlideDataRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        $arr =  $this->topSlideDataRepo->getList($search);
        return $arr;
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->topSlideDataRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->topSlideDataRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

        // 更新
        if(!$this->topSlideDataRepo->updateItem($where, $data)) return false;

        return $this->getItem($where);

    }

    // 削除
    public function deleteItem($where)
    {
        return $this->topSlideDataRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {

        return $this->topSlideDataRepo->deleteItems($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->topSlideDataRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        $arr =  $this->getItem(['id'=>$id]);

        return $arr;

    }

    // 画像の登録
    public function updateOrCreateImageData($Id, $imageData, $wh=[])
    {

        if(!$image=$this->topSlideDataRepo->updateOrCreateImageData($Id, $wh, $imageData)) return false;
        return $image;
    }


}
