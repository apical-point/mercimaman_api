<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondBannerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('second_banner', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('detail')->nullable()->comment('詳細');
            $table->text('name')->nullable();
            $table->text('url')->nullable();
            $table->tinyInteger('eventpage_flg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('second_banner');
    }
}
