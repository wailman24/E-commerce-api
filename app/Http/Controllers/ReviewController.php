<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json($reviews, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if (!Product::find($productId)) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found'], 404);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review Has Been Added Successfully', 'review' => $review], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review Not Found'], 404);
        }

        return response()->json($review, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $reviewId)
    {
        $request->validate([
            'rating' => 'integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $review = DB::table('reviews')->where('id', $reviewId)
            ->first();

        if ($review->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to update this review'], 403);
        }

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review Not Found'], 404);
        }

        

        $review->update($request->only(['rating', 'comment']));

        return response()->json([
            'success' => true,
            'message' => 'Review Updated', 'review' => $review], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($reviewId)
    {
        $review = Review::where('id', $reviewId)
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review Not Found'], 404);
        }

        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You Are Not Allowed To Update This Review'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review Deleted'], 200);
    }
}
