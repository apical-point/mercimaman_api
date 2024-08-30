<?php namespace App\Repositories\Eloquent;

use App\Repositories\MailTemplateRepositoryInterface;
use App\Repositories\Eloquent\Models\MailTemplate;

class MailTemplateRepository extends BaseEloquent implements MailTemplateRepositoryInterface
{
    protected $mailTemplate;

    public function __construct(
        MailTemplate $mailTemplate
    ){
        parent::__construct();
        $this->mailTemplate = $mailTemplate;
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->mailTemplate->create($data);
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);

         \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

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
        $query = $this->mailTemplate;

        // ページ
        $perPage = $this->getPerPage($search);

        //
        return $perPage!==-1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }


    // 1件数の取得
    public function getItem($where)
    {
        return $this->mailTemplate->where($where)->first();
    }

    //
    public function getItems(array $where, $take=0, $orderByRaw='')
    {

        $query = $this->mailTemplate->where($where);

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
        return $this->mailTemplate->where($where)->delete();
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
        return  $this->mailTemplate->find($id);
    }
}
