<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cl_course_comments', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('course_id')->unsigned();
            $table->foreign('course_id')->references('id')->on('cl_courses')->onDelete('cascade');

            $table->text('body');
            $table->integer('rating')->default(0);

            $table->timestamps();
        });

        Schema::create('cl_lesson_comments', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('lesson_id')->unsigned();
            $table->foreign('lesson_id')->references('id')->on('cl_lessons')->onDelete('cascade');

            $table->text('body');
            $table->integer('rating')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cl_course_comments');
        Schema::dropIfExists('cl_lesson_comments');
    }
}
