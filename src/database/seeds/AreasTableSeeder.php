<?php

use Illuminate\Database\Seeder;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('areas')->insert([
            [
                'id' => '1',
                'area_name' => '北海道',
            ],
            [
                'id' => '2',
                'area_name' => '東北',
            ],
            [
                'id' => '3',
                'area_name' => '北関東',
            ],
            [
                'id' => '4',
                'area_name' => '南関東',
            ],
            [
                'id' => '5',
                'area_name' => '北陸',
            ],
            [
                'id' => '6',
                'area_name' => '東海',
            ],
            [
                'id' => '7',
                'area_name' => '関西',
            ],
            [
                'id' => '8',
                'area_name' => '中国',
            ],
            [
                'id' => '9',
                'area_name' => '四国',
            ],
            [
                'id' => '10',
                'area_name' => '九州',
            ],
            [
                'id' => '11',
                'area_name' => '沖縄',
            ],
        ]);
    }
}
