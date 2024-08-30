<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('content_id')->nullable()->comment('コンテンツID');
            $table->tinyInteger('type')->default(1)->comment('タイプ 1:今週のテーマ 2:二択 3:プレゼント');
            $table->bigInteger('user_id')->nullable()->comment('送信者');
            $table->text('message')->nullable()->comment('メッセージ');
            $table->tinyInteger('open_flg')->default(1)->comment('0:非表示 1:表示 ');
            $table->tinyInteger('election_flg')->default(0)->comment('0:はずれ 1:当選 ');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);

        });

        DB::statement("ALTER TABLE content_messages COMMENT 'コンテンツメッセージ'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_messages');
    }
}
