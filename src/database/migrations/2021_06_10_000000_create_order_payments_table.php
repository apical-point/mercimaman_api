<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateOrderpaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_payments', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			// リレーション
			$table->bigInteger('user_id')->nullable()->comment('注文ID');

            // 販売への評価
            $table->bigInteger('count')->nullable()->comment('商品件数');
			$table->bigInteger('bank_price')->nullable()->comment('振込金額');
            $table->bigInteger('sales_price')->nullable()->comment('売上金額合計');
			$table->bigInteger('system_charge')->nullable()->comment('システム手数料合計');
			$table->bigInteger('total_price')->nullable()->comment('商品合計');
			$table->date('payment_request_date')->nullable()->comment('売上振込申請日');
            $table->date('payment_csv_date')->nullable()->comment('銀行申請日');
            $table->date('banktransfer_date')->nullable()->comment('振込日');
            $table->tinyInteger('csv_flg')->default(0)->comment('0:未申請 1:申請済 2:振込済');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE order_details COMMENT '注文銀行振込履歴'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('order_payments');
	}

}
