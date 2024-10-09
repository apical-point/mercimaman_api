<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUrlColumnsToReviewProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('review_product', function (Blueprint $table) {
        //     $table->text('rakuten_url')->after("url")->nullable()->comment('楽天URL');
        //     $table->text('myshop_url')->after("rakuten_url")->nullable()->comment('公式サイトURL');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_product', function (Blueprint $table) {
            //
        });
    }
}
