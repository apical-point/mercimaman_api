<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateSiteConfigsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_configs', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			// データ
			$table->string('key_name', 64)->nullable()->comment('設定キー名');
			$table->string('value', 64)->nullable()->comment('設定名');
			$table->text('description', 65535)->nullable()->comment('設定説明');
			$table->tinyInteger('sort')->nullable()->comment('ソート順:未使用');
			// $table->string('data_type', 32)->nullable()->comment('データ種別:未使用');

			// 時刻データ
			$table->timestamps();

			// インデックス
			$table->index(['id']);
		});

		DB::statement("ALTER TABLE site_configs COMMENT 'サイトの設定'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_configs');
	}

}
