<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qa_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title');
            $table->text('body');
            $table->integer('rating')->default(0);
            $table->text('tags');
            $table->integer('views')->default(0);
            $table->timestamps();
        });

        Schema::create('qa_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('question_id')->unsigned();
            $table->foreign('question_id')->references('id')->on('qa_questions')->onDelete('cascade');

            $table->text('body');
            $table->integer('rating')->default(0);
            $table->timestamps();
        });

        Schema::create('qa_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('answer_id')->unsigned();
            $table->foreign('answer_id')->references('id')->on('qa_answers')->onDelete('cascade');

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
        Schema::dropIfExists('qa_comments');
        Schema::dropIfExists('qa_answers');
        Schema::dropIfExists('qa_questions');
    }
}
