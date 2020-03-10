<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignsToLocationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_districts', function (Blueprint $table) {
            $table->integer('region_id')->nullable()->unsigned()->change();
            $table->foreign('region_id')->references('id')->on('location_region')->onDelete('cascade');
        });

        Schema::table('location_localities', function (Blueprint $table) {
            $table->integer('region_id')->nullable()->unsigned()->change();
            $table->foreign('region_id')->references('id')->on('location_region')->onDelete('cascade');

            $table->integer('district_id')->nullable()->unsigned()->change();
            $table->foreign('district_id')->references('id')->on('location_districts')->onDelete('cascade');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->integer('region_id')->nullable()->unsigned()->change();
            $table->foreign('region_id')->references('id')->on('location_region')->onDelete('cascade');

            $table->integer('locality_id')->nullable()->unsigned()->change();
            $table->foreign('locality_id')->references('id')->on('location_localities')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('region_id')->nullable()->unsigned()->change();
            $table->foreign('region_id')->references('id')->on('location_region')->onDelete('cascade');

            $table->integer('district_id')->nullable()->unsigned()->change();
            $table->foreign('district_id')->references('id')->on('location_districts')->onDelete('cascade');

            $table->integer('locality_id')->nullable()->unsigned()->change();
            $table->foreign('locality_id')->references('id')->on('location_localities')->onDelete('cascade');

            $table->integer('school_id')->nullable()->unsigned()->change();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_districts', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
        });

        Schema::table('location_localities', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['district_id']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['locality_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['locality_id']);
            $table->dropForeign(['school_id']);
        });
    }
}
