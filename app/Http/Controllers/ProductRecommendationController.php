<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProductRecommendationController extends Controller
{
    public function getRecommendations_content($productID)
    {
        $url = "http://127.0.0.1:5000/recommend_content/" . rawurlencode($productID);

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data); // or pass $data to a view
        } else {
            return response()->json(['error' => 'Failed to fetch recommendations'], 500);
        }
    }

    public function getRecommendations_collaborative($UserID)
    {
        $url = "http://127.0.0.1:5000/recommend_users/" . rawurlencode($UserID);

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data); // or pass $data to a view
        } else {
            return response()->json(['error' => 'Failed to fetch recommendations'], 500);
        }
    }

    public function getRecommendations_popularity()
    {
        $url = "http://127.0.0.1:5000/recommend_popular";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data); // or pass $data to a view
        } else {
            return response()->json(['error' => 'Failed to fetch recommendations'], 500);
        }
    }
}
