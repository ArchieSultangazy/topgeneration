<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAchievementsTable extends Migration
{
    private $table = 'achievements';
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
                $table->string('ru_name', 255)->nullable()->default(null)->comment('Название на русском');
                $table->string('kk_name', 255)->nullable()->default(null)->comment('Название на казахском');
                $table->string('en_name', 255)->nullable()->default(null)->comment('Название на английском');
                $table->string('key', 191)->nullable()->default(null)->comment('Буквенный ключ');
                $table->integer('points', false, true)->default(null)->comment('Очки за достижение');
                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('achievements');
    }
}
