<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResultProcentColumnToUserResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_results') && !Schema::hasColumn('user_results', 'result_percent')) {
            Schema::table('user_results', function (Blueprint $table) {
                $table->float('result_percent')->nullable()->after('result');
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
        if (Schema::hasTable('user_results') && Schema::hasColumn('user_results', 'result_percent')) {
            Schema::table('user_results', function (Blueprint $table) {
                $table->dropColumn('result_percent');
            });
        }
    }
}
