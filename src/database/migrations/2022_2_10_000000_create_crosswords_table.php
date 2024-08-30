<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateCrosswordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crosswords', function(Blueprint $table)
		{
			// 主キー
			$table->bigIncrements('id')->comment('主キー');

			$table->date('post_date')->nullable()->comment('掲載日');
            $table->string('answer', 20)->nullable()->comment('回答');
            $table->string('hint1', 5)->nullable()->comment('ヒント1文字目');
            $table->string('hint2', 5)->nullable()->comment('ヒント2文字目');
            $table->string('hint3', 5)->nullable()->comment('ヒント3文字目');
            $table->string('hint4', 5)->nullable()->comment('ヒント4文字目');
            $table->string('hint5', 5)->nullable()->comment('ヒント5文字目');
            $table->string('hint6', 5)->nullable()->comment('ヒント6文字目');
            $table->string('hint7', 5)->nullable()->comment('ヒント7文字目');
            $table->string('hint8', 5)->nullable()->comment('ヒント8文字目');
            $table->string('hint9', 5)->nullable()->comment('ヒント9文字目');
            $table->string('hint10', 5)->nullable()->comment('ヒント10文字目');

            $table->string('xq1', 128)->nullable()->comment('ヨコのカギ1');
            $table->string('xq2', 128)->nullable()->comment('ヨコのカギ2');
            $table->string('xq3', 128)->nullable()->comment('ヨコのカギ3');
            $table->string('xq4', 128)->nullable()->comment('ヨコのカギ4');

            $table->string('yq1', 128)->nullable()->comment('タテのカギ1');
            $table->string('yq2', 128)->nullable()->comment('タテのカギ2');
            $table->string('yq3', 128)->nullable()->comment('タテのカギ3');
            $table->string('yq4', 128)->nullable()->comment('タテのカギ4');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
		});

		DB::statement("ALTER TABLE crosswords COMMENT '火曜日コンテンツ　クロスワードデータ'");
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
