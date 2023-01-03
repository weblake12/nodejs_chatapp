<?php

use Illuminate\Database\Seeder;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $users_array = [
            [
                'name' => 'Lionel AKE',
                'email' => 'contact@lionel-ake.com',
                'password' => bcrypt('password'),
                'is_online' => false,
                'created_at' => time(),
                'created_by' => 1
            ],[
                'name' => 'George Washington',
                'email' => 'george-washington@gmail.com',
                'password' => bcrypt('password'),
                'is_online' => false,
                'created_at' => time(),
                'created_by' => 1
            ],[
                'name' => 'Abraham Lincoln',
                'email' => 'abraham-lincoln@gmail.com',
                'password' => bcrypt('password'),
                'is_online' => false,
                'created_at' => time(),
                'created_by' => 1
            ],[
                'name' => 'John Kenedy',
                'email' => 'john-kenedy@gmail.com',
                'password' => bcrypt('password'),
                'is_online' => false,
                'created_at' => time(),
                'created_by' => 1
            ],
        ];

        foreach ($users_array as $user) {
            User::create($user);
        }
    }
}
