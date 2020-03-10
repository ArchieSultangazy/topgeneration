<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTable1 extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('email');
            $table->dropColumn('email_verified_at');

            $table->string('firstname')->after('password');
            $table->string('lastname')->after('firstname');
            $table->string('middlename')->nullable()->default(null)->after('lastname');
            $table->string('username')->after('id')->unique()->nullable()->default(null);
            $table->string('phone')->after('username')->unique();
            $table->timestamp('phone_verified_at')->after('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('password');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->dropColumn('firstname');
            $table->dropColumn('lastname');
            $table->dropColumn('middlename');
            $table->dropColumn('username');
            $table->dropColumn('phone');
            $table->dropColumn('phone_verified_at');
        });
    }
}
