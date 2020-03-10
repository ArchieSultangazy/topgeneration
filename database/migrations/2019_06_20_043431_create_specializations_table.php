<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecializationsTable extends Migration
{
    private $specializations = 'specializations';
    private $userSpecializations = 'user_specializations';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->specializations)) {
            Schema::create($this->specializations, function (Blueprint $table) {
                $table->increments('id');
                $table->enum('locale', ['kk', 'ru', 'en']);
                $table->string('name');
            });
        }
        if (!Schema::hasTable($this->userSpecializations)) {
            Schema::create($this->userSpecializations, function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->integer('specialization_id')->unsigned();
                $table->foreign('specialization_id')->references('id')->on('specializations')->onDelete('cascade');
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
        Schema::dropIfExists('user_specializations');
        Schema::dropIfExists('specializations');
    }
}
