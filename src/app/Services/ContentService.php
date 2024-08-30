<?php namespace App\Services;

// ulid
// use \Ulid;
use App\Repositories\Eloquent\Models\PublicitySurvey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// リポジトリ
use App\Repositories\ContentRepositoryInterface;

use App\Repositories\UserDetailRepositoryInterface;

class ContentService extends Bases\BaseService
{
    // リポジトリ
    protected $contentRepo;

    public function __construct(
        UserDetailRepositoryInterface $userDetailRepo,
        ContentRepositoryInterface $contentRepo
    ) {
        // リポジトリ
        $this->contentRepo = $contentRepo;
        $this->userDetailRepo = $userDetailRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        if(!$admin = $this->contentRepo->createItem($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->contentRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->contentRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->contentRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {

        // 更新
        if(!$this->contentRepo->updateItem($where, $data)) return false;

        // 取得
        if(!$admin = $this->getItem($where)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->contentRepo->deleteItem($where);
    }

    // 複数削除
    public function deleteItems($where)
    {
        return $this->contentRepo->deleteItem($where);
    }

    // idで削除
    public function deleteItemById($id)
    {
        return $this->contentRepo->deleteItem(['id'=>$id]);
    }

    // 1件数の取得
    public function getItemById($id)
    {
        return $this->getItem(['id'=>$id]);
    }

    // メイン画像の登録
    public function updateOrCreateImageData($contentId, $imageData, $status)
    {
        $imageData['status'] = $status;
        if(!$image=$this->contentRepo->updateOrCreateImageData($contentId, ['status'=>$status], $imageData)) return false;

        return $image;
    }

     /**
     *
     * これ知ってるよう画像登録
     *
     * @param mixed $contentId
     * @param mixed $imageData
     * @param mixed $status
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreateSurveyImage($contentId, $imageData, $status)
    {
        $imageData['status'] = $status;
        // 商品取得
        if(!$contentRow = PublicitySurvey::find($contentId)) return false;

        $where = ['status'=>$status];

        // 画像作成
        return $contentRow->mainImage()->updateOrCreate($where, $imageData);
    }


    //--------クロスワード------------------
    // 作成
    public function createItemCrossword(array $data)
    {
        if(!$admin = $this->contentRepo->createItemCrossword($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getListCrossword($search=[])
    {
        $arr =  $this->contentRepo->getListCrossword($search);

        return $arr;
    }

    // 1件数の取得
    public function getItemByIdCrossword($id, $user_id)
    {
        $arr =  $this->contentRepo->getItemCrossword(['id'=>$id]);
        return $arr;

    }

    // 更新
    public function updateItemCrossword($where, $data, $user_id)
    {

            // 内容更新
        if(!$this->contentRepo->updateItemCrossword($where, $data)) return false;



        return true;
    }
    // idで削除
    public function deleteItemByIdCrossword($id)
    {
        return $this->contentRepo->deleteItemCrossword(['id'=>$id]);
    }


    //--------タロット------------------
    // 作成
    public function createItemTarot(array $data)
    {
        if(!$admin = $this->contentRepo->createItemTarot($data)) return false;

        // 返す
        return $admin;
    }

    // 複数取得
    public function getListTarot($search=[])
    {
        $arr =  $this->contentRepo->getListTarot($search);
        //占いをした人数
        foreach($arr as $key=>$val){

            $search = [];
            $search["tarot_id"] = $val["id"];
            $tmp = $this->contentRepo->getListTarotUser($search);

            $arr[$key]["user_total"] = count($tmp);

        }



        return $arr;
    }

    // 1件数の取得
    public function getItemByIdTarot($id, $user_id)
    {
        $arr =  $this->contentRepo->getItemTarot(['id'=>$id]);
        return $arr;

    }

    // 更新
    public function updateItemTarot($where, $data, $user_id)
    {

        // 内容更新
        if(!$this->contentRepo->updateItemTarot($where, $data)) return false;



        return true;
    }
    // idで削除
    public function deleteItemByIdTarot($id)
    {
        return $this->contentRepo->deleteItemTarot(['id'=>$id]);
    }

    //占いを行ったユーザーの結果登録
    public function createItemTarotUser($data)
    {
        if(!$admin = $this->contentRepo->createItemTarotUser($data)) return false;

        // 返す
        return $admin;
    }

    public function getListTarotUser($search=[])
    {
        $arr =  $this->contentRepo->getListTarotUser($search);

        return $arr;
    }

     // これ知ってる？
    /**
     *
     *
     * @param array<string, string> {
     *  'themedate' => string,
     *  'theme' => string
     * } $data
     * @return mixed
     */
    public function createItemSurvey(array $data)
    {
        Log::debug($data);
        $survey = PublicitySurvey::create([
            'themedate' => $data['themedate'],
            'theme' => $data['theme'],
            'description' => $data['description'],
            'url' => $data['url']
        ]);

        if (!$survey) return false;
        // 返す
        return $survey;
    }

    // 1件数の取得
    public function getItemByIdSurvey($id)
    {
        $arr =  PublicitySurvey::where('id', $id)->get();
        return $arr;

    }

}
