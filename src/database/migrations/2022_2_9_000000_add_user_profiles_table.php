<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;


class AddUserProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

	    Schema::table('user_profiles', function (Blueprint $table) {


	        $table->string('child_name1',30)->after("child_gender5")->nullable()->comment('子供のお名前またはニックネーム1');
	        $table->string('child_name2',30)->after("child_name1")->nullable()->comment('子供のお名前またはニックネーム2');
	        $table->string('child_name3',30)->after("child_name2")->nullable()->comment('子供のお名前またはニックネーム3');
	        $table->string('child_name4',30)->after("child_name3")->nullable()->comment('子供のお名前またはニックネーム4');
	        $table->string('child_name5',30)->after("child_name4")->nullable()->comment('子供のお名前またはニックネーム5');


	    });
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
