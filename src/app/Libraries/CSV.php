<?php

namespace App\Library;

// use \Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;

class CSV
{
    public function __construct()
    {
    }
    /**
     * CSVダウンロード
     * @param array $list csvデータ
     * @param array $header csvヘッダ
     * @param string $filename ダウンロードファイル名
     * @return \Illuminate\Http\Response
     */
    public static function download($list, $header, $filename)
    {
        if (count($header) > 0) {
            array_unshift($list, $header);
        }
        $stream = fopen('php://temp', 'r+b');
        foreach ($list as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        );
        return Response::make($csv, 200, $headers);
    }

    /**
     *
     * ファイル出力
     * @param array $list データ
     * @param array $header ヘッダ
     * @param string $filePath 出力ファイルパス
     */
    public static function outputFile($list, $header, $filePath)
    {
        $file = fopen($filePath, 'w');

        mb_convert_variables('SJIS-win', 'UTF-8', $header);

        fputcsv($file, $header);

        foreach ($list as $item) {

            mb_convert_variables('SJIS-win', 'UTF-8', $item);

            fputcsv($file, $item);
        }
        fclose($file);
    }

}

