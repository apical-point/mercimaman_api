<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateProductBrandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_brands', function(Blueprint $table)
		{
			$table->bigIncrements('id')->comment('主キー');

			// データ
            $table->string('strindex',10)->comment('索用文字列');
            $table->tinyInteger('brandindex')->comment('索用数値');
			$table->string('brand',256)->comment('ブランド名');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE product_categories COMMENT 'ブランド'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('product_brands');
	}

}
