<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test user
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $this->command->error('Test user not found. Please run DatabaseSeeder first.');
            return;
        }
        
        // Array of text files to seed
        $textFiles = [
            [
                'file' => 'content/artificial_intelligence.txt',
                'title' => 'Artificial Intelligence: An Overview',
                'description' => 'A comprehensive overview of artificial intelligence, its history, approaches, applications, and future prospects.'
            ],
            [
                'file' => 'content/climate_change.txt',
                'title' => 'Climate Change: A Global Challenge',
                'description' => 'An in-depth look at climate change, its causes, impacts, and potential solutions.'
            ],
            [
                'file' => 'content/history_of_computing.txt',
                'title' => 'The History of Computing',
                'description' => 'A detailed timeline of computing history from early calculating devices to modern computers.'
            ],
        ];
        
        foreach ($textFiles as $textFile) {
            // Check if file exists
            if (File::exists(base_path($textFile['file']))) {
                // Get file content
                $content = File::get(base_path($textFile['file']));
                
                // Get file size
                $fileSize = File::size(base_path($textFile['file']));
                
                // Create document
                Document::create([
                    'user_id' => $user->id,
                    'title' => $textFile['title'],
                    'description' => $textFile['description'],
                    'file_path' => $textFile['file'],
                    'file_type' => 'text/plain',
                    'file_size' => $fileSize,
                    'content' => $content,
                ]);
                
                $this->command->info("Document '{$textFile['title']}' created successfully.");
            } else {
                $this->command->error("File {$textFile['file']} not found.");
            }
        }
    }
}