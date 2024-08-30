<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adminers', function (Blueprint $table) {
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

            // 認証
			$table->string('email', 256)->comment('メアド');
			$table->string('password', 100)->nullable()->comment('パスワード');

            // カラム
			$table->string('name', 256)->comment('名前');

            // ステータス
			$table->tinyInteger('status')->default(0)->comment('ステータス');

            // 権限
			$table->tinyInteger('admin_type')->default(0)->comment('権限');

            // ログイントークン
            $table->rememberToken();

            // 時刻データ
            $table->timestamps();

            // 倫理削除
            $table->softDeletes();

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
        Schema::dropIfExists('adminers');
    }
}
