<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateTarotUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tarot_users', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			$table->bigInteger('tarot_id')->nullable()->comment('該当のタロット占いのID');
			$table->bigInteger('user_id')->nullable()->comment('占ったユーザーID');


            $table->tinyInteger('card')->nullable()->comment('占い結果のカード番号');
            $table->tinyInteger('card_fb')->nullable()->comment('占い結果のカード　1:正 2:逆');
            $table->string('card_result', 255)->nullable()->comment('占い結果');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE tarot_users COMMENT 'タロット占い ユーザーの占い結果を登録'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */

	public function down()
	{

	}

}
