<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FriendsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $friends = [
            [
                'user_id' => 1,
                'friend_id' => 2,
                'status' => 2
            ]
        ];

        foreach ($friends as $friend) {
            DB::table('friendships')->insert($friend);
        }
    }
}
