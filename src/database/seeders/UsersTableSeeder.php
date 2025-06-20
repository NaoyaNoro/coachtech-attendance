<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'user1',
                'email' => 'user1@sample.com',
                'password' => Hash::make('user10000'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => 'user2',
                'email' => 'user2@sample.com',
                'password' => Hash::make('user20000'),
                'email_verified_at' => Carbon::now(),
            ],
        ];
        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
