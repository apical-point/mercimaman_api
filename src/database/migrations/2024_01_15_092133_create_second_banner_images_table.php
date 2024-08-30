<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondBannerImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('second_banner_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('second_banner_id');
            $table->string('image_url');
            $table->string('name')->nullable();
            $table->tinyInteger('type');

            $table->timestamps();

            $table->foreign('second_banner_id')->references('id')->on('second_banner')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('second_banner_images');
    }
}
