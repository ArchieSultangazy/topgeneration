<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cl_lessons', function (Blueprint $table) {
            $table->string('body_short')->nullable()->after('body');
            $table->text('scheme')->nullable()->after('body_short');
            $table->text('articles')->nullable()->after('scheme');
        });

        Schema::create('cl_lesson_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lesson_id')->unsigned();
            $table->foreign('lesson_id')->references('id')->on('cl_lessons')->onDelete('cascade');
            $table->string('title');
            $table->string('body');
            $table->string('link');
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
        Schema::dropIfExists('cl_lesson_files');

        Schema::table('cl_lessons', function (Blueprint $table) {
            $table->dropColumn('body_short');
            $table->dropColumn('scheme');
            $table->dropColumn('articles');
        });
    }
}
