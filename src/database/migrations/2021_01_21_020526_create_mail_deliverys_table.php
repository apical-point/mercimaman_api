<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaildeliverysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_deliverys', function (Blueprint $table) {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            $table->tinyInteger('status')->nullable()->comment('0:未送信 1:送信済 2 :送信中 9:エラー');
            $table->tinyInteger('target_flg')->nullable()->comment('送信タイプ 1:管理者 2:会員');
            $table->tinyInteger('mail_type')->nullable()->comment('メールの種別');

			// データ
            $table->bigInteger('company_id')->nullable()->comment('送信先会社コード');
			$table->bigInteger('belong_code')->nullable()->comment('送信先所属コード');

            $table->string('subject', 128)->nullable()->comment('件名');
            $table->text('template')->nullable()->comment('本文');
            $table->string('senderId', 3)->nullable()->comment('送信者ID');

            $table->bigInteger('all_cnt')->nullable()->comment('総数');
            $table->bigInteger('success')->nullable()->comment('成功数');
            $table->bigInteger('failure')->nullable()->comment('失敗数');
            $table->tinyInteger('reserve_houre')->nullable()->comment('送信時間');

            $table->bigInteger('messageID')->nullable()->comment('メセージID');
            $table->bigInteger('reason')->nullable()->comment('理由');

            $table->bigInteger('send_id')->nullable()->comment('送信者ID');
            $table->bigInteger('send_code')->nullable()->comment('送信者所属コード');
			$table->dateTime('maildelivery_date')->nullable()->comment('配信日付');

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
