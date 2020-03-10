<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKbThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kb_themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('kb_theme_articles', function (Blueprint $table) {
            $table->integer('theme_id')->unsigned();
            $table->foreign('theme_id')->references('id')->on('kb_themes')->onDelete('cascade');

            $table->integer('article_id')->unsigned();
            $table->foreign('article_id')->references('id')->on('kb_articles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kb_theme_articles');
        Schema::dropIfExists('kb_themes');
    }
}
