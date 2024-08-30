<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {

          $table->bigIncrements('id');

          $table->date('themedate')->nullable()->comment('今週テーマ掲載日');
          $table->string('theme',40)->nullable()->comment('今週のテーマ');

          $table->date('choicesdate')->nullable()->comment('二択テーマ掲載日');
          $table->string('choicestheme',40)->nullable()->comment('今週のテーマ');
          $table->string('answer1',40)->nullable()->comment('回答1');
          $table->string('answer2',40)->nullable()->comment('回答2');
          $table->integer('answerCnt1')->default(0)->comment('回答件数1');
          $table->integer('answerCnt2')->default(0)->comment('回答件数2');

          $table->date('presentdate')->nullable()->comment('プレゼント掲載日');
          $table->string('present',40)->nullable()->comment('商品名');
          $table->string('presentdetail',512)->nullable()->comment('商品説明');
          $table->tinyInteger('election')->nullable()->comment('当選人数');
          $table->string('company_name',40)->nullable()->comment('会社名');
          $table->string('company_url',256)->nullable()->comment('会社URL');
          $table->tinyInteger('election_flg')->default(0)->comment('抽選結果登録');

          // 時刻データ
          $table->timestamps();

          // インデックス
          $table->index(['id']);

        });

        DB::statement("ALTER TABLE contents COMMENT 'コンテンツ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
}
