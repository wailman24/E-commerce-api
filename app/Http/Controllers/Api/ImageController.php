<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ImageResource;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Support\Facades\Validator;
use Laravel\Prompts\Prompt;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Image = Image::get();
        if ($Image->count() > 0) {
            return ImageResource::collection($Image);
        } else {

            return response()->json(['message' => 'No Image Available'], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'image_url' => 'required|mimes:png,jpg,jpeg,webp',
                'product_id' => 'required|integer|exists:products,id',

            ]);
            $isMain = 1;
            if (Image::where('product_id', $request->product_id)->count() > 0) {
                $isMain = 0;
            }

            $Product = Product::where('id', $request->product_id)->first();

            $seller = Seller::where('user_id', $user->id)->first();

            if ($Product->seller_id !== $seller->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'you are not allowed to store images in other seller\'s products ',
                    'sp_id' => $Product->seller_id,
                    's_id' => $seller->id

                ], 403);
            }


            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->messages(),
                ], 422);
            }

            $imagePath = null;
            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image_url')->store('uploads/images', 'public');
            }
            // dd(config('filesystems.disks'));

            $data = Image::create([
                'image_url' => $imagePath,
                'product_id' => $request->product_id,
                'is_main' => $isMain,
            ]);

            return response()->json([
                'message' => 'Image created successfully',
                'DATA' => new ImageResource($data),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $Image)
    {
        return new ImageResource($Image);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateimage(Request $request, Image $Image)
    {
        $user = Auth::user();


        $main = $Image->is_main;


        $validator = Validator::make($request->all(), [
            'image_url' => 'required|mimes:png,jpg,jpeg,webp',
            'product_id' => 'required|integer|exists:products,id',
            'is_main' => 'boolean',

        ]);

        $Product = Product::where('id', $request->product_id)->first();

        $seller = Seller::where('user_id', $user->id)->first();

        if ($Product->seller_id !== $seller->id) {
            return response()->json([
                'status' => false,
                'message' => 'you are not allowed to modify images of other seller\'s products '
            ], 403);
        }



        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages(),
            ], 422);
        }

        if ($main == 1 && $request->is_main == 0 && $request->is_main !== null) {
            return response()->json([
                'status' => false,
                'message' => 'you are not allowed to remove the main images directly'

            ], 403);
        }

        if ($request->hasFile('image_url')) {
            if ($Image->image_url) {
                Storage::delete($Image->image_url);
            }
            $path = $request->file('image_url')->store('public/images');
            $Image->image_url = $path;
        }

        $Image->product_id = $request->product_id;
        $Image->save();



        if ($main == 0 && $request->is_main == 1 && $request->is_main !== null) {
            $mainImage = Image::where('is_main', 1)
                ->where('product_id', $request->product_id)->first();
            $mainImage->update(['is_main' => 0]);
        }

        if ($request->is_main !== null) {
            $main = $request->is_main;
        }

        $Image->update([
            'image_url' => $path,
            'product_id' => $request->product_id,
            'is_main' => $main,

        ]);

        return response()->json([
            'message' => 'Image updated successfully',
            'DATA' => new ImageResource($Image),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $Image, Product $Product)
    {
        $user = Auth::user();
        if ($user->role_id !== 2) {

            $seller = Seller::where('user_id', $user->id)->first();

            $Product = Product::where('id', $Image->product_id)->first();

            if ($Product->seller_id !== $seller->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'you are not allowed not delete images of other seller\'s products '
                ], 403);
            }
        }

        $main = $Image->is_main;
        $productId = $Image->product_id;

        $Image->delete();

        if ($main) {
            $mainImage = Image::where('is_main', 0)
                ->where('product_id', $productId)
                ->first();
            if ($mainImage) {
                $mainImage->update(['is_main' => 1]);
            }
        }

        return response()->json([
            'message' => 'Image deleted successfully',
        ], 200);
    }
}
