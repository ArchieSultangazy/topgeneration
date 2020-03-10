<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinishedAtColumnToUserResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_results')) {
            Schema::table('user_results', function (Blueprint $table) {
                if (!Schema::hasColumn('user_results', 'finished_at')) {
                    $table->timestamp('finished_at')->nullable();
                }
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
        if (Schema::hasTable('user_results')) {
            Schema::table('user_results', function (Blueprint $table) {
                if (Schema::hasColumn('user_results', 'finished_at')) {
                    $table->dropColumn('finished_at');
                }
            });
        }
    }
}
