<?php namespace App\Services;

// ulid
// use \Ulid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


// リポジトリ
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\OrderDetailRepositoryInterface;

// サービス
use App\Services\UserService;
use App\Services\SiteConfigService;

// モデル
use App\Repositories\Eloquent\Models\UserSubscription;
use App\Repositories\Eloquent\Models\Order;

class OrderService extends Bases\BaseService
{
    protected $orderRepo;
    protected $orderDeatilRepo;
    protected $userService;
    protected $siteConfigService;

    public function __construct(
        OrderRepositoryInterface $orderRepo,
        OrderDetailRepositoryInterface $orderDeatilRepo,
        UserService $userService,
        SiteConfigService $siteConfigService
    ) {
        $this->orderRepo = $orderRepo;
        $this->orderDeatilRepo = $orderDeatilRepo;
        $this->userService = $userService;
        $this->siteConfigService = $siteConfigService;
    }

    // --------------------------- 基本的なもの ---------------------------
    // 作成
    public function createItem(array $data)
    {
        return $this->orderRepo->createItem($data);
    }

    // 複数取得
    public function getList($search=[])
    {
        return $this->orderRepo->getList($search);
    }

    // 複数取得
    public function getItems($where=[], $take=0, $orderByRaw='')
    {
        return $this->orderRepo->getItems($where, $take, $orderByRaw);
    }

    // 1件取得
    public function getItem($where)
    {
        return $this->orderRepo->getItem($where);
    }

    // idで1件取得
    public function getItemById($id)
    {
        return $this->orderRepo->getItem(['id'=>$id]);
    }

    // 1件の更新
    public function updateItem($where, $data)
    {

        \Log::debug($where);
        \Log::debug($data);

        return $this->orderRepo->updateItem($where, $data);
    }

    // 1件の削除
    public function deleteItem($where)
    {
        return $this->orderRepo->deleteItem($where);
    }

    // 複数の削除
    public function deleteItems($where)
    {
        return $this->orderRepo->deleteItems($where);
    }

    // 売上複数取得
    public function getSalesGroupList($search=[])
    {
        return $this->orderRepo->getSalesGroupList($search);
    }


    // 売上の詳細取得
    public function getSalesGroupDetails($search=[])
    {
        return $this->orderRepo->getSalesGroupDetails($search);
    }

    // 売上複数取得
    public function getpayment()
    {
        return $this->orderRepo->getpayment();
    }



    /**
     * 指定したユーザーの利用済無料回数を返す
     */
    public function getFreeOrderCount($userId)
    {
        $where = [
            'user_id' => $userId,
            'type' => Order::$TYPE_SUBSCRIPTION,
            'status' => Order::$STATUS_CREDIT_OK,
            'free' => 1,
            'per_page' => -1,
        ];
        $res = $this->orderRepo->getList($where);
        if ($res === false){
            return false;
        }
        return count($res);
    }


    // --------------------------- チェック関数 ---------------------------
    // public function isInputDataProducts($products)
    // {
    // }

    // --------------------------- その他の関数 ---------------------------
    // 注文、注文詳細作成
    public function createOrdersAndOrderDetails($cartData, $productRows, $userId)
    {
        // 商品を連想配列に変換する
        $products = $productRows->toArray();

        // 結果配列
        $results = [];

        $orderData = [
            'shop_id' => 0,
            'user_id' => $userId,
            'status' => 1,
            'subtotal_price' => 0,
        ];

        // 注文の作成
        if(!$newOrderRow = $this->createItem($orderData)) return false;

        $updateOrderData = $newOrderRow->toArray();


        // 商品毎に注文詳細データを作る
        foreach ($products as $key => $productValue) {

            // 初期化
            $orderDetail = [];


            $productId = $productValue['id'];
            $keyIndex = array_search($productId, $cartData['product_ids']);

            $orderDetail['order_id'] = $newOrderRow->id;
            $orderDetail['num'] = $cartData['nums'][$keyIndex];
            $orderDetail['product_id'] = $productId;
            $orderDetail['budget_price'] = $productValue['price'];
            $orderDetail['total_price'] = $orderDetail['budget_price'] * $orderDetail['num'];

            $updateOrderData['subtotal_price'] += $orderDetail['total_price'];


            // 消費税などを計算する---今回は0円なのでなにも行わなでおく
            // $results['discount_price'] = $results['discount_price'];
            // $results['tax_price'] = $results['tax_price'];
            // $orderData['total_price'] = $orderData['subtotal_price'];

            // 注文詳細を作成する
            if(!$this->orderDeatilRepo->createItem($orderDetail)) return false;
        }

        // 注文の更新
        if(!$newOrderRow = $this->updateItem(['id'=>$updateOrderData['id']], $updateOrderData)) return false;
        return $updateOrderData['id'];
    }


    // カートデータの取得
    public function getCartOrderData($cart, $productRows, $prefectureName, $areaId) {
        $cartData = $cart['cart'];

        $results = [];
        $productSizes = [];

        $results['items'] = [];
        $results['subtotal_price'] = 0;

        // 配列毎に回す
        foreach ($cartData as $cartItem) {
            // 注文詳細のデータ
            $result = [];

            //  商品の取得
            if(!$productRow = $productRows->where('id', $cartItem['product_id'])->first()) return false;

            // 公開していなければエラー
            if(!$productRow->is_public) return false;

            // 遅延読み込みを行う
            $productRow->load(['productCategory', 'mainImage']);

            // 商品を入れる
            $result['product'] = $productRow;

            // カートデータ
            $result['cart'] = $cartItem;

            // 量
            $quantity = $result['cart']['quantity'];

            // 単価
            $result['unit_price'] = $productRow->price;
            $result['total_price'] = $productRow->price * $quantity;

            $results['subtotal_price'] += $result['total_price'];
            $results['items'][] = $result;

            // 送料計算用の配列の作成
            for($i = 0; $i < $quantity; $i++){
                $productSizes[] = [
                    'size' => $productRow->size,
                    'weight' => $productRow->weight,
                ];
            }
        }

        $results['prefecture_name'] = $prefectureName;
        $results['delivery_charge'] = $this->__getDeliveryPrice($productSizes, $areaId);
        $tax_total_price = $results['subtotal_price'] + $results['delivery_charge'];
        $results['tax_price'] = $this->__getTaxPrice($tax_total_price, config('const.site.TAX_RATE'));
        $results['total_price'] = $tax_total_price + $results['tax_price'];

        return $results;
    }

    // スキップに変更
    public function updateOrderStatusToSkip($orderId, $userId) {
        if(!$item = $this->getItem(['id' => $orderId, 'user_id' => $userId, 'type' => Order::$TYPE_SUBSCRIPTION, 'status' => UserSubscription::$STATUS_SUBSCRIBE])) throw new \Exception(__('messages.not_found'));
        $this->updateItem(['id'=>$item->id], ['status' => UserSubscription::$STATUS_SKIP]);
        return true;
    }

    public function downloadOrderCsv($search=[]) {
        $search['type'] = Order::$TYPE_SUBSCRIPTION;
        if ($search['status'] == ""){
            $search['status'] = array(Order::$STATUS_CREDIT_OK,Order::$STATUS_RE_ORDER);
        }

        $search['per_page'] = -1;
        $orders = $this->orderRepo->getList($search);
        $orders->loadMissing(['orderDetails','orderDetails.plan']);
    //    $orders = $this->orderRepo->getCsvOrder($search);
        $orderData = $orders->toArray();
        $csv = [];

        // 全カラム名を配列で取得
        $csv[] = $this->__getSubOrderCsvHeader();

        // csv用の配列を取得
        foreach ($orderData as $order) {
            if ($search['bill_status'] == $order['order_details'][0]['plan']['bill_status']){
                $csv[] = $this->__getSubOrderCsvRow($order,$search['bill_status']);
            }
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=subscription_orders.csv",
        ];

        $csvData = [
            'headers' => $headers,
            'data' => $csv,
        ];

        return $csvData;

    }

    public function downloadOrderShopCsv($search=[]) {
        $search['type'] = Order::$TYPE_SUBSCRIPTION;
        if ($search['status'] == ""){
            $search['status'] = array(Order::$STATUS_CREDIT_OK,Order::$STATUS_RE_ORDER);
        }

        $search['per_page'] = -1;
        $orders = $this->orderRepo->getList($search);
//        $orders = $this->orderRepo->getCsvOrder($search);
        $orders->loadMissing(['orderDetails','orderDetails.plan']);
        $orderData = $orders->toArray();
        $csv = [];

        // 全カラム名を配列で取得
        $csv[] = $this->__getSubOrderCsvHeader();

        //店舗情報取得
        $shop_data = $this->shopService->getItemById($search['shop_id']);
        $shop_data->loadMissing(['prefecture']);

        // csv用の配列を取得
        foreach ($orderData as $order) {
            if ($search['bill_status'] == $order['order_details'][0]['plan']['bill_status']){
                $csv[] = $this->__getSubOrderCsvShopRow($order, $shop_data, $search['bill_status']);
            }
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=subscription_orders.csv",
        ];

        $csvData = [
            'headers' => $headers,
            'data' => $csv,
        ];

        return $csvData;

    }

    public function getListstatusAll($search=[]) {
        $search['per_page'] = -1;
        $orders = $this->orderRepo->getList($search);
        //$orderData = $orders->toArray();
        return $orders;
    }

    public function sendMessageToOrders($orderIds) {

    }

    private function __getSubOrderCsvHeader() {
        return [
            'お客様管理番号',
            '送り状種類',
            'クール区分',
            '',
            '出荷予定日',
            '',
            '',
            '',
            'お届け先電話番号',
            '',
            'お届け先郵便番号',
            'お届け先住所',
            'お届け先アパートマンション名',
            '',
            '',
            'お届け先名',
            '',
            '敬称',
            '',
            'ご依頼主電話番号',
            '',
            'ご依頼主郵便番号',
            'ご依頼主住所',
            'ご依頼主アパートマンション',
            'ご依頼主名',
            '',
            '',
            '品名１',
            '',
            '',
            '',
            '',
            '記事',
            '',
            '',
            '',
            '',
            '',
            '',
            '請求先顧客コード',
            '',
            '運賃管理番号',
        ];
    }

    private function __getSubOrderCsvRow($order, $status) {

        if ($order['order_details'][0]['plan']['bill_status'] == "1"){
            //ネコポス
            return [
                $order['id'], // お客様管理番号
                '7', // 送り状種類
                '0', // クール区分
                '',
                date('Y/m/d', strtotime($order['delivery_date_start'])), // 出荷予定日
                '',
                '',
                '',
                $order['buyer_tel'], // お届け先電話番号
                '',
                $order['buyer_zip'], // お届け先郵便番号
                $order['buyer_adress'], // お届け先住所
                $order['buyer_building'], // お届け先アパートマンション名
                '',
                '',
                $order['buyer_name'], // お届け先名
                '',
                '様', // 敬称
                '',
                '03-0000-0000', // ご依頼主電話番号
                '',
                '1430006', // ご依頼主郵便番号
                '東京都大田区平和島3-4-1', // ご依頼主住所
                '東京団地倉庫　管理棟402', // ご依頼主アパートマンション
                '【 &flower 】カスタマーセンター', // ご依頼主名
                '',
                '',
                'フラワーギフト《生花》', // 品名１
                '',
                '',
                '',
                '',
                $order['serial_no'],
                '',
                '',
                '',
                '',
                '',
                '',
                '035348580901', // 請求先顧客コード
                '',
                '01', // 運賃管理番号
            ];
        }else{
            //宅配
            return [
                $order['id'], // お客様管理番号
                '0', // 送り状種類
                '0', // クール区分
                '',
                date('Y/m/d', strtotime($order['delivery_date_start'])), // 出荷予定日
                '',
                '',
                '',
                $order['buyer_tel'], // お届け先電話番号
                '',
                $order['buyer_zip'], // お届け先郵便番号
                $order['buyer_adress'], // お届け先住所
                $order['buyer_building'], // お届け先アパートマンション名
                '',
                '',
                $order['buyer_name'], // お届け先名
                '',
                '様', // 敬称
                '',
                '03-0000-0000', // ご依頼主電話番号
                '',
                '1430006', // ご依頼主郵便番号
                '東京都大田区平和島3-4-1', // ご依頼主住所
                '東京団地倉庫　管理棟402', // ご依頼主アパートマンション
                '【 &flower 】カスタマーセンター', // ご依頼主名
                '',
                '',
                'フラワーギフト《生花》', // 品名１
                '',
                '',
                '',
                '',
                $order['serial_no'],
                '',
                '',
                '',
                '',
                '',
                '',
                '035348580901', // 請求先顧客コード
                '',
                '01', // 運賃管理番号
            ];
        }
    }

    private function __getSubOrderCsvShopRow($order,$shop, $status) {

        if ($order['order_details'][0]['plan']['bill_status'] == "1"){
            return [
                $order['id'], // お客様管理番号
                '7', // 送り状種類
                '0', // クール区分
                '',
                date('Y/m/d', strtotime($order['delivery_date_start'])), // 出荷予定日
                '',
                '',
                '',
                $order['buyer_tel'], // お届け先電話番号
                '',
                $order['buyer_zip'], // お届け先郵便番号
                $order['buyer_adress'], // お届け先住所
                $order['buyer_building'], // お届け先アパートマンション名
                '',
                '',
                $order['buyer_name'], // お届け先名
                '',
                '様', // 敬称
                '',
                $shop['tel'], // ご依頼主電話番号
                '',
                $shop['zip'], // ご依頼主郵便番号
                $shop['prefecture']['prefecture_name'] . $shop['address'], // ご依頼主住所
                $shop['building'], // ご依頼主アパートマンション
                $shop['shop_name'], // ご依頼主名
                '',
                '',
                'フラワーギフト《生花》', // 品名１
                '',
                '',
                '',
                '',
                $order['serial_no'],
                '',
                '',
                '',
                '',
                '',
                '',
                '035348580901', // 請求先顧客コード
                '',
                '01', // 運賃管理番号
            ];
        }else{
            return [
                $order['id'], // お客様管理番号
                '0', // 送り状種類
                '0', // クール区分
                '',
                date('Y/m/d', strtotime($order['delivery_date_start'])), // 出荷予定日
                '',
                '',
                '',
                $order['buyer_tel'], // お届け先電話番号
                '',
                $order['buyer_zip'], // お届け先郵便番号
                $order['buyer_adress'], // お届け先住所
                $order['buyer_building'], // お届け先アパートマンション名
                '',
                '',
                $order['buyer_name'], // お届け先名
                '',
                '様', // 敬称
                '',
                $shop['tel'], // ご依頼主電話番号
                '',
                $shop['zip'], // ご依頼主郵便番号
                $shop['prefecture']['prefecture_name'] . $shop['address'], // ご依頼主住所
                $shop['building'], // ご依頼主アパートマンション
                $shop['shop_name'], // ご依頼主名
                '',
                '',
                'フラワーギフト《生花》', // 品名１
                '',
                '',
                '',
                '',
                $order['serial_no'],
                '',
                '',
                '',
                '',
                '',
                '',
                '035348580901', // 請求先顧客コード
                '',
                '01', // 運賃管理番号
            ];
        }


    }

    // ステータスの変更を行う（注文一覧）
    // 決済完了の場合は出荷済に　出荷済の場合は決済完了に戻す
    public function updStatus($data){

        //決済完了　or 再発行の場合は出荷済に　出荷済の場合は決済完了　or 再発行に戻す
        switch($data['status']){
            case 4:
                $status = "7";
                break;
            case 7:
                $status = "4";
                break;
            case 8:
                $status = "9";
                break;
            case 9:
                $status = "8";
        }
        return $this->updateItem(['id'=>$data['id']], ['status' =>$status]);
    }


}
