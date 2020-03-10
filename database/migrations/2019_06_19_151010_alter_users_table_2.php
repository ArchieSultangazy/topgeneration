<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTable2 extends Migration
{
    private $locationRegion = 'location_region';
    private $jobDomains = 'job_domains';
    private $userJobs = 'user_jobs';
    private $users = 'users';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->locationRegion)) {
            Schema::create($this->locationRegion, function (Blueprint $table) {
                $table->increments('id');
                $table->enum('locale', ['kk', 'ru', 'en']);
                $table->string('name');
            });
        }

        if (!Schema::hasTable($this->jobDomains)) {
            Schema::create($this->jobDomains, function (Blueprint $table) {
                $table->increments('id');
                $table->enum('locale', ['kk', 'ru', 'en']);
                $table->string('name');
            });
        }

        if (!Schema::hasTable($this->userJobs)) {
            Schema::create($this->userJobs, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->string('name')->nullable()->default(null);
                $table->integer('domain_id')->nullable()->default(null);
                $table->string('position')->nullable()->default(null);
                $table->timestamp('start_date')->nullable()->default(null);
                $table->string('link')->nullable()->default(null);
                $table->timestamps();
            });
        }

        Schema::table($this->users, function (Blueprint $table) {
            if (!Schema::hasColumn($this->users, 'email')) {
                $table->string('email')->nullable()->default(null)->after('phone_verified_at');
            }
            if (!Schema::hasColumn($this->users, 'avatar')) {
                $table->string('avatar')->nullable()->default(null)->after('middlename');
            }
            if (!Schema::hasColumn($this->users, 'status')) {
                $table->string('status')->nullable()->default(null)->after('avatar');
            }
            if (!Schema::hasColumn($this->users, 'about')) {
                $table->string('about')->nullable()->default(null)->after('status');
            }
            if (!Schema::hasColumn($this->users, 'site')) {
                $table->string('site')->nullable()->default(null)->after('about');
            }
            if (!Schema::hasColumn($this->users, 'contacts')) {
                $table->text('contacts')->nullable()->default(null)->after('site');
            }
            if (!Schema::hasColumn($this->users, 'birth_date')) {
                $table->timestamp('birth_date')->nullable()->default(null)->after('contacts');
            }
            if (!Schema::hasColumn($this->users, 'region_id')) {
                $table->integer('region_id')->nullable()->default(null)->after('birth_date');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->locationRegion);
        Schema::dropIfExists($this->jobDomains);
        Schema::dropIfExists($this->userJobs);

        Schema::table($this->users, function (Blueprint $table) {
            if (Schema::hasColumn($this->users, 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn($this->users, 'avatar')) {
                $table->dropColumn('avatar');
            }
            if (Schema::hasColumn($this->users, 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn($this->users, 'about')) {
                $table->dropColumn('about');
            }
            if (Schema::hasColumn($this->users, 'site')) {
                $table->dropColumn('site');
            }
            if (Schema::hasColumn($this->users, 'contacts')) {
                $table->dropColumn('contacts');
            }
            if (Schema::hasColumn($this->users, 'birth_date')) {
                $table->dropColumn('birth_date');
            }
            if (Schema::hasColumn($this->users, 'region_id')) {
                $table->dropColumn('region_id');
            }
        });
    }
}
