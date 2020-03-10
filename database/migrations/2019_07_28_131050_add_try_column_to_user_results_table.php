<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTryColumnToUserResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_results') && !Schema::hasColumn('user_results', 'try')) {
            Schema::table('user_results', function (Blueprint $table) {
                $table->unsignedInteger('try')->nullable()->after('success');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('user_results') && Schema::hasColumn('user_results', 'try')) {
            Schema::table('user_results', function (Blueprint $table) {
                $table->dropColumn('try');
            });
        }
    }
}
