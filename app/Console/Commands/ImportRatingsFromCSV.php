<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Review; // Make sure you have this model
use Illuminate\Support\Facades\DB;

class ImportRatingsFromCSV extends Command
{
    protected $signature = 'ratings:import {file : Path to the CSV file}';

    protected $description = 'Import ratings from a CSV file into the database';

    public function handle()
    {
        $path = $this->argument('file');

        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return 1;
        }

        $this->info("Importing ratings from: $path");

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // Skip header

        $count = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $user_id = $data[0];
            $product_id = $data[1];
            $rating = (int) $data[2];

            // Random comment logic
            $comment = $this->generateComment($rating);

            try {
                Review::create([
                    'user_id'    => $user_id,
                    'product_id' => $product_id,
                    'rating'     => $rating,
                    'comment'    => $comment,
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to insert row: " . json_encode($data));
                $this->error($e->getMessage());
            }
        }

        fclose($handle);
        $this->info("Successfully imported $count ratings.");
        return 0;
    }

    private function generateComment($rating)
    {
        // 50% chance to skip comment
        if (rand(0, 1) === 0) {
            return null;
        }

        $comments = [
            1 => ['a wast of money', 'Horrible experience', 'Not recommended at all'],
            2 => ['Not great', 'Disappointed', 'Could be better'],
            3 => ['It’s okay', 'Average', 'Nothing special'],
            4 => ['Pretty good', 'Satisfied', 'I liked it'],
            5 => ['Excellent!', 'Amazing product', 'Highly recommend!'],
        ];

        $pool = $comments[$rating] ?? ['No comment'];
        return $pool[array_rand($pool)];
    }
}
