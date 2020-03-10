<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cl_themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale', 10);
            $table->string('name', 255);
            $table->timestamps();
        });

        Schema::create('cl_courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale', 10);
            $table->string('slug', 255);
            $table->string('title', 255);
            $table->string('img_preview', 255)->nullable();
            $table->string('video',255)->nullable();
            $table->text('body')->nullable();
            $table->float('duration')->default(0);
            $table->float('rating')->nullable()->default(null);
            $table->boolean('is_published')->default(0);
            $table->timestamps();
        });

        Schema::create('cl_course_themes', function (Blueprint $table) {
            $table->integer('theme_id')->unsigned();
            $table->foreign('theme_id')->references('id')->on('cl_themes')->onDelete('cascade');

            $table->integer('course_id')->unsigned();
            $table->foreign('course_id')->references('id')->on('cl_courses')->onDelete('cascade');
        });

        Schema::create('cl_course_authors', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('course_id')->unsigned();
            $table->foreign('course_id')->references('id')->on('cl_courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cl_course_themes');
        Schema::dropIfExists('cl_course_authors');
        Schema::dropIfExists('cl_themes');
        Schema::dropIfExists('cl_courses');
    }
}
