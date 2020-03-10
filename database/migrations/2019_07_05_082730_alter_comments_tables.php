<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cl_course_comments', function (Blueprint $table) {
            $table->integer('parent_id')->nullable()->after('id');
        });
        Schema::table('cl_lesson_comments', function (Blueprint $table) {
            $table->integer('parent_id')->nullable()->after('id');
        });
        Schema::table('kb_comments', function (Blueprint $table) {
            $table->integer('parent_id')->nullable()->after('id');
        });
        Schema::table('qa_comments', function (Blueprint $table) {
            $table->integer('parent_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cl_course_comments', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
        Schema::table('cl_lesson_comments', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
        Schema::table('kb_comments', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
        Schema::table('qa_comments', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
