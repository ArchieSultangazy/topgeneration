<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteToAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cl_authors', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('cl_course_comments', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('cl_courses', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('cl_lesson_comments', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('cl_lesson_files', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('cl_lessons', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('cl_themes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('kb_comments', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('kb_themes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('qa_answers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('qa_comments', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('qa_questions', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('qa_themes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cl_authors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('cl_course_comments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('cl_courses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('cl_lesson_comments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('cl_lesson_files', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('cl_lessons', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('cl_themes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('kb_comments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('kb_themes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('qa_answers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('qa_comments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('qa_questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('qa_themes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
