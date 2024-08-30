<?php namespace App\Services\Bases;

abstract class BaseService
{
    public function __construct(

    ) {
        // parent::__construct();
    }

    // curlの設定
    public function curl() {
        $curl = new \Curl\Curl();

        // リダイレクト先のものを取得する。3回までok
        // $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        // $curl->setOpt(CURLOPT_MAXREDIRS, 3);

        // パスポート
        // $curl->setHeader("Accept", "application/json");
        // $curl->setHeader("Authorization", "Bearer ".$token);

        return $curl;
    }

    // curlでの返り値の整形
    public function resultApi($curl): array {
        $c = $curl->http_status_code;
        if($c==404 || $c==403 || $c==405 ) {
            return ['success'=>false, 'http_status_code'=>$c];
        }

        // http_response_code(404);

        $response = json_decode($curl->response, true);
        return ['success'=>$response['success'], 'data'=>$response['data'], 'message'=>$response['message'], 'http_status_code'=>$c];
    }

    // // 1件取得-抽象関
    // abstract protected function getItem($where);

    // // 1件更新-抽象関
    // abstract protected function updateItem($where, $data);

    // // 1件削除-抽象関数
    // abstract protected function deleteItem($where);


    // --------------------------- id ---------------------------
    // // idで取得
    // public function getItemById($id)
    // {
    //     return $this->getItem(['id'=>$id]);
    // }

    // // idで更新
    // public function updateItemById($id, $data)
    // {
    //     return $this->updateItem(['id'=>$id], $data);
    // }

    // // idで削除
    // public function deleteItemById($id)
    // {
    //     return $this->deleteItem(['id'=>$id]);
    // }


}
