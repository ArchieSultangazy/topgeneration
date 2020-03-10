<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampsToRatingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cl_authors', function (Blueprint $table) {
            $table->text('about')->nullable()->change();
        });

        Schema::table('rate_courses', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('rate_lessons', function (Blueprint $table) {
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
        Schema::table('cl_authors', function (Blueprint $table) {
            $table->text('about')->change();
        });

        Schema::table('rate_courses', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('rate_lessons', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
}
