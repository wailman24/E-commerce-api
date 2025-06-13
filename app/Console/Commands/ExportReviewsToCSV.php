<?php

namespace App\Console\Commands;

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
        $csvData[] = ['User ID', 'Product ID', 'Rating', 'Product Name'];

        foreach ($reviews as $review) {
            $csvData[] = [
                $review->user_id,
                $review->product_id,
                $review->rating,
                $review->product?->name ?? 'N/A',
            ];
        }

        $file = fopen($filePath, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        $this->info("Reviews exported successfully to: {$filePath}");
    }
}
