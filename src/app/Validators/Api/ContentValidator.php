<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;

// サービス
use App\Services\ContentService;

class ContentValidator extends BaseValidator {
    // サービス
    protected $contentService;

    public function __construct(
         ContentService $contentService
    ){
        parent::__construct();

        // サービス
        $this->contentService = $contentService;
    }

    // コンテンツ登録
    public function store($inputData) {

        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$content);
        if ($v->fails()){
            $val = $v->errors()->toArray();
            return $val;
        }

        // 同じ日付の登録があるかチェック
        $content = $this->contentService->getItem(['themedate' => $inputData['themedate']]);
        if(!$content){
            return $val;
        }else{
            $val['themedate'] = __('messages.content_date_error');
        }

        return $val;
    }

    // コンテンツメッセージの登録
    public function messageStore($inputData) {

        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$contentMessageStore);
        if ($v->fails()){
            $val = $v->errors()->toArray();
            return $val;
        }

        return $val;
    }

    // テーマ募集の登録
    public function offerStore($inputData) {

        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$contentOfferStore);
        if ($v->fails()){
            $val = $v->errors()->toArray();
            return $val;
        }

        return $val;
    }
}
