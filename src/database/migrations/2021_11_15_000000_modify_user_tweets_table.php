<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class ModifyUserTweetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

	    Schema::table('user_tweets', function (Blueprint $table) {

	        $table->tinyInteger('tweet_flg')->after("tweet")->default(1)->comment('1:ツイート　2:コメント');
	        $table->smallInteger('check1')->after("tweet_flg")->default(0)->comment('いいねの数');
	        $table->smallInteger('check2')->after("check1")->default(0)->comment('えらいの数');
	        $table->smallInteger('check3')->after("check2")->default(0)->comment('わかるの数');


	    });
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
