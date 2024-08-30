<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreatePointsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points', function(Blueprint $table)
        {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // 会員情報
            $table->bigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->tinyInteger('point_type')->nullable()->comment('ポイントタイプ');

            $table->string('point_detail',100)->nullable()->comment('詳細');
            $table->integer('point')->default(0)->comment('ポイント');
            $table->integer('use_point')->default(0)->comment('使用ポイント');
            $table->integer('use_flg')->default(0)->comment('使用済ポイント 0:使用可能　1:使用済み');
            $table->date('point_date')->nullable()->comment('ポイント付与日');
            $table->date('use_date')->nullable()->comment('最終ポイント使用日');
            $table->date('expiration_date')->nullable()->comment('有効期限');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
        });

        DB::statement("ALTER TABLE points COMMENT 'ユーザーポイント'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('points');
    }

}
