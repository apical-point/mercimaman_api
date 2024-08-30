<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(AreasTableSeeder::class);
        // $this->call(PrefecturesTableSeeder::class);
        // $this->call(PlansTableSeeder::class);
        // $this->call(AdminersTableSeeder::class);
        // $this->call(ShopsTableSeeder::class);
        // $this->call(SiteConfigsTableSeeder::class);
        $this->call(SecondBannerTableSeeder::class);
    }
}
