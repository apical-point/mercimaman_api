<?php namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\Models\ReviewProductOffers;

class ReviewProductOffersRepository extends BaseEloquent
{
    protected $ReviewProductOffersRepository;

    public function __construct(
        ReviewProductOffers $ReviewProductOffers
    ){
        parent::__construct();
        $this->ReviewProductOffers = $ReviewProductOffers;
    }

    // ------------------------------------- basic -------------------------------------

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->ReviewProductOffers;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $query->paginate($perPage) : $query->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->ReviewProductOffers->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
        return $this->ReviewProductOffers->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->ReviewProductOffers->where($where);
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

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }


    // ------------------------------------- その他関数 -------------------------------------


}