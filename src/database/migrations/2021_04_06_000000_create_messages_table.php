<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('group_id')->nullable()->comment('グループID');
            $table->bigInteger('user_from_id')->nullable()->comment('送信者');
            $table->bigInteger('user_to_id')->nullable()->comment('受信者');
            $table->text('message')->nullable()->comment('メッセージ');
            $table->date('confirm_date')->nullable()->comment('確認日');
            $table->tinyInteger('open_flg')->default(1)->comment('0:非表示 1:表示 ');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);

        });

		DB::statement("ALTER TABLE messages COMMENT 'メッセージ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
