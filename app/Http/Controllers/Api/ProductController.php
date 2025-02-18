<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Product= Product::get();
        if ($Product->count() > 0) {
            return ProductResource::collection($Product);
        }else{

            return response()->json(['message'=>'No Product Available'],200);
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
        $validator =Validator::make($request->all(),[
            'name'=>'required|string|max:255|unique:products',
            'category_id'=>'required|integer|exists:categories,id',
            'about'=>'required|string|max:1000',
            'prix'=>'required|integer|min:0',
            'stock'=>'integer|min:0',
            'seller_id'=>'required|integer|exists:sellers,id',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'=>$validator->messages(),
            ],422);
        }
        

        $data = Product::create([
            'name'=>$request->name,
            'category_id'=>$request->category_id,
            'about'=>$request->about,
            'prix'=>$request->prix,
            'stock'=>$request->stock,
            'seller_id'=>$request->seller_id,

        ]);

        return response()->json([
            'message'=>'Product created successfully',
            'DATA' => new ProductResource($data) ,
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $Product)
    {
        return new ProductResource($Product) ;
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
    public function update(Request $request, Product $Product)
    {
        $validator =Validator::make($request->all(),[
            'name'=>'required|string|max:255|unique:products',
            'category_id'=>'required|integer|exists:categories,id',
            'about'=>'required|string|max:1000',
            'prix'=>'required|integer|min:0',
            'stock'=>'integer|min:0',
            'seller_id'=>'required|integer|exists:sellers,id',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'=>$validator->messages(),
            ],422);
        }
        

        $Product->update([
            'name'=>$request->name,
            'category_id'=>$request->category_id,
            'about'=>$request->about,
            'prix'=>$request->prix,
            'stock'=>$request->stock,
            'seller_id'=>$request->seller_id,

        ]);

        return response()->json([
            'message'=>'Product Updated successfully',
            'DATA' => new ProductResource($Product) ,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $Product)
    {
        $Product->delete();
        return response()->json([
            'message'=>'Product deleted successfully',
        ],200);
    }
}
