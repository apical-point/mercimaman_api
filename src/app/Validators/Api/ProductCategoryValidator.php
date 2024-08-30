<?php namespace App\Validators\Api;

// ベース
use App\Validators\Api\Bases\BaseValidator;

// バリデートの配列
use App\Libraries\ValidateCheckArray;

// バリデート
use Illuminate\Support\Facades\Validator;

// サービス
use App\Services\ProductCategoryService;


class ProductCategoryValidator extends BaseValidator
{
    // サービス
    protected $productCategoryService;

    public function __construct(

        // サービス
        ProductCategoryService $productCategoryService
    ){
        parent::__construct();

        // サービス
        $this->productCategoryService = $productCategoryService;

    }

    public function store($inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$createCategory);
        if ($v->fails()) $val = $v->errors()->toArray();

        // 親があるかどうか
        if(!empty($inputData['parentid']) && !$this->productCategoryService->isParent($inputData['parentid'])) return $this->sendErrorResponse([], __('messages.not_found_product_parent_category'), 404);

        return $val;
    }

    public function update($id, $inputData)
    {
        // エラー配列の定義
        $val = [];

        // 入力のバリデート--共通の項目
        $v = Validator::make($inputData, ValidateCheckArray::$updateCategory);
        if ($v->fails()) $val = $v->errors()->toArray();


        return $val;
    }



}
