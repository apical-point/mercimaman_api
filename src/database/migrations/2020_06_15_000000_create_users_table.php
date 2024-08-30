<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // 認証
            $table->string('email', 300)->comment('メールアドレス');
            $table->string('password', 10000)->nullable()->comment('パスワード');

            $table->tinyInteger('status')->nullable()->comment('ステータス 0:仮登録/1:本登録/9:退会');

            $table->dateTime('regist_limit')->nullable()->comment('登録/パスワードリセットの有効期限');
            $table->string('param', 128)->nullable()->comment('登録/パスワードリセットのパラメーター');
            $table->string('temporary_email', 256)->nullable()->comment('変更用の仮メールアドレス');

            // ログイントークン
            $table->rememberToken();

            // 時刻データ
            $table->timestamps();

            // 倫理削除
            $table->softDeletes();            

            // インデックス
            $table->index(['id']);
        });

        DB::statement("ALTER TABLE users COMMENT 'ユーザー'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
