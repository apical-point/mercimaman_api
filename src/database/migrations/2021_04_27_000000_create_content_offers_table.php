<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('content_id')->nullable()->comment('コンテンツID');
            $table->tinyInteger('type')->default(1)->comment('タイプ 1:今週のテーマ 2:二択 ');
            $table->bigInteger('user_id')->nullable()->comment('送信者');
            $table->text('theme')->nullable()->comment('テーマ');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);

        });

        DB::statement("ALTER TABLE content_offers COMMENT 'コンテンツ募集'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_offers');
    }
}
