<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateProductCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_categories', function(Blueprint $table)
		{
			$table->bigIncrements('id')->comment('主キー');

			// データ
			$table->string('product_category_name',256)->comment('カテゴリー名');
			$table->bigInteger('parentid')->default(0)->comment('親id');
            $table->tinyInteger('v_order')->comment('表示順');
			$table->tinyInteger('cflag')->default(0)->comment('カテゴリー種類');
            $table->tinyInteger('imgindex')->default(0)->comment('画像番号');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE product_categories COMMENT '商品カテゴリ'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('product_categories');
	}

}
