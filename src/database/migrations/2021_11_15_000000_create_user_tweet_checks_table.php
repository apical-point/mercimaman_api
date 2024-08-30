<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateUserTweetChecksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_tweet_checks', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

            $table->bigInteger('user_id')->nullable()->comment('会員ID');
            $table->bigInteger('tweet_id')->nullable()->comment('ツイートID');
            $table->bigInteger('check')->nullable()->comment('1:いいね 2:えらい 3:わかる');



            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE board_experience_users COMMENT 'ツイート 会員ボタン押下記録'");
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
