<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateBoardExperiencesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('board_experiences', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');


            // 販売への評価
			$table->text('title')->nullable()->comment('タイトル');
			$table->text('detail')->nullable()->comment('内容');
            $table->bigInteger('member_id')->nullable()->comment('会員ID');
            $table->tinyInteger('exp_flg')->nullable()->comment('1:要望　2:体験 3:雑談');
            $table->smallInteger('check1')->nullable()->comment('いいねの数');
            $table->smallInteger('check2')->nullable()->comment('参考になったの数');
            $table->smallInteger('check3')->nullable()->comment('応援してるの数');
            $table->smallInteger('check4')->nullable()->comment('すごいの数');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE board_experiences COMMENT '体験記掲示板'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	    // Schema::drop('board_experiencess');
	}

}
