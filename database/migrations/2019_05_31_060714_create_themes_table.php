<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qa_themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('qa_theme_questions', function (Blueprint $table) {
            $table->integer('theme_id')->unsigned();
            $table->foreign('theme_id')->references('id')->on('qa_themes')->onDelete('cascade');

            $table->integer('question_id')->unsigned();
            $table->foreign('question_id')->references('id')->on('qa_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qa_theme_questions');
        Schema::dropIfExists('qa_themes');
    }
}
