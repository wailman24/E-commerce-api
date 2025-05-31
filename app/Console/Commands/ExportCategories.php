<?php

namespace App\Console\Commands;

use SplFileObject;
use League\Csv\Writer;
use App\Models\Categorie;
use Illuminate\Console\Command;

class ExportCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $category = Categorie::all();

        // Create a new CSV writer object that writes to a file
        $csv = Writer::createFromFileObject(new SplFileObject(storage_path('app/category.csv'), 'w'));

        // Insert column headers
        $csv->insertOne(['name', 'category_id']);

        // Insert product data
        foreach ($category as $categorys) {

            $csv->insertOne([$categorys->name, $categorys->category_id]);
        }

        // Inform the user that the export was successful
        $this->info('category have been successfully exported to products.csv in the storage directory.');

        //to execute this command : php artisan app:export-products-to-csv
    }
}
