<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title',100)->nullable()->comment('件名');
            $table->text('detail')->nullable()->comment('詳細');
            $table->text('status')->nullable()->comment('ステータス 1:未読 2:既読');

            $table->tinyInteger('news_flg')->nullable()->comment('1:個人　2:全員');
            $table->date('open_date')->nullable()->comment('公開日');
            $table->tinyInteger('open_flg')->default(0)->comment('0:非公開 1:公開');
            $table->bigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->bigInteger('public_id')->nullable()->comment('公開ID');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);

        });

        DB::statement("ALTER TABLE faqs COMMENT 'メッセージ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
