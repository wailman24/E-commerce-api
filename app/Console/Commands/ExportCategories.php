<?php

namespace App\Console\Commands;

use League\Csv\Writer;
use App\Models\Categorie;
use Illuminate\Console\Command;
use League\Csv\CannotInsertRecord;
use SplTempFileObject;

class ExportCategories extends Command
{
    protected $signature = 'app:export-categories';
    protected $description = 'Export categories to a CSV file';

    public function handle(): void
    {
        // Get categories
        $categories = Categorie::all();

        // Create CSV writer using temporary memory file
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Insert header row
        $csv->insertOne(['name', 'category_id']);

        // Insert data
        foreach ($categories as $cat) {
            $csv->insertOne([$cat->name, $cat->category_id]);
        }

        // Write to storage file
        file_put_contents(storage_path('app/category.csv'), $csv->toString());

        $this->info('Categories exported to storage/app/category.csv');
    }
}
