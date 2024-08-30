<?php

use Illuminate\Database\Seeder;

class AdminersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('adminers')->insert([
            [
                'id' => '1',
                'email' => 'adminer@apice-tec.co.jp',
                'password' => '$2y$10$KT38ZC.gbghNQrXg5m6Dh.1csntk4sbJMnl.VBQ/gSP99SywNTSiu',
                'name' => 'Adminer',
            ],
        ]);
    }
}
