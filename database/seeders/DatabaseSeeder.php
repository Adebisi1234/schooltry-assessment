<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if test user already exists
        $testUser = User::where('email', 'test@example.com')->first();
        
        if (!$testUser) {
            // Create test user if doesn't exist
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'), 
                'role' => 'teacher',
            ]);
        }
        
        // Seed documents
        $this->call(DocumentSeeder::class);
    }
}
