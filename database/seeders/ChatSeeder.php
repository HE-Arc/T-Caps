<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chat1 = Chat::create([
            'name' => 'Test Chat',
        ]);

        $allUsers = User::all();
        $chat1->users()->attach([$allUsers->first()->id, $allUsers->last()->id]);

        foreach (range(1, 10) as $index) {
            $chat1->messages()->create([
                'user_id' => $allUsers->random()->id,
                'message' => 'This is test message number ' . $index . ' in chat ' . $chat1->name,
            ]);
        }
    }
}
