<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUpFilesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 画像--- 1対多のポリモーフィック
        Schema::create('up_files', function (Blueprint $table) {
            $table->char('id', 26)->primary();

            // ポリモーフィック
            $table->string('up_file_able_id', 1000)->nullable()->comment('ポリモーフィックid (ulidにも対応させるためにstring型になっている)');
            $table->string('up_file_able_type', 256)->nullable()->comment('ポリモーフィックタイプ');

            // 画像
            $table->string('name', 1000)->nullable()->comment('画像名');
            $table->string('title', 1000)->nullable()->comment('タイトル---任意の名前をつけれる');
            $table->string('mime_type', 64)->nullable()->comment('マイムタイプ');
            $table->integer('size')->nullable()->comment('画像サイズ');

            // 保存フォルダ
            $table->string('url_path', 128)->nullable()->comment('ルートからのurlパス');
            $table->string('dir_path', 128)->nullable()->comment('ルートからのdirパス');

            // 順番
            $table->tinyInteger('v_order')->default(1)->comment('順番');

            // システムで使用
            $table->tinyInteger('status')->default(0)->comment('ステータス');
            $table->text('remarks')->nullable()->comment('備考');

            // 時刻データ
            $table->timestamps();

            // インデックス
            $table->index(['id']);
        });

        // ALTER 文を実行しテーブルにコメントを設定
        DB::statement("ALTER TABLE up_files COMMENT 'アップロード画像'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('up_files');
    }
}
