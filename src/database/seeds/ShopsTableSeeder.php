<?php

use Illuminate\Database\Seeder;

class ShopsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shops')->insert([
            [
                'id' => '1',
                'shop_name' => 'テスト店舗',
                'prefecture_id' => '10',
                'zip' => '251-0023',
                'address' => '横浜市泉区和泉中央南',
                'building' => 'スカイビル１０２２',
                'tel' => '04511112222',
                'email' => 'test1@test.com',
            ],
        ]);
    }
}
