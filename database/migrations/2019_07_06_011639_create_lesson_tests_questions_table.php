<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonTestsQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cl_lesson_tests_questions')) {
            Schema::create('cl_lesson_tests_questions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('lesson_test_id');
                $table->string('ru_name')->nullable()->default(null);
                $table->string('kk_name')->nullable()->default(null);
                $table->string('en_name')->nullable()->default(null);
                $table->unsignedInteger('correct_answer_id');
                $table->timestamps();
                $table->softDeletes();
                $table->index(['lesson_test_id'], 'lesson_test_id_index');
                $table->foreign('lesson_test_id')
                    ->references('cl_lesson_tests')
                    ->on('id')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('cl_lesson_tests_questions');
    }
}
