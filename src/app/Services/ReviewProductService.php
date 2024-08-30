<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\Eloquent\ReviewProductRepository;
use App\Repositories\Eloquent\UserProfileRepository;
use App\Repositories\Eloquent\UserDetailRepository;


class ReviewProductService extends Bases\BaseService
{
    // リポジトリ
    protected $ReviewProductRepo;
    protected $UserProfileRepo;
    protected $UserDetailRepo;

    public function __construct(
        ReviewProductRepository $ReviewProductRepo,
        UserProfileRepository $UserProfileRepo,
        UserDetailRepository $UserDetailRepo
    ) {
        // リポジトリ
        $this->ReviewProductRepo = $ReviewProductRepo;
        $this->UserProfileRepo = $UserProfileRepo;
        $this->UserDetailRepo = $UserDetailRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {

        // ユーザーの作成
        if(!$admin = $this->ReviewProductRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        if(isset($search['like_name']) && isset($search['include_review'])){

            $search['keyword_type'] = 'product_name';
            $query_1 = $this->ReviewProductRepo->searchReviewProduct($search);

            $search['keyword_type'] = 'review';
            $query_2 = $this->ReviewProductRepo->searchReviewProduct($search);

            return $query_1->union($query_2)->orderByRaw($search['order_by_raw'])->paginate(10);

        }

        $search['keyword_type'] = 'product_name';
        $query = $this->ReviewProductRepo->searchReviewProduct($search);

        return $query->paginate(10);

    }

    // 複数取得
    public function getItems($where=[])
    {
        return $this->ReviewProductRepo->getItems($where);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->ReviewProductRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {
        return $this->ReviewProductRepo->updateItem($where, $data);
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->ReviewProductRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->ReviewProductRepo->deleteItem($where);
    }

    // --------------------------- id系 ---------------------------
    public function getItemById($search)
    {
        $arr = $this->getItem(['id'=>$search['id']]);

        $inputData = [
            "product_id" => $arr["id"],
            'per_page' => $search['per_page'],
            'block_users' => $search['block_users'],
            'order_by_raw' => $search['order_by_raw']
        ];

        //口コミの取得
        $reviews = $this->ReviewProductRepo->searchReview($inputData);

        foreach($reviews as $review){
            //ユーザーの取得
            $user = $this->UserProfileRepo->getItem(["user_id" => $review["user_id"]]);
            $review["image_id"] = $user->image_id;
            $review["nickname"] = $user->nickname;

            $userDetail = $this->UserDetailRepo->getItem(["user_id" => $review["user_id"]]);
            $review["kanri_user_flg"] = $userDetail->kanri_user_flg;

        }

        $arr["review"] = $reviews;

        return $arr;
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

    // 画像の登録
    public function updateOrCreateImageData($Id, $imageData, $wh=[])
    {
        if(!$image=$this->ReviewProductRepo->updateOrCreateImageData($Id, $wh, $imageData)) return false;
        return $image;
    }

    // --------------------------- 口コミ関連の処理 ---------------------------

    // idで削除
    public function deleteReviewById($id)
    {
        return $this->deleteReview(['id'=>$id]);
    }

    public function deleteReviewByProductId($id)
    {
        return $this->deleteReview(['product_id' => $id, 'per_page' => '-1']);
    }

    public function deleteReview($where)
    {
        return $this->ReviewProductRepo->deleteReview($where);
    }

    //口コミの投稿
    public function postReview(array $data)
    {

        //口コミの投稿
        if(!$admin = $this->ReviewProductRepo->postReview($data)) return false;

        //口コミ商品の取得
        $product_data = $this->getItem(['id'=>$admin->product_id]);

        //星評価を計算
        $total_star = $product_data->total_star;
        $review_count = $product_data->review_count;

        $total_star = $total_star + $admin->star;
        $review_count = $review_count + 1;
        $star = round($total_star/$review_count, 1);

        //星評価を更新
        $product_data = [
            'star' => $star,
            'total_star' => $total_star,
            'review_count' => $review_count,
        ];

        $product_response = $this->updateItem(['id'=>$admin->product_id], $product_data);

        // 返す
        return $product_response;

    }

    //追加 by Koki
    public function getUserReviews($search)
    {
        $reviews = $this->ReviewProductRepo->searchReview($search);
        return $reviews;
    }

}
