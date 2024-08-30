<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			// カラム
            $table->bigInteger('seller_user_id')->nullable()->comment('販売ユーザーID');
            $table->bigInteger('buyer_user_id')->nullable()->comment('購入ユーザーID');
            $table->bigInteger('product_id')->nullable()->comment('商品ID');

            $table->bigInteger('total_price')->nullable()->comment('商品合計');
            $table->bigInteger('system_charge')->nullable()->comment('システム手数料');
            $table->bigInteger('sales_price')->nullable()->comment('出品者受取金額');
            $table->bigInteger('payment_price')->nullable()->comment('購入者支払金額');
            $table->bigInteger('point')->nullable()->comment('購入者使用ポイント');

            $table->tinyInteger('status')->default(1)->comment('1：取引中 2：発送済 3:受取完了 4：取引完了 5：支払登録 6：支払自動 7：支払処理中 8:支払完了');
            $table->text('charge_id')->nullable()->comment('決済ID => Stripeの与信結果で返ってくる決済ID');

            $table->string('seller_zip',10)->nullable()->comment('配送元郵便番号');
            $table->bigInteger('seller_prefecture_id')->nullable()->comment('配送元都道府県');
            $table->string('seller_address1',40)->nullable()->comment('配送元住所1');
            $table->string('seller_address2',40)->nullable()->comment('配送元住所2');
            $table->string('seller_building',40)->nullable()->comment('配送元建物');
            $table->string('seller_name',128)->nullable()->comment('配送元名');
            $table->string('seller_email',40)->nullable()->comment('配送元email');
            $table->string('seller_tel',40)->nullable()->comment('配送元電話番号');
            $table->string('buyer_zip',10)->nullable()->comment('配送先郵便番号');
            $table->bigInteger('buyer_prefecture_id')->nullable()->comment('配送先都道府県');
            $table->string('buyer_address1',40)->nullable()->comment('配送先住所1');
            $table->string('buyer_address2',40)->nullable()->comment('配送先住所2');
            $table->string('buyer_building',40)->nullable()->comment('配送先建物');
            $table->string('buyer_name',128)->nullable()->comment('配送先氏名');
            $table->string('buyer_email',40)->nullable()->comment('配送先email');
            $table->string('buyer_tel',40)->nullable()->comment('配送先電話番号');
            $table->string('yamato_password',40)->nullable()->comment('匿名発送時のパスワード');
            $table->string('yamato_reserve_no',40)->nullable()->comment('匿名発送時の予約番号');
            $table->bigInteger('payment_id')->nullable()->comment('銀行申請ID');

            $table->date('order_date')->nullable()->comment('注文日');
            $table->date('shipping_date')->nullable()->comment('発送日');
            $table->date('arrival_date')->nullable()->comment('到着日');
            $table->date('tradeend_date')->nullable()->comment('取引終了日');
            $table->string('sellkeep_date',7)->nullable()->comment('売上金保持期限');
            $table->date('payment_request_date')->nullable()->comment('支払申請日');
            $table->date('payment_csv_date')->nullable()->comment('銀行申請CSV出力日');
            $table->date('banktransfer_date')->nullable()->comment('銀行振込日');
            $table->date('auto_request')->nullable()->comment('自動申請');

            $table->tinyInteger('open_flg')->default(1)->comment('1：表示 0：非表示');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE orders COMMENT '注文'");
	}



	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders');
	}

}
