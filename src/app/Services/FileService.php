<?php namespace App\Services;

// ulid
use \Ulid;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Exception;

use Illuminate\Support\Facades\Storage;

class FileService extends Bases\BaseService
{
    private static $resizeWidth;
    private static $resizeHeight;

    public function __construct(
    ) {
        parent::__construct();

        self::$resizeWidth = config('const.image.resize.IMAGE_RESIZE_WIDTH_SIZE');
        self::$resizeHeight = config('const.image.resize.IMAGE_RESIZE_HEIGHT_SIZE');
    }

    // 1枚の画像をリサイズして保存する
    // 配列で取得したものを保存して名前を返す。
    // 引数はUploadedFileオブジェクト
    public function saveResizeImage($upLoadFile, $saveDir)
    {
        // 拡張子が良ければ
        if(!in_array(File::extension($upLoadFile->getClientOriginalName()), config('const.IMAGE_EXTENSION'))) return false;

        // まず保存
        $newImagePath = storage_path('app/'.$upLoadFile->store($saveDir));

        // リサイズ
        $img = Image::make($newImagePath);

        // アップロードされた画像について縦長化か、横長かで分ける
        // 横長
//        if($img->width()>=$img->height()) {
//            $img->resize(self::$resizeWidth, null, function($constraint){
//                $constraint->aspectRatio();
//            })->save();

        // 縦長
//        } else {
            $img->resize(null, self::$resizeHeight, function($constraint){
                $constraint->aspectRatio();
            })->save();

//        }

        return $newImagePath;
    }

    // 複数の画像をリサイズして保存する
    public function saveResizeImages($upLoadFiles, $saveDir)
    {
        $results = [];
        foreach ($upLoadFiles as $upLoadFile) $results[] = $this->saveResizeImage($upLoadFile, $saveDir);

        return $results;
    }

    // ファイルの保存
    // 引数はUploadedFileオブジェクト
    public function saveFile($upLoadFile, $saveDir)
    {
        return storage_path('app/'.$upLoadFile->store($saveDir));
    }

    // 複数のファイルの保存
    // 引数はUploadedFileオブジェクト
    public function saveFiles($upLoadFiles, $saveDir)
    {
        $results = [];
        foreach ($upLoadFiles as $upLoadFile) $results[] = $this->saveFile($upLoadFile, $saveDir);

        return $results;
    }

    public function deletefilesByPaths(array $paths=[])
    {
        if(empty($paths)) return false;

        try {
            File::delete($paths);

            return true;

        } catch (Exception $e){
            return false;
        }

    }

    // リサイズを行う
    public function resizeImage($imagePath, $resizeWidth, $resizeHeight)
    {
        try {
            // イメージ
            $img = Image::make($imagePath);

            // 横長
            if($img->width()>=$img->height()) {
                $img->resize($resizeWidth, null, function($constraint){
                    $constraint->aspectRatio();
                })->save();

            // 縦長
            } else {
                $img->resize(null, $resizeHeight, function($constraint){
                    $constraint->aspectRatio();
                })->save();

            }

            return true;
        } catch (Exception $e){
            return false;
        }
    }

    // base64の画像を保存する
    public function saveImageByBase64($base64Data, $saveDir)
    {
        // ファイルに変換
        $fileData = base64_decode($base64Data);

        // 保存名
    //    $savePath = $saveDir.uniqid(date("YmdHis")).'.jpg';
        $savePath = $saveDir.uniqid(date("YmdHis")).'.png';

        // 保存
       $r = Storage::put($savePath, (string) $fileData);

        // リサイズ
    //    $img = Image::make(storage_path().'/app/'.$savePath);

    //    $img->resize(self::$resizeWidth,self::$resizeWidth, function($constraint){
    //    $constraint->aspectRatio();
    //                })->save();

        // フルパスを返す
        return storage_path().'/app/'.$savePath;
    }

    // base64の画像を保存する
    public function saveImagesByBase64($base64Data, $saveDir)
    {
        // 結果配列
        $savePaths = [];

        foreach ($base64Data as $row){
            if ($row != ""){
                $savePaths[] = $this->saveImageByBase64($row, $saveDir);
            }
        }

        // フルパスを返す
        return $savePaths;
    }

    //画像の削除
    public function deleteImage($savepath){
        Storage::delete($savepath);
    }

    //画像データをストレージに保存し、パスを返す関数
    public function saveBannerImageToStorage($encodedBanner, $fileName) {
        $binaryBannerData = base64_decode($encodedBanner);
        $storageDisk = Storage::disk('public');
        $storageDisk->put($fileName, $binaryBannerData);
        $path = asset('storage/'. $fileName, true); //FIXMEKOKI

        return $path;
    }


}
