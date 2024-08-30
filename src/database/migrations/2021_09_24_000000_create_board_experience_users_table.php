<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateBoardExperienceUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('board_experience_users', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

            $table->bigInteger('member_id')->nullable()->comment('会員ID');
            $table->bigInteger('experience_id')->nullable()->comment('体験記ID');



            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE board_experience_users COMMENT '体験記掲示板 会員ボタン押下記録'");
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
