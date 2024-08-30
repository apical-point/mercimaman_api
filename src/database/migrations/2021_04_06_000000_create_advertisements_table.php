<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            $table->string('advertisement_name',100)->nullable()->comment('商品名');
            $table->string('detail',512)->nullable()->comment('商品説明');
            $table->string('company',40)->nullable()->comment('会社名');
            $table->string('url',100)->nullable()->comment('会社名URL');
            $table->string('term',7)->nullable()->comment('詳細');
            $table->tinyInteger('open_flg')->default(0)->comment('0:非公開 1:公開');

            $table->text('script')->nullable()->comment('スクリプト');

            $table->tinyInteger('type')->nullable()->comment('1:広告 2:ディスプレイ広告');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);

        });

        DB::statement("ALTER TABLE advertisements COMMENT '問合せ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisements');
    }
}
