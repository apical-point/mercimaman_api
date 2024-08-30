<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaildeliveryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_delivery_details', function (Blueprint $table) {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // データ
            $table->bigInteger('maildelivery_id')->nullable()->comment('メール配信ID');
            $table->tinyInteger('status')->nullable()->comment('0:未送信 1:送信済 9:エラー');

            $table->bigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->string('email', 256)->nullable()->comment('メールアドレス');
            $table->string('name', 40)->nullable()->comment('名前');
            $table->string('errno', 20)->nullable()->comment('エラー番号');
            $table->text('reason')->nullable()->comment('理由');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_deliverys');
    }
}
