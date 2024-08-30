<?php

use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plans')->insert([
            ['id' => '1', 'plan_name' => '&flowerプラン', 'detail' => '(お花とグリーン4〜5本)', 'price' => '800', 'system_charge' => '0', 'delivery_charge' => '350'],
        ]);
    }
}
