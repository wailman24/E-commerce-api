<?php

namespace App\Console\Commands;

use App\Models\Categorie;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Illuminate\Support\Facades\Storage;

class ImportCategories extends Command
{

    protected $signature = 'app:import-categories';

    protected $description = 'Import categories from a CSV file into the database';

    /**

Execute the console command.*/
    public function handle()
    {
        $filePath = storage_path('app/category.csv');

        if (!file_exists($filePath)) {
            $this->error("CSV file not found at: $filePath");
            return;
        }

        // Read the CSV
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0); // first row is the header

        $records = $csv->getRecords();

        $imported = 0;

        foreach ($records as $record) {
            // Basic validation
            if (!isset($record['name'])) {
                $this->warn('Skipped a row due to missing "name".');
                continue;
            }

            // Insert into DB
            Categorie::create([
                'name' => $record['name'],
                //'category_id' => $record['category_id'] ?? null,
                'category_id' => $record['category_id'] !== '' ? (int) $record['category_id'] : null,

            ]);

            $imported++;
        }

        $this->info("$imported categories imported successfully.");
    }
}
