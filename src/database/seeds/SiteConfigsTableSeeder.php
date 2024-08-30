<?php

use Illuminate\Database\Seeder;

class SiteConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('site_configs')->insert([
            [
                'id' => '1',
                'key_name' => 'info_day',
                'value' => '3',
                'description' => '案内メールの送信日(受注確定日のn日前)',
            ],
            [
                'id' => '2',
                'key_name' => 'main_shop_id',
                'value' => '1',
                'description' => 'オプションアイテム販売・サブスク配送に使用されるメインの店舗ID',
            ],
        ]);
    }
}
