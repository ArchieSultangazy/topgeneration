<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_results')) {
            Schema::create('user_results', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('test_id');
                $table->unsignedInteger('lesson_id');
                $table->float('result');
                $table->tinyInteger('success');
                $table->timestamps();
                $table->softDeletes();
                $table->index('user_id', 'user_id_results_index');
                $table->index('test_id', 'test_id_results_index');
                $table->index('lesson_id', 'lesson_id_results_index');
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
        Schema::dropIfExists('user_answers');
    }
}
