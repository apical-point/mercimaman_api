<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('email',100)->nullable()->comment('EMAIL');
            $table->string('name',100)->nullable()->comment('名前');
            $table->string('title',100)->nullable()->comment('件名');
            $table->text('detail')->nullable()->comment('詳細');

            $table->text('reply_mail_text')->nullable()->comment('返信内容');
            $table->date('reply_mail_date')->nullable()->comment('返信日付');
            $table->tinyInteger('inquiry_flg')->nullable()->comment('1：ご意見ご要望　2：問い合わせ　3：広告出稿');
            $table->tinyInteger('demand_flg')->nullable()->comment('1:未返信 2:返信済');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);

        });

        DB::statement("ALTER TABLE inquiries COMMENT '問合せ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inquiries');
    }
}
