<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cl_lesson_tests')) {
            Schema::create('cl_lesson_tests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('lesson_id')->comment('id урока из связанной таблицы');
                $table->unsignedInteger('created_user_id')
                    ->nullable()
                    ->default(null)
                    ->comment('id юзера создавшего запись');
                $table->timestamps();
                $table->softDeletes();
                $table->index(['lesson_id'], 'lesson_id_index');
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
        Schema::dropIfExists('cl_lesson_tests');
    }
}
