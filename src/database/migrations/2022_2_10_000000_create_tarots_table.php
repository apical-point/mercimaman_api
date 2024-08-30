<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateTarotsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tarots', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			$table->date('post_date')->nullable()->comment('掲載日');

            $table->tinyInteger('card1')->nullable()->comment('1枚目のカード番号');
            $table->tinyInteger('card_fb1')->nullable()->comment('1枚目のカード　1:正 2:逆');
            $table->string('card_result1', 255)->nullable()->comment('1枚目のカード 結果');

            $table->tinyInteger('card2')->nullable()->comment('2枚目のカード番号');
            $table->tinyInteger('card_fb2')->nullable()->comment('2枚目のカード　1:正 2:逆');
            $table->string('card_result2', 255)->nullable()->comment('2枚目のカード 結果');

            $table->tinyInteger('card3')->nullable()->comment('3枚目のカード番号');
            $table->tinyInteger('card_fb3')->nullable()->comment('3枚目のカード　1:正 2:逆');
            $table->string('card_result3', 255)->nullable()->comment('3枚目のカード 結果');

            $table->tinyInteger('card4')->nullable()->comment('4枚目のカード番号');
            $table->tinyInteger('card_fb4')->nullable()->comment('4枚目のカード　1:正 2:逆');
            $table->string('card_result4', 255)->nullable()->comment('4枚目のカード 結果');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE tarots COMMENT '木曜日コンテンツ　タロット占い'");
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
