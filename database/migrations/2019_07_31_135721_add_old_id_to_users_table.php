<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOldIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_districts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id')->nullable();
            $table->string('name');
        });

        Schema::create('location_localities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->string('name');
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id')->nullable();
            $table->integer('locality_id')->nullable();
            $table->string('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('district_id')->nullable()->after('region_id');
            $table->integer('locality_id')->nullable()->after('district_id');
            $table->integer('school_id')->nullable()->after('locality_id');
        });

        Schema::table('location_region', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_districts');
        Schema::dropIfExists('location_localities');
        Schema::dropIfExists('schools');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('district_id');
            $table->dropColumn('locality_id');
            $table->dropColumn('school_id');
        });

        Schema::table('location_region', function (Blueprint $table) {
            $table->enum('locale', ['kk', 'ru', 'en']);
        });
    }
}
