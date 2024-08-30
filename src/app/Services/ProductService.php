<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductCategoryRepositoryInterface;



class ProductService extends Bases\BaseService
{
    protected $productRepo;
    protected $productCategoryRepo;

    public function __construct(
        ProductRepositoryInterface $productRepo,
        ProductCategoryRepositoryInterface $productCategoryRepo

    ) {
        $this->productRepo = $productRepo;
        $this->productCategoryRepo = $productCategoryRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        // 商品の登録
        return $this->productRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->productRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->productRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->productRepo->getItem($where);
    }

    // 1件更新
    public function updateItem($where, $data)
    {
        return $this->productRepo->updateItem($where, $data);
    }

    // 1件削除
    public function deleteItem($where)
    {
        return $this->productRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->productRepo->deleteItems($where);
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

    // --------------------------- その他関数 ---------------------------
    // 画像の登録
    public function createImagesByProductIdAndImagesData($productId, $imagesData)
    {
        $results = [];
        foreach ($imagesData as $imageData) {
            if(!$image=$this->productRepo->createImageByProductIdAndImageData($productId, $imageData)) return false;
            $results[] = $image;
        }

        return $results;
    }

    // メイン画像の登録
    public function createMainImageByProductIdAndImageData($productId, $imageData)
    {
        $imageData['status'] = 1;
        if(!$image=$this->productRepo->UpdateOrCreateImageByProductIdAndImageData($productId, ['status'=>1], $imageData)) return false;

        return $image;
    }


    // // メイン画像を登録or更新
    // public function updateMainProfileImagebyPathAndProductId($path, $productId)
    // {

    //     $data['dir_path'] = $path;
    //     $data['type'] = 1;
    //     $data['size'] = \File::size($path);
    //     $data['mime_type'] = mime_content_type($path);

    //     return $this->memberFileRepo->upadteOrCreateItem(['member_id'=>$memberId, 'type'=>1], $data);
    // }



    // 公開非公開フラグの一括修正
    public function updateVireFlgs($ids, $vireFlgs)
    {
        // 配列の要素数が血がければエラー
        if(count($ids)!=count($vireFlgs))  return false;

        // 配列ごとに回す
        for ($i=0; $i<count($ids); $i++) {

            if(!$this->updateItem(['id'=>$ids[$i]], ['vire_flg'=>$vireFlgs[$i]])) return false;

        }

        return true;
    }


    // 作成
    public function createItemByUserId($userId, $data)
    {
        // ユーザーidを入れる
        $data['user_id'] = $userId;

        // 商品の登録
        return $this->productRepo->createItem($data);
    }

    public function getItemsByIds($ids)
    {
        return $this->productRepo->getItemsByIds($ids);
    }

    // 商品データからユーザーidのみ取り出す
    public function getUserIdsByProductRows($productRows)
    {
        // ユーザーidのみ取得
        $userIds = $productRows->pluck('user_id');

        // 重複を省く
        return array_unique($userIds->toArray());
    }

}
