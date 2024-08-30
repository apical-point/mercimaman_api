<?php namespace App\Repositories\Eloquent;

use App\Repositories\AdminerRepositoryInterface;
use App\Repositories\Eloquent\Models\Adminer;

class AdminerRepository extends BaseEloquent implements AdminerRepositoryInterface
{
    protected $AdminerRepository;

    public function __construct(
        Adminer $adminer
    ){
        parent::__construct();
        $this->adminer = $adminer;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        // -------------------------------- 検索 --------------------------------
        if(in_array('email', $keys)) if($search['email']!=NULL) $query = $query->where('email', 'LIKE', "%".$search['email']."%");
        if(in_array('name', $keys)) if($search['name']!=NULL) $query = $query->where('name', 'LIKE', "%".$search['name']."%");
        if(in_array('AdminerRepository_level', $keys)) if($search['AdminerRepository_level']!=NULL) $query = $query->where('AdminerRepository_level', $search['AdminerRepository_level']);
        if(in_array('AdminerRepository_type', $keys)) if($search['AdminerRepository_type']!=NULL) $query = $query->where('AdminerRepository_type', $search['AdminerRepository_type']);
        if(in_array('shop_id', $keys)) if($search['shop_id']!=NULL) $query = $query->where('shop_id', $search['shop_id']);

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            // $query = $query->orderBy(, $search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->adminer;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->adminer->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->adminer->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->adminer->where($where);
        if($take)  $query->take($take);
        if($orderByRaw)  $query->orderByRaw($orderByRaw);

        return $query->get();
    }

    // 更新
    public function updateItem(array $where, array $data)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->fill($data)->save();
    }


    // 複数の削除
    public function deleteItems(array $where)
    {
        return $this->adminer->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }


    // ------------------------------------- その他関数 -------------------------------------
    // 画像データの新規作成
    public function createImageByAdminerRepositoryIdAndImageData($AdminerRepositoryId, array $imageData)
    {
        // ユーザーの取得
        if(!$AdminerRepository = $this->getItem(['id'=>$AdminerRepositoryId])) return false;

        return $AdminerRepository->image()->create($imageData);

        // 画像データの登録
        // return $AdminerRepository->image()->create($imageData);
    }

    // AdminerRepository_idで画像データの削除
    public function deleteImageByAdminerRepositoryId($AdminerRepositoryId)
    {
        // ユーザーの取得
        if(!$AdminerRepository = $this->getItem(['id'=>$AdminerRepositoryId])) return false;

        // 画像データの登録
        return $AdminerRepository->image()->delete();
    }

}
