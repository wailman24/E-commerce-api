<?php

namespace App\Http\Controllers\Api;

use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getallproducts()
    {
        $user = Auth::user();
        if ($user->role_id == 1) {
            $Product = Product::all();
            return ProductResource::collection($Product);
        } else {
            $seller_id = Seller::where('user_id', $user->id)->first();
            $Products = Product::where('seller_id', $seller_id)->get();
            return ProductResource::collection($Products);
        }
    }

    public function getallproductsforsellers()
    {
        try {
            $user = Auth::user();

            $seller_id = Seller::where('user_id', $user->id)->first();
            if (!$seller_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Seller not found for this user.',
                ], 404);
            }
            $Products = Product::where('seller_id', $seller_id->id)->get();
            return ProductResource::collection($Products);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getvalidproducts()
    {
        $Products = Product::where('is_valid', true)->get();
        //return new ProductResource($Products);
        return ProductResource::collection($Products);
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
            $seller = Seller::where('user_id', $user->id)->first();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:categories,id',
                'about' => 'required|string|min:20|max:2000',
                'prix' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->messages(),
                ], 422);
            }


            $data = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'about' => $request->about,
                'prix' => $request->prix,
                'stock' => $request->stock,
                'seller_id' => $seller->id,
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'DATA' => new ProductResource($data),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getBestDealsProducts()
    {

        $products = Product::with(['images'])->withSum('order_item as total_qte', 'qte')->orderByDesc('total_qte')->take(env('TOP_PURCHASED_PRODUCT'))->get();
        //return new ProductResource($products);
        return ProductResource::collection($products);
    }
    /**
     * Display the specified resource.
     */
    public function show(Product $Product)
    {
        return new ProductResource($Product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $Product)
    {
        $user = Auth::user();

        $seller = Seller::where('user_id', $user->id)->first();

        if ($Product->seller_id !== $seller->id) {
            return response()->json([
                'status' => false,
                'message' => 'you are not allowed not modify other seller\'s products ',
                'sp_id' => $Product->seller_id,
                's_id' => $seller->id

            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'about' => 'required|string|min:20|max:2000',
            'prix' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',

        ]);



        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages(),
            ], 422);
        }


        $Product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'about' => $request->about,
            'prix' => $request->prix,
            'stock' => $request->stock,

        ]);

        return response()->json([
            'message' => 'Product Updated successfully',
            'DATA' => new ProductResource($Product),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $Product)
    {
        $user = Auth::user();
        if ($user->role_id !== 2) {

            $seller = Seller::where('user_id', $user->id)->first();

            if ($Product->seller_id !== $seller->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'you are not allowed not delete other seller\'s products '
                ], 403);
            }
        }

        $Product->delete();
        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }

    public function updatestatus(Product $Product)
    {

        $Product->is_valid = !$Product->is_valid;
        $valid = 'unvalidated';
        if ($Product->is_valid) {
            $valid = 'validated';
        }
        $Product->update([
            'is_valid' => $Product->is_valid,
        ]);
        return response()->json([
            'message' => 'the product has been ' . $valid,
        ], 200);
    }
}
