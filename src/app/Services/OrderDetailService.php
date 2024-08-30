<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\OrderDetailRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;


class OrderDetailService extends Bases\BaseService
{
    protected $orderDeatilRepo;
    protected $productRepo;

    public function __construct(
        OrderDetailRepositoryInterface $orderDeatilRepo,
        ProductRepositoryInterface $productRepo
    ) {
        $this->orderDeatilRepo = $orderDeatilRepo;
        $this->productRepo = $productRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItemByOrderId($orderId, array $data)
    {
        return $this->orderDeatilRepo->createItemByOrderId($orderId, $data);
    }

    // 作成
    public function createItem(array $data)
    {
        if(!$item = $this->orderDeatilRepo->createItem($data)) return false;

        // 返す
        return $item;
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->orderDeatilRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->orderDeatilRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->orderDeatilRepo->getItem($where);
    }

    // idで1件取得
    public function getItemById($id)
    {
        return $this->orderDeatilRepo->getItem(['id'=>$id]);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {
        return $this->orderDeatilRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->orderDeatilRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->orderDeatilRepo->deleteItems($where);
    }



    // --------------------------- チェック関数 ---------------------------


    // --------------------------- その他の関数 ---------------------------

    // 複数作成
    public function createItemsByOrderId($orderId, array $rows)
    {
        // 結果の作成
        $results = [];

        foreach($rows as $row) {

            if(!$result = $this->createItemByOrderId($orderId, $row)) return false;
            $results[] = $result;
        }

        return $results;
    }

    // 注文詳細データの取得
    public function getOrderDetailData($inputData, $productRows)
    {
        $results = [];

        // 配列毎に回す
        // for($i=0; $i<count($inputData['product_ids']); $i++) {
        foreach ($inputData['product_ids'] as $key => $productId) {
            // 注文詳細のデータ
            $result = [];

            //  商品の取得
            if(!$productRow = $productRows->where('id', $productId)->first()) return false;

            // 公開していなければエラー
            if(!$productRow->is_public) return false;

            // 遅延読み込みを行う
            $productRow->load(['productCategory']);

            // // 商品を入れる
            $result['product'] = $productRow;

            // 注文詳細個数など
            $result['num'] = $inputData['nums'][$key];
            $result['budget_price'] = $productRow->price; // 単価
            $result['total_price'] = $productRow->price * $inputData['nums'][$key];

            // リレーションデータの作成
            $result['product_id'] = $inputData['product_ids'][$key];
            $result['product_category_id'] = $productRow->product_category_id;
            $result['product_category_name'] = $productRow->product_category->name;

            // 商品情報
            $result['product_name'] = $productRow->product_name;
            $result['product_detail'] = $productRow->detail;

            /*
            //  色の取得
            if(!empty($inputData['color_ids'][$key])) {
                if(!$productColorRow = $this->productOptionRepo->getItem(['id'=>$inputData['color_ids'][$key], 'product_id'=>$inputData['product_ids'][$key]])) return false;
                $result['product_color_option_id'] = $inputData['color_ids'][$key];
                $result['product_color_option_name'] =$productColorRow->name;
            }

            //  サイズの取得
            if(!empty($inputData['size_ids'][$key])) {
                if(!$productSizeRow = $this->productOptionRepo->getItem(['id'=>$inputData['size_ids'][$key], 'product_id'=>$inputData['product_ids'][$key]])) return false;
                $result['product_size_option_id'] = $inputData['size_ids'][$key];
                $result['product_size_option_name'] = $productSizeRow->name;
            }
            */

            $results[$productRow->user_id][] = $result;
        }

        return $results;

    }

    //
    public function getEvaluation($id, $type)
    {
        return $this->orderDeatilRepo->getEvaluation($id, $type);
    }

}
