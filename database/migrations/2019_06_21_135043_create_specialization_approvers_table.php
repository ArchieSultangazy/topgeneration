<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecializationApproversTable extends Migration
{
    private $specializationApprovers = 'specialization_approvers';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->specializationApprovers)) {
            Schema::create($this->specializationApprovers, function (Blueprint $table) {
                $table->increments('id');

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->integer('specialization_id')->unsigned();
                $table->foreign('specialization_id')->references('id')->on('specializations')->onDelete('cascade');

                $table->integer('approver_id')->unsigned();
                $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('specialization_approvers');
    }
}
