<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUpdatedAtDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('review_product', function (Blueprint $table) {
        //     $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->change();
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
