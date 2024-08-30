<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class CreateProductsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function(Blueprint $table)
        {
            // 主キー
            $table->bigIncrements('id')->comment('主キー');

            // データ
            $table->string('product_name',40)->nullable()->comment('商品名');
            $table->string('detail',512)->nullable()->comment('商品説明');
            $table->bigInteger('category')->nullable()->comment('1:既製品　2：ハンドメイド');
            $table->bigInteger('product_category1_id')->nullable()->comment('商品カテゴリ1');
            $table->bigInteger('product_category2_id')->nullable()->comment('商品カテゴリ2');

            $table->tinyInteger('status')->nullable()->comment('1：下書 2：出品中 3:取引中 4：発送済 5：受取完了 6：取引完了');
            $table->string('youtube', 100)->nullable()->comment('YouTube');

            $table->bigInteger('user_id')->nullable()->comment('販売者');
            $table->string('brand',40)->nullable()->comment('ブランド');
            $table->string('size', 40)->nullable()->comment('サイズ');
            $table->tinyInteger('condition')->nullable()->comment('商品状態');
            $table->tinyInteger('taste')->nullable()->comment('テイスト');
            $table->tinyInteger('shipping_charges')->nullable()->comment('発送料の負担');
            $table->tinyInteger('shipping_method')->nullable()->comment('発送方法');
            $table->tinyInteger('shipping_day')->nullable()->comment('発送日の目安');
            $table->string('shipping_area', 40)->nullable()->comment('発送元の地域');
            $table->tinyInteger('handing')->nullable()->comment('手渡しの可否');

            $table->bigInteger('price')->nullable()->comment('料金');
            $table->bigInteger('system_charge')->nullable()->comment('販売手数料');

            $table->tinyInteger('inappropriate')->default(0)->comment('不適切通報');
            $table->bigInteger('inappropriate_user')->nullable()->comment('不適切通報を行ったユーザー');
            $table->string('inappropriate_reason', 512)->nullable()->comment('不適切通報の理由');
            $table->tinyInteger('auto_flg')->default(0)->comment('自動解除');
            $table->tinyInteger('open_flg')->default(0)->comment('0:非表示 1:表示 ');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
        });

        DB::statement("ALTER TABLE products COMMENT '商品'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }
}
