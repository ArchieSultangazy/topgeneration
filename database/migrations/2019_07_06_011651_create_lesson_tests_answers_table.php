<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonTestsAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cl_lesson_tests_answers')) {
            Schema::create('cl_lesson_tests_answers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('question_id');
                $table->string('ru_name')->nullable()->default(null);
                $table->string('kk_name')->nullable()->default(null);
                $table->string('en_name')->nullable()->default(null);
                $table->tinyInteger('is_correct')->nullable()->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->index(['question_id'], 'question_id_index');
                $table->foreign('question_id')
                    ->references('cl_lesson_tests_questions')
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
        Schema::dropIfExists('cl_lesson_tests_answers');
    }
}
