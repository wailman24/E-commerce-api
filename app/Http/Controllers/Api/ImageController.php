<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Image= Image::get();
        if ($Image->count() > 0) {
            return ImageResource::collection($Image);
        }else{

            return response()->json(['message'=>'No Image Available'],200);
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
            'image_url'=>'required|string|max:2000|unique:images',
            'is_main'=>'required|boolean',
            'product_id'=>'integer|exists:products,id',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'=>$validator->messages(),
            ],422);
        }
        

        $data = Image::create([
            'image_url'=>$request->image_url,
            'is_main'=>$request->is_main,
            'product_id'=>$request->product_id,

        ]);

        return response()->json([
            'message'=>'Image created successfully',
            'DATA' => new ImageResource($data) ,
        ],200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Image $Image)
    {
        return new ImageResource($Image) ;
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
    public function update(Request $request, Image $Image)
    {
        $validator =Validator::make($request->all(),[
            'image_url'=>'required|string|max:2000|unique:images',
            'is_main'=>'required|boolean',
            'product_id'=>'integer|exists:products,id',
 
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'error'=>$validator->messages(),
             ],422);
         } 
         
 
         $Image->update([
            'image_url'=>$request->image_url,
            'is_main'=>$request->is_main,
            'product_id'=>$request->product_id,
         ]);
 
         return response()->json([
             'message'=>'Image updated successfully',
             'DATA' => new ImageResource($Image) ,
         ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $Image)
    {
        $Image->delete();
        return response()->json([
            'message'=>'Image deleted successfully',
        ],200);
    }
}
