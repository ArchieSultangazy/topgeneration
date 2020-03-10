<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionIdsToUserResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_results', function (Blueprint $table) {
            $table->text('correct_questions')->nullable()->after('try');
            $table->text('wrong_questions')->nullable()->after('correct_questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_results', function (Blueprint $table) {
            $table->dropColumn('correct_questions');
            $table->dropColumn('wrong_questions');
        });
    }
}
