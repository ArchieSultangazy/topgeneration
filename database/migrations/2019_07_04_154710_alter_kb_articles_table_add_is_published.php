<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterKbArticlesTableAddIsPublished extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->integer('is_published')->default(0)->after('locale');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
}
