<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            $table->string('title', 64)->nullable()->comment('タイトル');
			$table->string('subject', 64)->nullable()->comment('件名');
            $table->text('mail_text')->nullable()->comment('本文');

            $table->tinyInteger('mail_flg')->default(1)->comment('1:配信する　0:配信しない');
            $table->tinyInteger('news_flg')->default(1)->comment('1:配信する　0:配信しない');
            $table->string('day',4)->nullable()->comment('日');
            $table->tinyInteger('hour')->nullable()->comment('時間');

            $table->tinyInteger('status')->default(1)->comment('1:トランザクションメール　2:イベントメール 3:メールマガジン');

            $table->bigInteger('update_id')->nullable()->comment('更新者ID');

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
        Schema::dropIfExists('mail_templates');
    }
}
