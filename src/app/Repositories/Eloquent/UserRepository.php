<?php namespace App\Repositories\Eloquent;

use App\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\Models\User;
use Illuminate\Support\Facades\Log;

class UserRepository extends BaseEloquent implements UserRepositoryInterface
{
    protected $user;

    public function __construct(User $user){
        parent::__construct();
        $this->user = $user;
    }

    // ------------------------------------- basic -------------------------------------
    // 検索
    public function getSearchQuery($query, $search)
    {
        $keys = array_keys($search);
        \Log::debug($search);
        // -------------------------------- 検索 --------------------------------
        if(in_array('user_id', $keys)) if($search['user_id']!=NULL) $query = $query->where('id', $search['user_id']);
        if(in_array('email', $keys)) if($search['email']!=NULL) $query = $query->where('email', 'LIKE', "%".$search['email']."%");
        if(in_array('status', $keys)) if($search['status']!=NULL) $query = $query->where('status', $search['status']);
        if(in_array('non_id', $keys)) if($search['non_id']!=NULL) $query = $query->whereNotIn('users.id', [$search['non_id']]); //表示しない
        if(in_array('in_id', $keys)) if($search['in_id']!=NULL) $query = $query->whereIn('users.id', $search['in_id']); //表示しない

        // 詳細から検索
        if(in_array('status', $keys)) if($search['status']!="0"){
            $query = $query->whereHas('userDetail', function($query) use($search, $keys) {
                if (in_array('like_name', $keys) && $search['like_name']!=NULL) {
                    $likeName = $search['like_name'];
                    $query->where(function($query) use($likeName){
                      $query->where(\DB::raw('CONCAT(last_name, first_name)'), 'LIKE', "%".$likeName."%");
                    });
                }
                if(in_array('tel', $keys)) if($search['tel']!=NULL) $query = $query->where('tel', 'LIKE', "%".$search['tel']."%");
                if(in_array('prefecture_id', $keys)) if($search['prefecture_id']!=NULL) $query = $query->where('prefecture_id', $search['prefecture_id']);
                if(in_array('identification', $keys)) if($search['identification']!=NULL) $query = $query->where('identification', $search['identification']);
                if(in_array('mail_flg', $keys)) if($search['mail_flg']!=NULL) $query = $query->where('mail_flg', $search['mail_flg']);

            });

            //イベント会員はuserProfileが無いので省く
            if(!in_array("user_type_check", $keys)){

                $query = $query->whereHas('userProfile', function($query) use($search, $keys) {
                    if(in_array('birthday_from', $keys)) if($search['birthday_from']!=NULL) $query = $query->where('birthday', '>=', $search['birthday_from']);
                    if(in_array('birthday_to', $keys)) if($search['birthday_to']!=NULL) $query = $query->where('birthday', '<=', $search['birthday_to']);
                    //if(in_array('taste', $keys)) if($search['prefecture_id']!=NULL) $query = $query->where('prefecture_id', $search['prefecture_id']);
                    if(in_array('nickname', $keys)) if($search['nickname']!=NULL) $query = $query->where('nickname', 'LIKE', "%". $search['nickname']."%");

                    if(in_array('condition', $keys)) if($search['condition']!=NULL) $query = $query->where('condition', $search['condition']);


                    if(in_array('taste', $keys)) if($search['taste']!=NULL){
                        $query = $query->whereIn('taste1', $search['taste'])->orwhereIn('taste2', $search['taste'])->orwhereIn('taste3', $search['taste']);
                    }

                    if(in_array('mother_interest', $keys)) if($search['mother_interest']!=NULL){
                        $query = $query->where(function($query) use($search){
                            $query->where('mother_interest1', $search['mother_interest'])
                                    ->orWhere('mother_interest2', $search['mother_interest'])
                                    ->orWhere('mother_interest3', $search['mother_interest'])
                                    ->orWhere('mother_interest4', $search['mother_interest']);
                        });
                    }

                    if(in_array('child_interest', $keys)) if($search['child_interest']!=NULL){
                        $query = $query->where(function($query) use($search){
                            $query->where('child_interest1', $search['child_interest'])
                                    ->orWhere('child_interest2', $search['child_interest'])
                                    ->orWhere('child_interest3', $search['child_interest'])
                                    ->orWhere('child_interest4', $search['child_interest']);
                        });
                    }

                    if(in_array('experience', $keys)) if($search['experience']!=NULL){
                        $query = $query->where(function($query) use($search){
                        $query->where('experience1', $search['experience'])
                                    ->orWhere('experience2', $search['experience'])
                                    ->orWhere('experience3', $search['experience'])
                                    ->orWhere('experience4', $search['experience']);
                        });
                    }

                });
            }
            //子供の年齢と性別はそれぞれ対応させるべきなので、修正
            if((in_array('child-gender', $keys) && $search['child-gender']!=NULL)  && (!in_array('child_birthday_from', $keys) && !in_array('child_birthday_to', $keys) )){
                $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                    $query = $query->where(function($query) use($search){
                        $query->where('child_gender1', '=', $search['child-gender'])
                        ->orWhere('child_gender2', '=', $search['child-gender'])
                        ->orWhere('child_gender3', '=', $search['child-gender'])
                        ->orWhere('child_gender4', '=', $search['child-gender'])
                        ->orWhere('child_gender5', '=', $search['child-gender']);
                    });
                });
            }
            elseif((in_array('child-gender', $keys) && $search['child-gender']!=NULL) &&  (in_array('child_birthday_from', $keys) || in_array('child_birthday_to', $keys) )){

                //子供の年齢と性別で検索
                if(in_array('child_birthday_from', $keys) && in_array('child_birthday_to', $keys)){
                    $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                        $query = $query->where(function($query) use($search){
                            $query->where('child_birthday1', '<=', $search['child_birthday_from'])
                            ->where('child_birthday1', '>=', $search['child_birthday_to'])
                            ->where('child_gender1', '=', $search['child-gender']);
                        });

                            $query = $query->orwhere(function($query) use($search){
                                $query->orWhere('child_birthday2', '<=', $search['child_birthday_from'])
                                ->where('child_birthday2', '>=', $search['child_birthday_to'])
                                ->where('child_gender2', '=', $search['child-gender']);

                            });
                                $query = $query->orwhere(function($query) use($search){
                                    $query->orWhere('child_birthday3', '<=', $search['child_birthday_from'])
                                    ->where('child_birthday3', '>=', $search['child_birthday_to'])
                                    ->where('child_gender3', '=', $search['child-gender']);
                                });
                                    $query = $query->orwhere(function($query) use($search){
                                        $query->orWhere('child_birthday4', '<=', $search['child_birthday_from'])
                                        ->where('child_birthday4', '>=', $search['child_birthday_to'])
                                        ->where('child_gender4', '=', $search['child-gender']);
                                    });
                                        $query = $query->orwhere(function($query) use($search){
                                            $query->orWhere('child_birthday5', '<=', $search['child_birthday_from'])
                                            ->where('child_birthday5', '>=', $search['child_birthday_to'])
                                            ->where('child_gender5', '=', $search['child-gender']);
                                        });

                    });
                }
                elseif(in_array('child_birthday_from', $keys) && $search['child_birthday_from']!=NULL){
                    $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                        $query = $query->where(function($query) use($search){

                            $query->where('child_birthday1', '<=', $search['child_birthday_from'])
                            ->where('child_gender1', '=', $search['child-gender']);

                            $query->orWhere('child_birthday2', '<=', $search['child_birthday_from'])
                            ->where('child_gender2', '=', $search['child-gender']);

                            $query->orWhere('child_birthday3', '<=', $search['child_birthday_from'])
                            ->where('child_gender3', '=', $search['child-gender']);

                            $query->orWhere('child_birthday4', '<=', $search['child_birthday_from'])
                            ->where('child_gender4', '=', $search['child-gender']);

                            $query->orWhere('child_birthday5', '<=', $search['child_birthday_from'])
                            ->where('child_gender5', '=', $search['child-gender']);

                        });
                    });
                }
                else{
                    $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                        $query = $query->where(function($query) use($search){
                            $query->where('child_birthday1', '>=', $search['child_birthday_to'])
                            ->where('child_gender1', '=', $search['child-gender']);
                            $query->orWhere('child_birthday2', '>=', $search['child_birthday_to'])
                            ->where('child_gender2', '=', $search['child-gender']);
                            $query->orWhere('child_birthday3', '>=', $search['child_birthday_to'])
                            ->where('child_gender3', '=', $search['child-gender']);
                            $query->orWhere('child_birthday4', '>=', $search['child_birthday_to'])
                            ->where('child_gender4', '=', $search['child-gender']);
                            $query->orWhere('child_birthday5', '>=', $search['child_birthday_to'])
                            ->where('child_gender5', '=', $search['child-gender']);
                        });
                    });

                }


            }
            elseif((!in_array('child-gender', $keys) || $search['child-gender']==NULL) &&  (in_array('child_birthday_from', $keys) || in_array('child_birthday_to', $keys) )){


                //子供の生年月日検索
                if(in_array('child_birthday_from', $keys) && in_array('child_birthday_to', $keys)){

                    $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                        $query = $query->where(function($query) use($search){
                            $query->where('child_birthday1', '<=', $search['child_birthday_from'])
                            ->where('child_birthday1', '>=', $search['child_birthday_to']);
                        });
                            $query = $query->orwhere(function($query) use($search){
                                $query->orWhere('child_birthday2', '<=', $search['child_birthday_from'])
                                ->where('child_birthday2', '>=', $search['child_birthday_to']);
                            });
                                $query = $query->orwhere(function($query) use($search){
                                    $query->orWhere('child_birthday3', '<=', $search['child_birthday_from'])
                                    ->where('child_birthday3', '>=', $search['child_birthday_to']);
                                });
                                    $query = $query->orwhere(function($query) use($search){
                                        $query->orWhere('child_birthday4', '<=', $search['child_birthday_from'])
                                        ->where('child_birthday4', '>=', $search['child_birthday_to']);
                                    });
                                        $query = $query->orwhere(function($query) use($search){
                                            $query->orWhere('child_birthday5', '<=', $search['child_birthday_from'])
                                            ->where('child_birthday5', '>=', $search['child_birthday_to']);
                                        });

                    });
                }
                elseif(in_array('child_birthday_from', $keys) && $search['child_birthday_from']!=NULL){

                    $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                        $query = $query->where(function($query) use($search){
                            $query->where('child_birthday1', '<=', $search['child_birthday_from'])
                            ->orWhere('child_birthday2', '<=', $search['child_birthday_from'])
                            ->orWhere('child_birthday3', '<=', $search['child_birthday_from'])
                            ->orWhere('child_birthday4', '<=', $search['child_birthday_from'])
                            ->orWhere('child_birthday5', '<=', $search['child_birthday_from']);
                        });
                    });
                }
                elseif(in_array('child_birthday_to', $keys) && $search['child_birthday_to']!=NULL){

                    $query = $query->whereHas('userProfile', function($query) use ($search, $keys) {
                        $query = $query->where(function($query) use($search){
                            $query->where('child_birthday1', '>=', $search['child_birthday_to'])
                            ->orWhere('child_birthday2', '>=', $search['child_birthday_to'])
                            ->orWhere('child_birthday3', '>=', $search['child_birthday_to'])
                            ->orWhere('child_birthday4', '>=', $search['child_birthday_to'])
                            ->orWhere('child_birthday5', '>=', $search['child_birthday_to']);
                        });
                    });
                }

            }


            //過去に一度でもメッセージのやりとりがあったママ検索
            if(in_array('usermessage', $keys)){
                $item = $search["id"];

                    $query = $query->whereHas('messageFrom', function($query) use ($item) {
                        $query = $query->where(function($query) use($item){
                            $query->where('user_to_id', $item);

                        });
                    });

                    $query = $query->orwhereHas('messageTo', function($query) use ($item) {
                        $query = $query->where(function($query) use($item){
                            $query->where('user_from_id', $item);

                        });
                    });

            }

            
        }

        // -------------------------------- 並び替え --------------------------------
        if(!empty($search['order_by'])) {
            // $query = $query->orderBy(, $search['order_by']);

        } elseif(!empty($search['order_by_raw'])) {
            $query = $query->orderByRaw($search['order_by_raw']);
        }

        \Log::debug(print_r($query->toSql(), true) . "     " . print_r($query->getBindings(), true));

        return $query;
    }

    // リストの取得 per_page=-1のときは全件取得
    public function getList($search=[])
    {
        // query化
        $query = $this->user;

        if (isset($search['favorite_id'])){
            $user_id = $search['favorite_id'];
            $query = $query->select('users.*', \DB::Raw('IFNULL( `user_favorites`.`user_id` , 0 ) as favorite'))
                    ->leftJoin('user_favorites', function ($query) use($user_id) {
                                    $query->on('user_favorites.follow_id', '=', 'users.id')
                                    ->where('user_favorites.user_id', '=', $user_id)->where('user_favorites.type', '=', '2');});
        }else{
            //$query = $this->user;
        }

        // ページ
        $perPage = $this->getPerPage($search);

        return $perPage <> -1 ? $this->getSearchQuery($query, $search)->paginate($perPage) : $this->getSearchQuery($query, $search)->get();
    }

    // 新規作成
    public function createItem(array $data)
    {
        return $this->user->create($data);
    }

    // 1件数の取得
    public function getItem($where)
    {
    //    return $this->user->withTrashed()->where($where)->first();
        return $this->user->where($where)->first();
    }

    // 1件数の取得
    public function getItems(array $where, $take=0, $orderByRaw='')
    {
        $query = $this->user->where($where);
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
        return $this->user->where($where)->delete();
    }

    // 削除
    public function deleteItem(array $where)
    {
        if(empty($item=$this->getItem($where)))  return false;
        return $item->delete();
    }

    // 本登録済みのもの取得
    public function getMainRegistrationItem($where)
    {
        return $this->user->mainRegistration()->where($where)->get();
    }

    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->user->whereIn('id', $ids)->get();
    }

    // 画像データの新規作成or更新
    public function updateOrCreateImageData($Id, $where, $imageData)
    {
        // 商品取得
        if(!$contentRow = $this->getItem(['id'=>$Id])) return false;

        // 画像作成
        return $contentRow->images()->updateOrCreate($where, $imageData);
    }



}
