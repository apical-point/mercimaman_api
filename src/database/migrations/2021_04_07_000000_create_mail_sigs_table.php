<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailSigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_sigs', function (Blueprint $table) {

            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            $table->text('sig')->nullable()->comment('署名');

            $table->tinyInteger('admin_type')->nullable()->comment('不要　管理者種別');
            $table->bigInteger('update_id')->nullable()->comment('不要　更新者ID');

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
        Schema::dropIfExists('mail_sigs');
    }
}
