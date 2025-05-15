<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use App\Models\Review;

class ExportReviewsToCSV extends Command
{
    protected $signature = 'export:reviews';
    protected $description = 'Export reviews to a CSV file';

    public function handle()
    {
        $fileName = 'reviews.csv';
        $filePath = storage_path("app/{$fileName}");

        $reviews = Review::with('user', 'product')->get();

        $csvData = [];

        // Add headers
        $csvData[] = ['User ID', 'Product ID', 'Rating', 'Product Name'];

        foreach ($reviews as $review) {
            $ProductName = Product::where('id', $review->product_id)->first();
            $csvData[] = [

                $review->user_id,
                $review->product_id,
                $review->rating,
                $ProductName->name
            ];
        }

        // Open file for writing
        $file = fopen($filePath, 'w');

        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        $this->info("Reviews exported successfully to: {$filePath}");

        //to execute this command : php artisan export:reviews
    }
}
