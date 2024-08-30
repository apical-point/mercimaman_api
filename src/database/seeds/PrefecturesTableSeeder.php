<?php

use Illuminate\Database\Seeder;

class PrefecturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('prefectures')->insert([
            ['id' => '1', 'area_id' => '1', 'prefecture_name' => '北海道'],
            ['id' => '2', 'area_id' => '2', 'prefecture_name' => '青森県'],
            ['id' => '3', 'area_id' => '2', 'prefecture_name' => '岩手県'],
            ['id' => '4', 'area_id' => '2', 'prefecture_name' => '宮城県'],
            ['id' => '5', 'area_id' => '2', 'prefecture_name' => '秋田県'],
            ['id' => '6', 'area_id' => '2', 'prefecture_name' => '山形県'],
            ['id' => '7', 'area_id' => '2', 'prefecture_name' => '福島県'],
            ['id' => '8', 'area_id' => '3', 'prefecture_name' => '茨城県'],
            ['id' => '9', 'area_id' => '3', 'prefecture_name' => '栃木県'],
            ['id' => '10', 'area_id' => '3', 'prefecture_name' => '群馬県'],
            ['id' => '11', 'area_id' => '3', 'prefecture_name' => '埼玉県'],
            ['id' => '12', 'area_id' => '4', 'prefecture_name' => '千葉県'],
            ['id' => '13', 'area_id' => '4', 'prefecture_name' => '東京都'],
            ['id' => '14', 'area_id' => '4', 'prefecture_name' => '神奈川県'],
            ['id' => '15', 'area_id' => '5', 'prefecture_name' => '新潟県'],
            ['id' => '16', 'area_id' => '5', 'prefecture_name' => '富山県'],
            ['id' => '17', 'area_id' => '5', 'prefecture_name' => '石川県'],
            ['id' => '18', 'area_id' => '5', 'prefecture_name' => '福井県'],
            ['id' => '19', 'area_id' => '4', 'prefecture_name' => '山梨県'],
            ['id' => '20', 'area_id' => '5', 'prefecture_name' => '長野県'],
            ['id' => '21', 'area_id' => '6', 'prefecture_name' => '岐阜県'],
            ['id' => '22', 'area_id' => '6', 'prefecture_name' => '静岡県'],
            ['id' => '23', 'area_id' => '6', 'prefecture_name' => '愛知県'],
            ['id' => '24', 'area_id' => '6', 'prefecture_name' => '三重県'],
            ['id' => '25', 'area_id' => '7', 'prefecture_name' => '滋賀県'],
            ['id' => '26', 'area_id' => '7', 'prefecture_name' => '京都府'],
            ['id' => '27', 'area_id' => '7', 'prefecture_name' => '大阪府'],
            ['id' => '28', 'area_id' => '7', 'prefecture_name' => '兵庫県'],
            ['id' => '29', 'area_id' => '7', 'prefecture_name' => '奈良県'],
            ['id' => '30', 'area_id' => '7', 'prefecture_name' => '和歌山県'],
            ['id' => '31', 'area_id' => '8', 'prefecture_name' => '鳥取県'],
            ['id' => '32', 'area_id' => '8', 'prefecture_name' => '島根県'],
            ['id' => '33', 'area_id' => '8', 'prefecture_name' => '岡山県'],
            ['id' => '34', 'area_id' => '8', 'prefecture_name' => '広島県'],
            ['id' => '35', 'area_id' => '8', 'prefecture_name' => '山口県'],
            ['id' => '36', 'area_id' => '9', 'prefecture_name' => '徳島県'],
            ['id' => '37', 'area_id' => '9', 'prefecture_name' => '香川県'],
            ['id' => '38', 'area_id' => '9', 'prefecture_name' => '愛媛県'],
            ['id' => '39', 'area_id' => '9', 'prefecture_name' => '高知県'],
            ['id' => '40', 'area_id' => '10', 'prefecture_name' => '福岡県'],
            ['id' => '41', 'area_id' => '10', 'prefecture_name' => '佐賀県'],
            ['id' => '42', 'area_id' => '10', 'prefecture_name' => '長崎県'],
            ['id' => '43', 'area_id' => '10', 'prefecture_name' => '熊本県'],
            ['id' => '44', 'area_id' => '10', 'prefecture_name' => '大分県'],
            ['id' => '45', 'area_id' => '10', 'prefecture_name' => '宮崎県'],
            ['id' => '46', 'area_id' => '10', 'prefecture_name' => '鹿児島県'],
            ['id' => '47', 'area_id' => '11', 'prefecture_name' => '沖縄県'],
        ]);
    }
}
