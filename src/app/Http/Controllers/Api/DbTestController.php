<?php namespace App\Http\Controllers\Api;

// 使用するもの--使用頻度が高いので、基本は読み込んでおいたほうがいい。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

// サービス
use App\Services\ContentService;
use App\Services\ContentMessageService;
use App\Services\ContentOfferService;
use App\Services\FileService;
use App\Services\UpFileService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\PointService;
use App\Services\SiteConfigService;
use App\Services\NgWordsService;

// バリデートの配列
use App\Libraries\ValidateCheckArray;
use App\Validators\Api\ContentValidator;

class DbTestController extends Bases\ApiBaseController
{

    // 画像の保存先--公開フォルダ
    private static $saveFileDir = 'public/content/';
    private static $saveFileUrl = 'content/';

    // サービス
    protected $contentService;
    protected $contentMessageService;
    protected $contentOfferService;
    protected $fileService;
    protected $upFileService;
    protected $userService;
    protected $mailService;
    protected $pointService;
    protected $siteConfigService;
    protected $ngWordsService;

    // バリデート
    protected $ContentValidator;

    public function __construct(
        // サービス
        ContentService $contentService,
        ContentMessageService $contentMessageService,
        ContentOfferService $contentOfferService,
        FileService $fileService,
        UserService $userService,
        UpFileService $upFileService,
        MailService $mailService,
        PointService $pointService,
        SiteConfigService $siteConfigService,
        NgWordsService $ngWordsService,

        // バリデート
        ContentValidator $ContentValidator
    ){
        parent::__construct();

        // サービス
        $this->contentService = $contentService;
        $this->contentMessageService = $contentMessageService;
        $this->contentOfferService = $contentOfferService;
        $this->fileService = $fileService;
        $this->userService = $userService;
        $this->upFileService = $upFileService;
        $this->mailService = $mailService;
        $this->pointService = $pointService;
        $this->siteConfigService = $siteConfigService;
        $this->ngWordsService = $ngWordsService;

        // バリデート
        $this->ContentValidator = $ContentValidator;

    }

    //一覧取得
    public function index(Request $request)
    {
        // *-*-*-*-*-*-*-*-*-*-*- アクセスチェック *-*-*-*-*-*-*-*-*-*-*-
        // キーのチェック
        $this->requestKeyCheck($request);

        // *-*-*-*-*-*-*-*-*-*-*- データの定義 *-*-*-*-*-*-*-*-*-*-*-
        // 入力データの取得

        return "これでだいぶ楽になる";
    }

}
