<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ImportProducts extends Command
{
    protected $signature = 'import:products';
    protected $description = 'Import products from a cleaned CSV file';

    public function handle()
    {
        $path = storage_path('app/products_cleaned.csv');

        if (!file_exists($path)) {
            $this->error("CSV file not found at $path");
            return 1;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file); // read column names

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            Product::create([

                'name'         => $data['name'],
                'category_id'  => $data['category_id'],
                'about'        => $data['about'],
                'prix'         => $data['prix'],
                'stock' => rand(5, 200),
                'seller_id' => rand(1, 50),
                'is_valid' => 1,

                // Leave stock, is_valide, seller_id for the seeder
            ]);
        }

        fclose($file);
        $this->info('Products imported successfully.');
        return 0;
    }
}
