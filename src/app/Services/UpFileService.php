<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\UpFileRepositoryInterface;


class UpFileService extends Bases\BaseService
{
    protected $upFileRepo;

    public function __construct(
        UpFileRepositoryInterface $upFileRepo
    ) {
        $this->upFileRepo = $upFileRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    // public function createItem(array $data)
    // {
    //     return $this->upFileRepo->createItem($data);
    // }

    // // 複数取得
    // public function getList($search=[])
    // {
    //     return $this->upFileRepo->getList($search);
    // }

    // // 複数取得
    // public function getItems($where=[], $take=0, $orderByRaw='')
    // {
    //     return $this->upFileRepo->getItems($where, $take, $orderByRaw);
    // }

    // 1件取得
    public function getItem($where)
    {
        return $this->upFileRepo->getItem($where);
    }

    // 1件更新
    public function updateItem($where, $data)
    {
    }

    // 1件削除
    public function deleteItem($where)
    {
        return $this->upFileRepo->deleteItem($where);
    }


    public function getItems($where)
    {
        return $this->upFileRepo->getItems($where);
    }



    // --------------------------- id系 ---------------------------
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // idで更新
    public function updateItemById($id, $data)
    {
        return $this->updateItem(['id'=>$id], $data);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->deleteItem(['id'=>$id]);
    }


    // --------------------------- チェック関数 ---------------------------


    // --------------------------- その他 ---------------------------
    // データベースに保存するデータを返す
    public function getFileData(string $imagePath, $saveDir='')
    {

        if(!empty($saveDir)) $saveDir = $saveDir;

        // データの定義
        $imageData = [
            'mime_type' => File::mimeType($imagePath),
            'name' => basename($imagePath),
            'size' => File::size($imagePath),
            'url_path' => 'storage/'.$saveDir.basename($imagePath),
            'dir_path' => $imagePath,
            'v_order' => 1,
        ];

        return $imageData;
    }

    // データベースに保存するデータを返す
    public function getImagesData(array $imagePaths, $saveDir='')
    {
        $imagesData = [];
        foreach($imagePaths as $imagePath) $imagesData[] = $this->getFileData($imagePath, $saveDir);

        return $imagesData;
    }

    // idsで複数の削除
    public function deleteItemsByIds($ids)
    {
        return $this->upFileRepo->deleteItemsByIds($ids);
    }


    public function createItem($data)
    {
        return $this->upFileRepo->createItem($data);
    }
    // --------------------------- / ---------------------------

}
