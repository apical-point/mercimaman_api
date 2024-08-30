<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateGrowthsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('growths', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

            $table->tinyInteger('age_no')->nullable()->comment('年齢番号');
            $table->text('detail')->nullable()->comment('イベント内容');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE growths COMMENT '成長カレンダー記事'");
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
