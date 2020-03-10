<?php

use Illuminate\Database\Seeder;

class UserGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('access_group')->insert([
            ['id' => \App\User::TYPE_TEACHER, 'name' => "teacher", 'description' => "Учитель"],
            ['id' => \App\User::TYPE_ENTREPRENEUR, 'name' => "entrepreneur", 'description' => "Предприниматель"],
            ['id' => \App\User::TYPE_SPECIALIST, 'name' => "specialist", 'description' => "Специалист"],
            ['id' => \App\User::TYPE_STUDENT, 'name' => "student", 'description' => "Школьник"],
        ]);
    }
}
