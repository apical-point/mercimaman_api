<?php

use Illuminate\Database\Seeder;

class SecondBannerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // sampleバナー
        DB::table('second_banner')->insert([
            [
                'id' => '1',
                'name' => 'サンプル',
                'detail' => 'サンプルデータになります。入力項目をご確認の上、更新ボタンを押下してください。',
                'url' => 'sample.com',
                'eventpage_flg' => '1',
            ],
        ]);
    }
}
