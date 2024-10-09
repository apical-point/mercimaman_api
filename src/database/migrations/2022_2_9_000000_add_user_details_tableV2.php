<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class AddUserDetailsTableV2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

	    Schema::table('user_details', function (Blueprint $table) {


	        $table->tinyInteger('mail_flg')->after("device_id")->default(1)->comment('1:メール送信:0:メール停止');


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
