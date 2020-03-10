<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('locale', ['kk', 'ru', 'en']);
            $table->enum('type', ['text', 'video_in', 'video_out']);

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title');
            $table->mediumText('body')->nullable()->default(null);
            $table->string('video')->nullable()->default(null);

            $table->integer('rating')->default(0);
            $table->integer('views')->default(0);
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
        Schema::dropIfExists('kb_articles');
    }
}
