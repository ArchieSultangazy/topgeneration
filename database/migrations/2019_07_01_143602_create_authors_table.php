<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cl_authors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('middlename')->nullable();
            $table->text('about');
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('cl_course_authors');
        Schema::create('cl_course_authors', function (Blueprint $table) {
            $table->integer('author_id')->unsigned();
            $table->foreign('author_id')->references('id')->on('cl_authors')->onDelete('cascade');

            $table->integer('course_id')->unsigned();
            $table->foreign('course_id')->references('id')->on('cl_courses')->onDelete('cascade');
        });

        Schema::table('cl_courses', function (Blueprint $table) {
            $table->dropColumn('body');

            $table->text('body_in')->nullable()->after('video');
            $table->text('body_out')->nullable()->after('body_in');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cl_courses', function (Blueprint $table) {
            $table->dropColumn('body_in');
            $table->dropColumn('body_out');

            $table->text('body')->nullable()->after('video');
        });

        Schema::dropIfExists('cl_course_authors');
        Schema::create('cl_course_authors', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('course_id')->unsigned();
            $table->foreign('course_id')->references('id')->on('cl_courses')->onDelete('cascade');
        });

        Schema::dropIfExists('cl_authors');
    }
}
