<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateUserGrowthsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_growths', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			$table->bigInteger('user_id')->nullable()->comment('ユーザーID');
			$table->smallInteger('growth_age_id')->nullable()->comment('各年齢の出来る事');
            $table->string('name', 20)->nullable()->comment('ユーザーのお子様のフィールド名 child_name1～5');
            $table->string('birth', 20)->nullable()->comment('ユーザーのお子様のフィールド名 child_birthday1～5');
            $table->date('age_date')->nullable()->comment('出来るようになった日付け');


            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE user_growths COMMENT 'ユーザーのお子様が各年齢の出来る事で出来た日の登録'");
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
