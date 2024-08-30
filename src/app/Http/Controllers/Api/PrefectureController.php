<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

// サービス
use App\Services\PrefectureService;

class PrefectureController extends Bases\ApiBaseController
{
    // サービス
    protected $prefectureService;

    public function __construct(
        // サービス
        PrefectureService $prefectureService
    ){
        parent::__construct();

        // サービス
        $this->prefectureService = $prefectureService;
    }

    // 一覧データ取得
    public function index(Request $request)
    {
        // データ定義
        $inputData = $request->all();

        // 取得
        $prefectureRows = $this->prefectureService->getList($inputData);

        // 返す
        return $this->sendResponse($prefectureRows);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
