<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
		DB::table('users')->insert([
			'name' => 'Example User',
			'email' => 'example@example.com',
			'password' => Hash::make('password'),
		]);
		DB::table('users')->insert([
			'name' => 'Another User',
			'email' => 'another@example.com',
			'password' => Hash::make('password'),
		]);
	}
}
