<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('category_id')->nullable()->comment('タイプ');
            $table->string('title',100)->nullable()->comment('件名');
            $table->text('detail')->nullable()->comment('詳細');

            $table->tinyInteger('sort')->nullable()->comment('順番');

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
        Schema::dropIfExists('faqs');
    }
}
