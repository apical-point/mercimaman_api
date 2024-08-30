<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;


// リポジトリ
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserDetailRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use App\Repositories\Eloquent\BlockRepository;

class UserService extends Bases\BaseService
{
    // リポジトリ
    protected $userRepo;
    protected $userDetailRepo;
    protected $messageRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        UserDetailRepositoryInterface $userDetailRepo,
        MessageRepositoryInterface $messageRepo,
        BlockRepository $blockRepo
    ) {

        // リポジトリ
        $this->userRepo = $userRepo;
        $this->userDetailRepo = $userDetailRepo;
        $this->messageRepo = $messageRepo;
        $this->blockRepo = $blockRepo;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data, $password='')
    {
        // パスワードが指定されていれば入力データとする
        if(!empty($password)) $data['password'] = $password;

        // ユーザーの作成
        if(!$user = $this->userRepo->createItem($data)) return false;

        // // ユーザー詳細の登録
        // if(!$this->userDetailRepo->createUserDetailByUserId($user->id, $data)) return false;

        // 返す
        return $user;
    }

    // リスト取得
    public function getList($search=[])
    {
        $arr = $this->userRepo->getList($search);

        return $arr;

    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->userRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->userRepo->getItem($where);
    }

    // 更新
    public function updateItem($where, $data)
    {
        // ユーザーの更新
        if(!$this->userRepo->updateItem($where, $data)) return false;

        return true;
    }

    // 削除
    public function deleteItem($where)
    {
        return $this->userRepo->deleteItem($where);
    }

    // i削除
    public function deleteItems($where)
    {
        return $this->userRepo->deleteItem($where);
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
    // 仮登録でメアドが使えるかどうか
    public function canUseEmailOfEntry($email)
    {
        $userRows = $this->userRepo->getMainRegistrationItem([['email', $email]]);

        return $userRows->count()===0 ? true : false;
    }

    // 更新でメアドが使えるかどうか
    public function canUseEmailOfUpdate($id, $email)
    {
        return !empty($this->userRepo->getItem([['id', '!=', $id], ['email', $email]])) ? false :true;
    }


    // --------------------------- その他関数 ---------------------------
    // ハッシュ化
    public function passwordHash($password) {
        return Hash::make($password);
    }

    // UserIdから重複しない64文字のパラメータを返す
    public function getRandomParam($id) {
        $idHash = md5($id.config('const.site.PARAM_SALT'));
        $length = 16;

        // ハッシュの衝突の考慮
        $collisionCheck = true;
        while ($collisionCheck) {
            $param = $idHash.bin2hex(random_bytes($length));
            $userRow = $this->userRepo->getItem(['param' => $param]);
            if(!$userRow) {
                $collisionCheck = false;
            }
        }

        return $param;
    }

    // 登録期限取得
    public function getRegistLimit($limitHour) {
        $registLimit = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));
        $registLimit->modify('+'.$limitHour.' hour');
        return $registLimit->format('Y-m-d H:i:s');
    }

    // 登録日時の確認
    public function isRegistLimit($registLimit)
    {
        return strtotime($registLimit) > strtotime(date("Y-m-d H:i:s"));
    }

    // パスワードの更新
    public function updatePasswordById($id, $passwrod)
    {
        return $this->updateItemById($id, ['password'=>$this->passwordHash($passwrod)]);
    }

    // メアドの更新
    public function updateEmailById($id, $email)
    {
        return $this->updateItemById($id, ['email'=>$email]);
    }

    // 本登録に変更
    public function updateMainRegistById($id)
    {
        return $this->updateItemById($id, ['status'=>1]);
    }

    // idsで取得
    public function getItemsByIds($ids)
    {
        return $this->userRepo->getItemsByIds($ids);
    }

    // パスワード再設定用のパラメータをセット
    public function setResetPasswordParam($id) {
        $newData = [
            'regist_limit' => $this->getRegistLimit(config('const.site.PASSWORD_LIMIT_HOUR')),
            'param' => $this->getRandomParam($id),
        ];

        return $this->userRepo->updateItem(['id'=>$id], $newData);
    }

    // メールアドレス変更用のパラメータをセット
    public function setChangeEmailParam($id, $temporaryEmail) {
        $newData = [
            'regist_limit' => $this->getRegistLimit(1),
            'param' => $this->getRandomParam($id),
            'temporary_email' => $temporaryEmail,
        ];

        return $this->userRepo->updateItem(['id'=>$id], $newData);
    }

    // パラメータと登録期限のリセット
    public function resetParam($id) {
        $newData = [
            'regist_limit' => null,
            'param' => null,
            'temporary_email' => null,
        ];

        return $this->userRepo->updateItem(['id'=>$id], $newData);
    }

    // ハッシュされたパスワードを取得
    public function getHashedPasswordById($id) {
        $hidden = $this->getItemById($id)->makeVisible('password')->toArray();
        $hashedPassword = $hidden['password'];
        return $hashedPassword;
    }

    // メイン画像の登録
    public function updateOrCreateImageData($Id, $imageData, $status)
    {
        $imageData['status'] = $status;
        if(!$image=$this->userRepo->updateOrCreateImageData($Id, ['status'=>$status], $imageData)) return false;

        return $image;
    }



}
