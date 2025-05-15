<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use App\Models\Image;

class ImportImagesFromCSV extends Command
{
    protected $signature = 'app:import-images-from-csv';
    protected $description = 'Import images from CSV (with URLs) and download them into storage';

    public function handle()
    {
        $csvPath = storage_path('app/filtered_images.csv'); // make sure your CSV is here
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // assumes first row = header

        $records = $csv->getRecords();

        foreach ($records as $record) {
            $url = $record['img_link']; // assumes column name is 'image_url'
            $productId = $record['product_id'] ?? null;

            if (!$url || !$productId) {
                $this->error("Missing URL or product_id. Skipping...");
                continue;
            }

            try {
                // Create filename from the URL
                $filename = Str::random(10) . '.jpg'; // assume all images are jpg
                $imageContent = file_get_contents($url);

                // Store in public disk
                Storage::disk('public')->put("uploads/images/{$filename}", $imageContent);

                // Save to DB if needed
                Image::create([
                    'image_url' => "uploads/images/{$filename}",
                    'product_id' => $productId,
                    'is_main' => 1, // or your logic
                ]);

                $this->info("Image downloaded and saved: $filename");
            } catch (\Exception $e) {
                $this->error("Failed to download from $url: " . $e->getMessage());
            }
        }

        $this->info("Import completed.");
    }
}
