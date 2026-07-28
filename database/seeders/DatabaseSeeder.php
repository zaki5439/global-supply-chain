<?php

namespace Database\Seeders;

use App\Models\User;
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
        \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'user'
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'admin@supplychain.com'],
            [
                'name' => 'Admin Root',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin'
            ]
        );

        // Seed sentiment lexicon
        $this->call([
            LexiconSentimentSeeder::class,
            AllCountriesSeeder::class,
        ]);
    }
}
