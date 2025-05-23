<?php

namespace App\Console\Commands;

use App\Models\Categorie;
use App\Models\Product;
use Illuminate\Console\Command;
use App\Models\Category;
use League\Csv\Writer;
use SplFileObject;

class ExportProductsToCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-products-to-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export product data to CSV for recommendation system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::with('categorie')
            ->select('name', 'about', 'category_id')
            ->get();

        // Create a new CSV writer object that writes to a file
        $csv = Writer::createFromFileObject(new SplFileObject(storage_path('app/products.csv'), 'w'));

        // Insert column headers
        $csv->insertOne(['name', 'about', 'category_name']);

        // Insert product data
        foreach ($products as $product) {

            $categoryName = Categorie::where('id', $product->category_id)->first();

            $csv->insertOne([$product->name, $product->about, $categoryName->name]);
        }

        // Inform the user that the export was successful
        $this->info('Products have been successfully exported to products.csv in the storage directory.');
    }
}
