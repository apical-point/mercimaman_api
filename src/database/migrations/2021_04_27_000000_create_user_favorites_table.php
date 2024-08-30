<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateUserFavoritesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_favorites', function(Blueprint $table)
        {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // 会員情報
            $table->bigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->tinyInteger('type')->nullable()->comment('1:気になる 2:フォロー');

            $table->bigInteger('product_id')->nullable()->comment('お気に入り');
            $table->bigInteger('follow_id')->nullable()->comment('フォローID');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
        });

        DB::statement("ALTER TABLE user_favorites COMMENT 'ユーザーお気に入り'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_favorites');
    }

}
