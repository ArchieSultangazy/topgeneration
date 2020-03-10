<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAchievementsTable extends Migration
{
    private $table = 'user_achievements';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('user_id', false, true)
                    ->comment('user_id из связной таблицы users');
                $table->integer('achievement_id', false, true)
                    ->comment('achievement_id из связной таблицы achievements');
                $table->timestamps();
                $table->softDeletes();
                $table->index(['user_id'], 'user_id');
                $table->index(['achievement_id'], 'achievement_id');
                $table->foreign('user_id', 'user_id_fk')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_achievements');
    }
}
