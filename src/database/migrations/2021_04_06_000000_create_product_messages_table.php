<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateProductMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_messages', function(Blueprint $table)
		{
			$table->bigIncrements('id')->comment('主キー');

			// データ
			$table->bigInteger('product_id')->nullable()->comment('商品ID');
			$table->text('message')->nullable()->comment('メッセージ');
			$table->tinyInteger('user_id')->nullable()->comment('送信者ID');
            $table->tinyInteger('open_flg')->default(1)->comment('0:非表示 1:表示 ');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE product_messages COMMENT '商品メッセージ'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('product_messages');
	}

}
