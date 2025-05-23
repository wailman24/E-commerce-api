<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProductRecommendationController extends Controller
{
    public function getRecommendations(Request $request)
    {
        // Validate input (product name)
        $request->validate([
            'product_name' => 'required|string',
        ]);

        $productName = $request->input('product_name');

        // Path to the Python script (relative to Laravel base path)
        //$pythonScriptPath = base_path('../recommander_system/recommender.py');
        //C:\Users\badro\AppData\Local\Programs\Python\Python313\Scripts\
        $pythonPath = 'C:\Users\badro\AppData\Local\Programs\Python\Python313\python.exe'; // Use the correct Python path

        // Run the Python script with the product name
        $process = new Process([$pythonPath, 'C:\xampp\htdocs\recommander_system\recommander.py', escapeshellarg($productName)]);
        $process->run();


        // Check if the process executed successfully
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Get and decode the Python script output
        $output = trim($process->getOutput());

        try {
            $recommendedProducts = json_decode($output, true);

            // Ensure output is a valid array
            if (!is_array($recommendedProducts)) {
                throw new \Exception("Invalid JSON response from Python script.");
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process recommendations.',
                'message' => $e->getMessage()
            ], 500);
        }

        // Return the recommended products as JSON
        return response()->json([
            'recommended_products' => $recommendedProducts
        ]);
    }
}

exec('C:\Users\badro\AppData\Local\Programs\Python\Python313\python.exe --version', $output);
print_r($output);
