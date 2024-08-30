<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateUserprofilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function(Blueprint $table)
        {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // 会員情報
            $table->bigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->tinyInteger('image_id')->nullable()->comment('顔画像ID');
            $table->tinyInteger('chat')->nullable()->comment('チャットの有無 0:なし 1:あり');
            $table->string('nickname',14)->nullable()->comment('ニックネーム');
            $table->tinyInteger('condition')->nullable()->comment('現在の状況');
            $table->date('birthday')->nullable()->comment('生年月日');

            $table->date('child_birthday1')->nullable()->comment('子供の生年月日1');
            $table->date('child_birthday2')->nullable()->comment('子供の生年月日2');
            $table->date('child_birthday3')->nullable()->comment('子供の生年月日3');
            $table->date('child_birthday4')->nullable()->comment('子供の生年月日4');
            $table->date('child_birthday5')->nullable()->comment('子供の生年月日5');
            $table->tinyInteger('child_gender1')->nullable()->comment('子供の性別1');
            $table->tinyInteger('child_gender2')->nullable()->comment('子供の性別2');
            $table->tinyInteger('child_gender3')->nullable()->comment('子供の性別3');
            $table->tinyInteger('child_gender4')->nullable()->comment('子供の性別4');
            $table->tinyInteger('child_gender5')->nullable()->comment('子供の性別5');

            $table->tinyInteger('taste1')->nullable()->comment('テイスト1');
            $table->tinyInteger('taste2')->nullable()->comment('テイスト2');
            $table->tinyInteger('taste3')->nullable()->comment('テイスト3');

            $table->string('mother_interest1', 40)->nullable()->comment('ママの興味1');
            $table->string('mother_interest2', 40)->nullable()->comment('ママの興味2');
            $table->string('mother_interest3', 40)->nullable()->comment('ママの興味3');
            $table->string('mother_interest4', 40)->nullable()->comment('ママの興味4');

            $table->string('child_interest1', 40)->nullable()->comment('子供の興味1');
            $table->string('child_interest2', 40)->nullable()->comment('子供の興味2');
            $table->string('child_interest3', 40)->nullable()->comment('子供の興味3');
            $table->string('child_interest4', 40)->nullable()->comment('子供の興味4');

            $table->string('experience1', 40)->nullable()->comment('経験1');
            $table->string('experience2', 40)->nullable()->comment('経験2');
            $table->string('experience3', 40)->nullable()->comment('経験3');
            $table->string('experience4', 40)->nullable()->comment('経験4');

            $table->text('introduction')->nullable()->comment('自己紹介');
            $table->bigInteger('referral_code')->nullable()->comment('紹介コード');

            $table->string('mother_word1', 40)->nullable()->comment('ママの興味追加');
            $table->string('mother_word2', 40)->nullable()->comment('ママの興味追加');
            $table->string('mother_word3', 40)->nullable()->comment('ママの興味追加');
            $table->string('mother_word4', 40)->nullable()->comment('ママの興味追加');

            $table->string('child_word1', 40)->nullable()->comment('子供の興味追加');
            $table->string('child_word2', 40)->nullable()->comment('子供の興味追加');
            $table->string('child_word3', 40)->nullable()->comment('子供の興味追加');
            $table->string('child_word4', 40)->nullable()->comment('子供の興味追加');

            $table->string('experience_word1', 40)->nullable()->comment('経験の追加');
            $table->string('experience_word2', 40)->nullable()->comment('経験の追加');
            $table->string('experience_word3', 40)->nullable()->comment('経験の追加');
            $table->string('experience_word4', 40)->nullable()->comment('経験の追加');

            $table->string('show_product', 256)->nullable()->comment('最近見た商品');

            // 時刻データ
            $table->timestamps();

            // 倫理削除
            $table->softDeletes();

            // インデックス
            $table->index(['id']);
        });

        DB::statement("ALTER TABLE user_profiles COMMENT 'ユーザープロフィール'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_profiles');
    }

}
