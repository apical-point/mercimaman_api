<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateGrowthAgesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('growth_ages', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

            $table->tinyInteger('age_no')->nullable()->comment('年齢番号');
            $table->text('name')->nullable()->comment('該当年齢で出来る事');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE growth_ages COMMENT '各年齢で出来る事'");
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
