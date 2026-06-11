<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Give the demo user a zero-balance AEPS wallet to receive CW credits.
        Wallet::firstOrCreate(
            ['user_id' => $user->id, 'type' => 'aeps'],
            ['balance' => 0],
        );
    }
}
