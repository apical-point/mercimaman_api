<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateOrderDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_details', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			// リレーション
			$table->bigInteger('order_id')->nullable()->comment('注文ID');

            // 販売への評価
            $table->bigInteger('seller_user_id')->nullable()->comment('出品ユーザーID');
			$table->bigInteger('seller_evaluation')->nullable()->comment('1:良い 2:悪い 3:普通');
			$table->string('seller_comment',512)->nullable()->comment('出品者へのコメント');

			// 購入者への評価
			$table->bigInteger('buyer_user_id')->nullable()->comment('購入ユーザーID');
			$table->bigInteger('buyer_evaluation')->nullable()->comment('1:良い 2:悪い 3:普通');
			$table->string('buyer_comment',512)->nullable()->comment('購入者へのコメント');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE order_details COMMENT '注文詳細'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('order_details');
	}

}
