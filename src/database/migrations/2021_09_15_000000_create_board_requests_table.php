<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateBoardRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('board_requests', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');


            $table->text('detail')->nullable()->comment('リクエスト内容');
            $table->bigInteger('parent_id')->default(0)->comment('親ID');
            $table->bigInteger('member_id')->nullable()->comment('会員ID');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE board_requests COMMENT 'リクエスト掲示板'");
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
