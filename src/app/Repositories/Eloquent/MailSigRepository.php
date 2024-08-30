<?php namespace App\Repositories\Eloquent;

use App\Repositories\MailSigRepositoryInterface;
use App\Repositories\Eloquent\Models\MailSig;

class MailSigRepository extends BaseEloquent implements MailSigRepositoryInterface
{
    protected $mailSig;

    public function __construct(
        MailSig $mailSig
    ){
        parent::__construct();
        $this->mailSig = $mailSig;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->mailSig->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

        $query = $query->orderByRaw('id desc');

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->mailSig;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->mailSig->where($where)->first();
    }

    //
    public function getItems(array $where, $take=0, $orderByRaw='')
    {

        $query = $this->mailSig->where($where);

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
        return $this->mailSig->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

    // idで1件の取得
    public function getItemById($id)
    {
        return  $this->mailSig->find($id);
    }
}
