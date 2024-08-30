<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicitySurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publicity_survey', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('themedate')->nullable()->comment('掲載日');
            $table->string('theme',40)->nullable()->comment('今週のテーマ');
            $table->integer('yesCnt')->default(0)->comment('「知ってる」と答えた件数');
            $table->integer('noCnt')->default(0)->comment('「知らない」と答えた件数');
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
        Schema::dropIfExists('publicity_survey');
    }
}
