<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ImageResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $images = Image::get();
        if ($images->count() > 0) {
            return ImageResource::collection($images);
        } else {
            return response()->json(['message' => 'No Image Available'], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'image' => 'required|mimes:png,jpg,jpeg,webp',
                'product_id' => 'required|integer|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->messages(),
                ], 422);
            }

            $product = Product::find($request->product_id);
            $seller = Seller::where('user_id', $user->id)->first();

            if ($product->seller_id !== $seller->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to store images in other seller\'s products.',
                ], 403);
            }

            $isMain = Image::where('product_id', $request->product_id)->exists() ? 0 : 1;

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('uploads/images', 'public');
            }

            $image = Image::create([
                'image_url' => $imagePath,
                'product_id' => $request->product_id,
                'is_main' => $isMain,
            ]);

            return response()->json([
                'message' => 'Image created successfully',
                'DATA' => new ImageResource($image),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        return new ImageResource($image);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateImage(Request $request, Image $image)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'image' => 'nullable|mimes:png,jpg,jpeg,webp',
                'product_id' => 'required|integer|exists:products,id',
                'is_main' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->messages(),
                ], 422);
            }

            $product = Product::find($request->product_id);
            $seller = Seller::where('user_id', $user->id)->first();

            if ($product->seller_id !== $seller->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to modify images of other seller\'s products.',
                ], 403);
            }

            if ($image->is_main && $request->is_main === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to remove the main image directly.',
                ], 403);
            }

            $path = $image->image_url;

            if ($request->hasFile('image')) {
                if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
                $path = $request->file('image')->store('uploads/images', 'public');
            }

            // If changing to main, demote existing main image
            if (!$image->is_main && $request->is_main == 1) {
                Image::where('product_id', $request->product_id)
                    ->where('is_main', 1)
                    ->update(['is_main' => 0]);
            }

            $image->update([
                'image_url' => $path,
                'product_id' => $request->product_id,
                'is_main' => $request->is_main ?? $image->is_main,
            ]);

            return response()->json([
                'message' => 'Image updated successfully',
                'DATA' => new ImageResource($image),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        try {
            $user = Auth::user();

            if ($user->role_id !== 2) {
                $seller = Seller::where('user_id', $user->id)->first();
                $product = Product::find($image->product_id);

                if ($product->seller_id !== $seller->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You are not allowed to delete images of other seller\'s products.',
                    ], 403);
                }
            }

            $main = $image->is_main;
            $productId = $image->product_id;

            if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }

            $image->delete();

            if ($main) {
                $newMain = Image::where('product_id', $productId)->first();
                if ($newMain) {
                    $newMain->update(['is_main' => 1]);
                }
            }

            return response()->json([
                'message' => 'Image deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
