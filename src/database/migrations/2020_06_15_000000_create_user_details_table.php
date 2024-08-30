<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateUserDetailsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function(Blueprint $table)
        {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // 会員情報
            $table->bigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->string('last_name',60)->nullable()->comment('姓');
            $table->string('first_name',60)->nullable()->comment('名');
            $table->string('last_name_kana',60)->nullable()->comment('姓カナ');
            $table->string('first_name_kana',60)->nullable()->comment('名カナ');
            $table->bigInteger('prefecture_id')->nullable()->comment('都道府県ID');
            $table->string('zip', 8)->nullable()->comment('郵便番号');
            $table->string('address1', 40)->nullable()->comment('住所1');
            $table->string('address2', 40)->nullable()->comment('住所2');
            $table->string('building', 40)->nullable()->comment('建物名');
            $table->string('tel', 20)->nullable()->comment('電話');
            $table->tinyInteger('address_flg')->default(0)->comment('プロフィール表示フラグ');
            $table->string('withdrawal',500)->nullable()->comment('退会理由');

            $table->text('stripe_id')->nullable()->comment('決済ID');
            $table->string('bank_code',10)->nullable()->comment('金融機関コード');
            $table->tinyInteger('bank_type')->nullable()->comment('口座種別');
            $table->string('bank_branch_code',10)->nullable()->comment('支店コード');
            $table->string('bank_number',8)->nullable()->comment('口座番号');
            $table->string('bank_name',30)->nullable()->comment('口座氏名');

            $table->tinyInteger('identification')->default(0)->comment('本人確認　0:未申請 1:申請中 2:承認 3:却下');

            //$table->tinyInteger('receive_flg')->nullable()->comment('送り先フラグ');
            //$table->string('receiver_last_name',60)->nullable()->comment('送り先姓');
            //$table->string('receiver_first_name',60)->nullable()->comment('送り先名');
            //$table->string('receiver_last_name_kana',60)->nullable()->comment('送り先姓カナ');
            //$table->string('receiver_first_name_kana',60)->nullable()->comment('送り先名カナ');
            //$table->bigInteger('receiver_zip')->nullable()->comment('送り先郵便番号');
            //$table->string('receiver_prefecture_id')->nullable()->comment('送り先都道府県ID');
            //$table->string('receiver_address1',40)->nullable()->comment('送り先住所1');
            //$table->string('receiver_address2',40)->nullable()->comment('送り先住所2');
            //$table->string('receiver_building',40)->nullable()->comment('送り先建物');
            //$table->string('receiver_tel',20)->nullable()->comment('送り先電話');

            // 時刻データ
            $table->timestamps();

            // 倫理削除
            $table->softDeletes();

            // インデックス
            $table->index(['id']);
        });

        DB::statement("ALTER TABLE user_details COMMENT 'ユーザー詳細'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_details');
    }

}
