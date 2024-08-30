<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('events', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

            $table->tinyInteger('month')->nullable()->comment('月');
            $table->string('name',128)->nullable()->comment('イベント名');
            $table->text('detail')->nullable()->comment('イベント内容');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE events COMMENT 'イベントカレンダー'");
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
